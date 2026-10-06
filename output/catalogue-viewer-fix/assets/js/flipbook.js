/**
 * X-TRAL 3D Flipbook Catalogue Engine
 * Instant loading, real-time cursor drag & swap,
 * smart spread-splitting for full-screen panoramic brochures,
 * procedural paper audio, high-resolution rendering, zoom, and thumbnails.
 */

(function () {
  'use strict';

  // --- Configuration & State ---
  const state = {
    pdfDoc: null,
    totalPdfSheets: 0,
    totalPages: 0, // total book pages
    currentPage: 0,
    pageFlip: null,
    aspectRatio: 2.828, // Default sheet aspect ratio
    singlePageRatio: 1.414, // Single page aspect ratio
    isSpreadPdf: false,
    pageCache: new Map(), // bookPageNum -> DataURL
    sheetCache: new Map(), // sheetNum -> { leftDataUrl, rightDataUrl }
    thumbCache: new Map(), // bookPageNum -> DataURL
    isFlipping: false,
    autoplayTimer: null,
    isAutoplay: false,
    soundEnabled: localStorage.getItem('xtral_flip_sound') !== 'false',
    isZoomActive: false,
    isDrawerOpen: false,
    scaleFactor: Math.min(2.0, Math.max(1.35, (window.devicePixelRatio || 1) * 1.15)),
  };

  // Prevent default image dragging and text selection
  document.addEventListener('dragstart', (e) => e.preventDefault());
  document.addEventListener('selectstart', (e) => {
    if (e.target.tagName !== 'INPUT') e.preventDefault();
  });

  // --- Web Audio Procedural Paper Turn Sound ---
  function playPaperTurnSound() {
    if (!state.soundEnabled) return;
    try {
      const AudioCtx = window.AudioContext || window.webkitAudioContext;
      if (!AudioCtx) return;
      const ctx = new AudioCtx();
      
      const duration = 0.26;
      const bufferSize = Math.floor(ctx.sampleRate * duration);
      const buffer = ctx.createBuffer(1, bufferSize, ctx.sampleRate);
      const output = buffer.getChannelData(0);

      // Soft pink noise burst simulating smooth luxury paper glide
      let b0 = 0, b1 = 0, b2 = 0;
      for (let i = 0; i < bufferSize; i++) {
        const white = Math.random() * 2 - 1;
        b0 = 0.99886 * b0 + white * 0.0555179;
        b1 = 0.99332 * b1 + white * 0.0750759;
        b2 = 0.96900 * b2 + white * 0.1538520;
        const pink = b0 + b1 + b2 + white * 0.5362;
        const decay = Math.pow(1 - i / bufferSize, 2.5);
        output[i] = pink * 0.08 * decay;
      }

      const whiteNoise = ctx.createBufferSource();
      whiteNoise.buffer = buffer;

      const filter = ctx.createBiquadFilter();
      filter.type = 'bandpass';
      filter.frequency.setValueAtTime(1400, ctx.currentTime);
      filter.frequency.exponentialRampToValueAtTime(650, ctx.currentTime + duration);
      filter.Q.setValueAtTime(1.2, ctx.currentTime);

      const gain = ctx.createGain();
      gain.gain.setValueAtTime(0.16, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration);

      whiteNoise.connect(filter);
      filter.connect(gain);
      gain.connect(ctx.destination);

      whiteNoise.start();
      whiteNoise.stop(ctx.currentTime + duration);
    } catch (e) {
      // AudioContext could be pending initial user interaction
    }
  }

  // --- DOM Elements ---
  const el = {
    stage: document.getElementById('fb-stage'),
    loaderScreen: document.getElementById('fb-loader-screen'),
    loaderBar: document.getElementById('fb-loader-bar'),
    loaderText: document.getElementById('fb-loader-text'),
    bookContainer: document.getElementById('fb-book-container'),
    bookWrapper: document.getElementById('fb-book-wrapper'),
    prevBtn: document.querySelector('.fb-side-btn--prev'),
    nextBtn: document.querySelector('.fb-side-btn--next'),
    dockPrev: document.querySelector('.fb-dock-btn[data-action="prev"]'),
    dockNext: document.querySelector('.fb-dock-btn[data-action="next"]'),
    dockFirst: document.querySelector('.fb-dock-btn[data-action="first"]'),
    dockLast: document.querySelector('.fb-dock-btn[data-action="last"]'),
    dockThumb: document.querySelector('.fb-dock-btn[data-action="thumbs"]'),
    dockSound: document.querySelector('.fb-dock-btn[data-action="sound"]'),
    dockFullscreen: document.querySelector('.fb-dock-btn[data-action="fullscreen"]'),
    dockAutoplay: document.querySelector('.fb-dock-btn[data-action="autoplay"]'),
    dockZoom: document.querySelector('.fb-dock-btn[data-action="zoom"]'),
    pageInput: document.getElementById('fb-page-input'),
    pageTotalText: document.getElementById('fb-page-total'),
    drawer: document.getElementById('fb-drawer'),
    drawerClose: document.getElementById('fb-drawer-close'),
    thumbStrip: document.getElementById('fb-thumb-strip'),
    zoomOverlay: document.getElementById('fb-zoom-overlay'),
    zoomClose: document.getElementById('fb-zoom-close'),
    zoomImg: document.getElementById('fb-zoom-img'),
    zoomStage: document.getElementById('fb-zoom-stage'),
    zoomTitle: document.getElementById('fb-zoom-title'),
    shareBtn: document.getElementById('fb-share-btn'),
    toast: document.getElementById('fb-toast'),
    toastMsg: document.getElementById('fb-toast-msg'),
  };

  // Toast feedback
  let toastTimer = null;
  function showToast(message) {
    if (!el.toast) return;
    if (el.toastMsg) el.toastMsg.textContent = message;
    el.toast.classList.add('is-show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => {
      el.toast.classList.remove('is-show');
    }, 2600);
  }

  // --- Initial Setup & Data Extraction ---
  const pdfUrl = document.body.dataset.pdfUrl;
  const thumbUrl = document.body.dataset.thumbUrl || '';
  let uploadedCoverPromise = null;

  function getUploadedCover() {
    if (!thumbUrl) return Promise.resolve(null);
    if (!uploadedCoverPromise) {
      uploadedCoverPromise = new Promise(resolve => {
        const image = new Image();
        const timeout = setTimeout(() => finish(null), 10000);
        function finish(url) {
          clearTimeout(timeout);
          image.onload = image.onerror = null;
          resolve(url);
        }
        image.onload = () => finish(image.naturalWidth ? image.src : null);
        image.onerror = () => finish(null);
        image.src = new URL(thumbUrl, document.baseURI).href;
      });
    }
    return uploadedCoverPromise;
  }

  if (!pdfUrl) {
    console.error('No PDF URL specified.');
    showLoadError();
    return;
  }

  // --- Resolve base URL — prefer server-injected absolute URL (most reliable on live servers with clean URL routing)
  const _baseEl = document.querySelector('base');
  const _pageBase = (window.XTRAL_BASE_URL)
    ? window.XTRAL_BASE_URL
    : (_baseEl ? new URL(_baseEl.getAttribute('href'), window.location.href).href : (window.location.origin + '/'));

  const PDF_VERSION = '3.11.174';
  const PDF_CDN = `https://cdnjs.cloudflare.com/ajax/libs/pdf.js/${PDF_VERSION}/`;

  function loadScript(src) {
    return new Promise((resolve, reject) => {
      const script = document.createElement('script');
      const timer = setTimeout(() => finish(new Error('Script loading timed out: ' + src)), 15000);
      function finish(error) {
        clearTimeout(timer);
        script.onload = script.onerror = null;
        if (error) { script.remove(); reject(error); }
        else resolve();
      }
      script.src = src;
      script.onload = () => finish();
      script.onerror = () => finish(new Error('Could not load script: ' + src));
      document.head.appendChild(script);
    });
  }

  async function ensureViewerLibraries() {
    const needsPdf = !window.pdfjsLib;
    await Promise.all([
      needsPdf ? loadScript(PDF_CDN + 'pdf.min.js') : Promise.resolve(),
      ((window.St && window.St.PageFlip) || window.PageFlip) ? Promise.resolve()
        : loadScript('https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.js'),
    ]);
    if (!window.pdfjsLib || !((window.St && window.St.PageFlip) || window.PageFlip)) {
      throw new Error('Catalogue viewer libraries are unavailable.');
    }

    // Keep the worker and PDF.js on the same version, even when an upload omitted
    // the vendor directory (or just the worker file).
    let workerSrc = PDF_CDN + 'pdf.worker.min.js';
    if (!needsPdf) {
      const localWorker = new URL('assets/vendor/pdf.worker.min.js', _pageBase);
      localWorker.searchParams.set('v', window.pdfjsLib.version);
      const controller = new AbortController();
      const timeout = setTimeout(() => controller.abort(), 8000);
      try {
        const response = await fetch(localWorker.href, { method: 'HEAD', signal: controller.signal });
        if (response.ok) workerSrc = localWorker.href;
      } catch (error) {
        console.warn('Using the catalogue PDF worker fallback.', error);
      } finally {
        clearTimeout(timeout);
      }
    }
    window.pdfjsLib.GlobalWorkerOptions.workerSrc = workerSrc;
  }

  // Resolve the PDF URL absolutely from the page base
  const _resolvedPdfUrl = pdfUrl ? new URL(pdfUrl, _pageBase).href : null;

  // Progress Bar Helper
  function updateProgress(percent, message) {
    if (el.loaderBar) el.loaderBar.style.width = percent + '%';
    if (el.loaderText) el.loaderText.textContent = message;
  }

  // Dismiss loader immediately and safely
  function hideLoader() {
    if (el.loaderScreen && !el.loaderScreen.classList.contains('is-hidden')) {
      el.loaderScreen.classList.add('is-hidden');
    }
  }

  function showLoadError() {
    if (!el.loaderScreen) return;
    el.loaderScreen.classList.remove('is-hidden');
    el.loaderScreen.setAttribute('role', 'alert');
    if (el.loaderText) el.loaderText.textContent = 'The 3D catalogue could not load. Please retry or open the PDF.';
    el.loaderScreen.querySelectorAll('.fb-loader-book-anim, .fb-loader-bar-wrap')
      .forEach(node => { node.style.display = 'none'; });
    if (el.loaderScreen.querySelector('.fb-load-error-actions')) return;
    const actions = document.createElement('div');
    actions.className = 'fb-load-error-actions';
    actions.style.cssText = 'display:flex;gap:12px;justify-content:center;flex-wrap:wrap;';
    const retry = document.createElement('button');
    retry.type = 'button';
    retry.className = 'fb-header-btn';
    retry.textContent = 'Try again';
    retry.addEventListener('click', () => window.location.reload());
    actions.appendChild(retry);
    if (pdfUrl) {
      const openPdf = document.createElement('a');
      openPdf.className = 'fb-header-btn fb-header-btn--primary';
      openPdf.href = pdfUrl;
      openPdf.textContent = 'Open PDF';
      actions.appendChild(openPdf);
    }
    el.loaderScreen.appendChild(actions);
  }

  // --- Spread Mapping Logic ---
  // Maps a booklet page number to the corresponding PDF sheet and side (left/right)
  function getSheetAndSide(bookPageNum) {
    if (!state.isSpreadPdf) {
      return { sheetNum: bookPageNum, isRight: false, label: `Page ${bookPageNum}` };
    }

    if (bookPageNum === 1) {
      // Front cover is Sheet 1 Right Half
      return { sheetNum: 1, isRight: true, label: 'Front Cover' };
    }
    if (bookPageNum === state.totalPages) {
      // Back cover is Sheet 1 Left Half
      return { sheetNum: 1, isRight: false, label: 'Back Cover' };
    }

    const offset = bookPageNum - 2;
    const sheetNum = 2 + Math.floor(offset / 2);
    const isRight = (offset % 2 === 1);
    return { sheetNum: sheetNum, isRight: isRight, label: `Page ${bookPageNum - 1}` };
  }

  // --- Sequential Safe PDF Sheet Renderer ---
  const sheetRenderInProgress = new Map();

  async function getSheetHalves(sheetNum, scale = state.scaleFactor) {
    if (state.sheetCache.has(sheetNum)) {
      return state.sheetCache.get(sheetNum);
    }
    if (sheetRenderInProgress.has(sheetNum)) {
      return sheetRenderInProgress.get(sheetNum);
    }

    const task = (async () => {
      try {
        const page = await state.pdfDoc.getPage(sheetNum);
        const viewport = page.getViewport({ scale: scale });

        const sheetCanvas = document.createElement('canvas');
        sheetCanvas.width = Math.round(viewport.width);
        sheetCanvas.height = Math.round(viewport.height);
        const sheetCtx = sheetCanvas.getContext('2d');

        await page.render({
          canvasContext: sheetCtx,
          viewport: viewport
        }).promise;

        if (!state.isSpreadPdf) {
          const fullDataUrl = sheetCanvas.toDataURL('image/jpeg', 0.90);
          const res = { leftDataUrl: fullDataUrl, rightDataUrl: fullDataUrl };
          state.sheetCache.set(sheetNum, res);
          return res;
        }

        // Slicing: Left Half vs Right Half
        const halfWidth = Math.round(viewport.width / 2);
        const halfHeight = viewport.height;

        // Left Half canvas
        const leftCanvas = document.createElement('canvas');
        leftCanvas.width = halfWidth;
        leftCanvas.height = halfHeight;
        const leftCtx = leftCanvas.getContext('2d');
        leftCtx.drawImage(sheetCanvas, 0, 0, halfWidth, halfHeight, 0, 0, halfWidth, halfHeight);
        const leftDataUrl = leftCanvas.toDataURL('image/jpeg', 0.90);

        // Right Half canvas
        const rightCanvas = document.createElement('canvas');
        rightCanvas.width = halfWidth;
        rightCanvas.height = halfHeight;
        const rightCtx = rightCanvas.getContext('2d');
        rightCtx.drawImage(sheetCanvas, halfWidth, 0, halfWidth, halfHeight, 0, 0, halfWidth, halfHeight);
        const rightDataUrl = rightCanvas.toDataURL('image/jpeg', 0.90);

        const res = { leftDataUrl, rightDataUrl };
        state.sheetCache.set(sheetNum, res);
        return res;
      } catch (err) {
        console.warn('Failed rendering sheet ' + sheetNum, err);
        return null;
      } finally {
        sheetRenderInProgress.delete(sheetNum);
      }
    })();

    sheetRenderInProgress.set(sheetNum, task);
    return task;
  }

  // Render a specific booklet page
  async function renderBookPage(bookPageNum) {
    if (state.pageCache.has(bookPageNum)) {
      return state.pageCache.get(bookPageNum);
    }

    const map = getSheetAndSide(bookPageNum);
    const sheetData = await getSheetHalves(map.sheetNum);
    if (!sheetData) {
      if (bookPageNum === 1) {
        const coverUrl = await getUploadedCover();
        if (coverUrl) {
          state.pageCache.set(bookPageNum, coverUrl);
          return coverUrl;
        }
      }
      return null;
    }

    const dataUrl = map.isRight ? sheetData.rightDataUrl : sheetData.leftDataUrl;
    state.pageCache.set(bookPageNum, dataUrl);
    return dataUrl;
  }

  // Fast thumbnail render at low scale
  async function renderThumbnail(bookPageNum) {
    if (state.thumbCache.has(bookPageNum)) return state.thumbCache.get(bookPageNum);
    if (state.pageCache.has(bookPageNum)) return state.pageCache.get(bookPageNum);

    const map = getSheetAndSide(bookPageNum);
    const sheetData = await getSheetHalves(map.sheetNum, 0.45);
    if (!sheetData) {
      if (bookPageNum === 1) {
        const coverUrl = await getUploadedCover();
        if (coverUrl) return coverUrl;
      }
      return null;
    }

    const dataUrl = map.isRight ? sheetData.rightDataUrl : sheetData.leftDataUrl;
    state.thumbCache.set(bookPageNum, dataUrl);
    return dataUrl;
  }

  // Inject rendered image into DOM page
  async function applyPageImage(bookPageNum) {
    const pageEl = document.querySelector(`.flip-page[data-page-index="${bookPageNum}"]`);
    if (!pageEl) return;

    const img = pageEl.querySelector('.page-render-img');
    const shimmer = pageEl.querySelector('.page-loader-shimmer');

    // Skip pages that already contain their final uploaded cover or PDF image.
    if (img && img.dataset.pdfRendered === 'true') {
      return true;
    }

    const dataUrl = await renderBookPage(bookPageNum);
    if (dataUrl && img) {
      img.classList.toggle('is-custom-cover', bookPageNum === 1 && !dataUrl.startsWith('data:'));
      img.src = dataUrl;
      img.dataset.pdfRendered = 'true';
      img.onload = () => {
        img.classList.add('is-loaded');
        if (shimmer) shimmer.classList.add('is-hidden');
      };
      // In case image is loaded synchronously from dataUrl
      img.classList.add('is-loaded');
      if (shimmer) shimmer.classList.add('is-hidden');
      updateThumbnailImage(bookPageNum, dataUrl);
      return true;
    }
    return false;
  }

  // Preload nearby pages in background without blocking animation frames
  function preloadNearbyPages(targetPage) {
    const pagesToLoad = [];
    const windowSize = 6;
    for (let i = -2; i <= windowSize; i++) {
      const p = targetPage + i;
      if (p >= 1 && p <= state.totalPages && !state.pageCache.has(p)) {
        pagesToLoad.push(p);
      }
    }

    for (const p of pagesToLoad) {
      if ('requestIdleCallback' in window) {
        requestIdleCallback(() => applyPageImage(p), { timeout: 1500 });
      } else {
        setTimeout(() => applyPageImage(p), 60);
      }
    }
  }

  // --- Dynamic Full-Screen Dimension Calculations ---
  function computeBookDimensions() {
    const stageWidth = window.innerWidth;
    const stageHeight = window.innerHeight - 60 - 85;
    const isMobile = window.innerWidth < 768;

    let singlePageWidth, singlePageHeight;
    const ratio = state.singlePageRatio || 1.414;

    if (isMobile) {
      singlePageWidth = Math.min(Math.round(stageWidth * 0.94), 580);
      singlePageHeight = Math.round(singlePageWidth / ratio);
      if (singlePageHeight > stageHeight * 0.88) {
        singlePageHeight = Math.round(stageHeight * 0.88);
        singlePageWidth = Math.round(singlePageHeight * ratio);
      }
    } else {
      const totalSpreadRatio = 2 * ratio; // ~2.828

      singlePageHeight = Math.min(stageHeight * 0.92, 750);
      let totalSpreadWidth = singlePageHeight * totalSpreadRatio;

      if (totalSpreadWidth > stageWidth * 0.92) {
        totalSpreadWidth = stageWidth * 0.92;
        singlePageHeight = Math.round(totalSpreadWidth / totalSpreadRatio);
      }
      singlePageWidth = Math.round(totalSpreadWidth / 2);
    }

    return {
      width: singlePageWidth,
      height: singlePageHeight
    };
  }

  // --- Build Page DOM Structure ---
  function createPageElements(numPages) {
    el.bookContainer.innerHTML = '';

    for (let i = 1; i <= numPages; i++) {
      const isFrontCover = i === 1;
      const isBackCover = i === numPages;
      const density = (isFrontCover || isBackCover) ? 'hard' : 'soft';
      const sideClass = (i % 2 === 0) ? '--left' : '--right';
      const map = getSheetAndSide(i);

      const pageDiv = document.createElement('div');
      pageDiv.className = `flip-page ${sideClass} ${isFrontCover ? 'flip-page--front' : ''} ${isBackCover ? 'flip-page--back' : ''}`;
      pageDiv.setAttribute('data-density', density);
      pageDiv.setAttribute('data-page-index', i);

      pageDiv.innerHTML = `
        <div class="page-inner">
          <div class="page-binding-shadow"></div>
          <div class="page-paper-lighting"></div>
          <div class="page-loader-shimmer">
            <div class="page-shimmer-ring"></div>
            <div class="page-shimmer-label">Loading ${map.label}…</div>
          </div>
          <img class="page-render-img" src="" alt="Catalogue ${map.label}" draggable="false" />
          <div class="page-corner-hint" title="Turn Page"></div>
          <div class="page-footer-num">${isFrontCover ? 'Cover' : isBackCover ? 'Back' : i - 1}</div>
        </div>
      `;

      el.bookContainer.appendChild(pageDiv);
    }
  }

  // --- Populate Thumbnail Drawer ---
  function buildThumbnails(numPages) {
    if (!el.thumbStrip) return;
    el.thumbStrip.innerHTML = '';

    for (let i = 1; i <= numPages; i++) {
      const map = getSheetAndSide(i);
      const item = document.createElement('div');
      item.className = `fb-thumb-item ${i === 1 ? 'is-current' : ''}`;
      item.dataset.page = i;

      item.innerHTML = `
        <div class="fb-thumb-preview">
          <div class="fb-thumb-placeholder">${map.label}</div>
          <img class="fb-thumb-img" alt="${map.label}" src="" loading="lazy" style="display:none;" draggable="false" />
        </div>
        <span class="fb-thumb-label">${map.label}</span>
      `;

      item.addEventListener('click', () => {
        goToPage(i);
        closeThumbnailDrawer();
      });

      el.thumbStrip.appendChild(item);
    }
  }

  function updateThumbnailImage(pageNum, dataUrl) {
    const item = el.thumbStrip?.querySelector(`.fb-thumb-item[data-page="${pageNum}"]`);
    if (!item) return;
    const img = item.querySelector('.fb-thumb-img');
    const placeholder = item.querySelector('.fb-thumb-placeholder');
    if (img && dataUrl) {
      img.src = dataUrl;
      img.style.display = 'block';
      if (placeholder) placeholder.style.display = 'none';
    }
  }

  function highlightThumbnail(pageNum) {
    if (!el.thumbStrip) return;
    const items = el.thumbStrip.querySelectorAll('.fb-thumb-item');
    items.forEach(it => {
      if (parseInt(it.dataset.page, 10) === pageNum) {
        it.classList.add('is-current');
        it.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
      } else {
        it.classList.remove('is-current');
      }
    });
  }

  async function loadVisibleThumbnails() {
    if (!state.pdfDoc) return;
    const items = el.thumbStrip?.querySelectorAll('.fb-thumb-item');
    if (!items) return;

    for (const it of items) {
      const p = parseInt(it.dataset.page, 10);
      const img = it.querySelector('.fb-thumb-img');
      if (img && img.style.display === 'none') {
        const thumbUrl = await renderThumbnail(p);
        if (thumbUrl) {
          updateThumbnailImage(p, thumbUrl);
        }
      }
    }
  }

  // --- Centering for Closed Book (Cover / Back) vs Open Spread ---
  function updateBookCentering(pageIndex) {
    if (!el.bookContainer) return;
    const isMobile = window.innerWidth < 768;
    const isPortrait = state.pageFlip && state.pageFlip.getOrientation() === 'portrait';

    if (isMobile || isPortrait) {
      el.bookContainer.classList.remove('is-cover', 'is-back-cover', 'is-open');
      if (el.stage) el.stage.classList.remove('has-cover', 'has-back', 'has-spread');
      return;
    }

    const isCover = pageIndex === 0;
    const isBack = pageIndex >= state.totalPages - 1;

    if (isCover) {
      el.bookContainer.classList.add('is-cover');
      el.bookContainer.classList.remove('is-back-cover', 'is-open');
      if (el.stage) {
        el.stage.classList.add('has-cover');
        el.stage.classList.remove('has-back', 'has-spread');
      }
    } else if (isBack) {
      el.bookContainer.classList.add('is-back-cover');
      el.bookContainer.classList.remove('is-cover', 'is-open');
      if (el.stage) {
        el.stage.classList.add('has-back');
        el.stage.classList.remove('has-cover', 'has-spread');
      }
    } else {
      el.bookContainer.classList.add('is-open');
      el.bookContainer.classList.remove('is-cover', 'is-back-cover');
      if (el.stage) {
        el.stage.classList.add('has-spread');
        el.stage.classList.remove('has-cover', 'has-back');
      }
    }
  }

  // --- UI Update Helpers ---
  function updateNavUI(pageIndex) {
    state.currentPage = pageIndex;
    const total = state.totalPages;

    updateBookCentering(pageIndex);

    if (pageIndex === 0) {
      if (el.pageInput) el.pageInput.value = 'Cover';
    } else if (pageIndex >= total - 1) {
      if (el.pageInput) el.pageInput.value = 'Back';
    } else {
      const isMobile = window.innerWidth < 768;
      if (isMobile) {
        if (el.pageInput) el.pageInput.value = pageIndex;
      } else {
        const leftP = pageIndex;
        const rightP = Math.min(pageIndex + 1, total - 1);
        if (el.pageInput) el.pageInput.value = `${leftP}-${rightP}`;
      }
    }

    if (el.pageTotalText) el.pageTotalText.textContent = `/ ${total - 2} Pages`;

    // Side buttons
    const isFirst = pageIndex <= 0;
    const isLast = pageIndex >= total - 1;

    if (el.prevBtn) el.prevBtn.classList.toggle('is-disabled', isFirst);
    if (el.nextBtn) el.nextBtn.classList.toggle('is-disabled', isLast);
    if (el.dockPrev) el.dockPrev.classList.toggle('is-disabled', isFirst);
    if (el.dockNext) el.dockNext.classList.toggle('is-disabled', isLast);
    if (el.dockFirst) el.dockFirst.classList.toggle('is-disabled', isFirst);
    if (el.dockLast) el.dockLast.classList.toggle('is-disabled', isLast);

    highlightThumbnail(pageIndex + 1);
  }

  // --- Page Navigation Controls with Smooth State Guards ---
  let lastFlipTimestamp = 0;

  function flipNext() {
    const now = Date.now();
    if (state.isFlipping || (now - lastFlipTimestamp < 600)) return;
    lastFlipTimestamp = now;
    state.isFlipping = true;

    if (state.pageFlip) {
      if (state.currentPage === 0) {
        // Glide towards center 2-page spread immediately as cover turns!
        updateBookCentering(1);
      } else if (state.currentPage >= state.totalPages - 2) {
        // Glide to back cover
        updateBookCentering(state.totalPages - 1);
      }
      state.pageFlip.flipNext();
    }
  }

  function flipPrev() {
    const now = Date.now();
    if (state.isFlipping || (now - lastFlipTimestamp < 600)) return;
    lastFlipTimestamp = now;
    state.isFlipping = true;

    if (state.pageFlip) {
      if (state.currentPage <= 2) {
        // Glide towards centered front cover immediately as it closes!
        updateBookCentering(0);
      } else if (state.currentPage >= state.totalPages - 1) {
        // Opening from back cover into 2-page spread
        updateBookCentering(state.totalPages - 2);
      }
      state.pageFlip.flipPrev();
    }
  }

  function goToPage(targetPageNum) {
    const now = Date.now();
    if (state.isFlipping || (now - lastFlipTimestamp < 600)) return;
    lastFlipTimestamp = now;
    if (!state.pageFlip) return;
    const targetIdx = Math.max(0, Math.min(targetPageNum - 1, state.totalPages - 1));
    updateBookCentering(targetIdx);
    state.pageFlip.flip(targetIdx);
  }

  // --- Autoplay / Slideshow ---
  function toggleAutoplay() {
    state.isAutoplay = !state.isAutoplay;
    if (el.dockAutoplay) {
      el.dockAutoplay.classList.toggle('is-active', state.isAutoplay);
      const icon = el.dockAutoplay.querySelector('i');
      if (icon) {
        icon.className = state.isAutoplay ? 'fa-solid fa-pause' : 'fa-solid fa-play';
      }
    }

    if (state.isAutoplay) {
      startAutoplayTimer();
    } else {
      stopAutoplayTimer();
    }
  }

  function startAutoplayTimer() {
    stopAutoplayTimer();
    state.autoplayTimer = setInterval(() => {
      if (!state.pageFlip) return;
      if (state.currentPage >= state.totalPages - 1) {
        state.pageFlip.flip(0); // loop back to cover
      } else {
        state.pageFlip.flipNext();
      }
    }, 4500);
  }

  function stopAutoplayTimer() {
    if (state.autoplayTimer) {
      clearInterval(state.autoplayTimer);
      state.autoplayTimer = null;
    }
  }

  // --- Sound Toggle ---
  function toggleSound() {
    state.soundEnabled = !state.soundEnabled;
    localStorage.setItem('xtral_flip_sound', state.soundEnabled ? 'true' : 'false');
    updateSoundButtonUI();
    if (state.soundEnabled) playPaperTurnSound();
  }

  function updateSoundButtonUI() {
    if (!el.dockSound) return;
    const icon = el.dockSound.querySelector('i');
    if (icon) {
      icon.className = state.soundEnabled ? 'fa-solid fa-volume-high' : 'fa-solid fa-volume-xmark';
    }
    el.dockSound.classList.toggle('is-active', state.soundEnabled);
  }

  // --- Fullscreen Toggle ---
  function toggleFullscreen() {
    if (!document.fullscreenElement) {
      document.documentElement.requestFullscreen().catch(() => {});
    } else {
      if (document.exitFullscreen) document.exitFullscreen();
    }
  }

  document.addEventListener('fullscreenchange', () => {
    if (el.dockFullscreen) {
      const icon = el.dockFullscreen.querySelector('i');
      if (icon) {
        icon.className = document.fullscreenElement ? 'fa-solid fa-compress' : 'fa-solid fa-expand';
      }
      el.dockFullscreen.classList.toggle('is-active', !!document.fullscreenElement);
    }
  });

  // --- Thumbnail Drawer ---
  function toggleThumbnailDrawer() {
    state.isDrawerOpen = !state.isDrawerOpen;
    if (el.drawer) el.drawer.classList.toggle('is-open', state.isDrawerOpen);
    if (el.dockThumb) el.dockThumb.classList.toggle('is-active', state.isDrawerOpen);

    if (state.isDrawerOpen) {
      highlightThumbnail(state.currentPage + 1);
      loadVisibleThumbnails();
    }
  }

  function closeThumbnailDrawer() {
    state.isDrawerOpen = false;
    if (el.drawer) el.drawer.classList.remove('is-open');
    if (el.dockThumb) el.dockThumb.classList.remove('is-active');
  }

  // --- High-Resolution Page Zoom Modal ---
  async function openZoomModal() {
    const pageNum = Math.max(1, state.currentPage + 1);
    state.isZoomActive = true;

    if (el.zoomOverlay) el.zoomOverlay.classList.add('is-active');
    if (el.zoomTitle) el.zoomTitle.textContent = `Page ${pageNum} — Inspect Details`;

    if (el.zoomImg) {
      el.zoomImg.src = '';
      const highResData = await renderBookPage(pageNum);
      el.zoomImg.src = highResData || state.pageCache.get(pageNum);
    }
  }

  function closeZoomModal() {
    state.isZoomActive = false;
    if (el.zoomOverlay) el.zoomOverlay.classList.remove('is-active');
  }

  // --- Share Button Interaction ---
  function setupShareAction() {
    if (!el.shareBtn) return;
    el.shareBtn.addEventListener('click', async () => {
      if (navigator.share) {
        try {
          await navigator.share({
            title: document.title,
            text: 'Check out the interactive 3D catalogue on X-Tral',
            url: window.location.href,
          });
          return;
        } catch (err) {}
      }

      if (navigator.clipboard) {
        try {
          await navigator.clipboard.writeText(window.location.href);
          showToast('Catalogue link copied to clipboard!');
        } catch (e) {
          showToast('Please copy the link from your address bar.');
        }
      }
    });
  }

  // --- Real-Page Cursor Swapping & Dragging Physics ---
  function setupCursorPageSwap() {
    const targetElement = el.bookWrapper || el.stage;
    if (!targetElement) return;

    let isMouseDown = false;
    let startX = 0;
    let startY = 0;
    let startTime = 0;
    let hasMoved = false;

    // Mouse Down - only track background drag/clicks outside the book itself
    targetElement.addEventListener('mousedown', (e) => {
      if (e.target.closest('button, a, input, .fb-dock, .fb-drawer, .fb-zoom-overlay, .stf__block, .stf__parent, .stf__wrapper, .flip-page')) return;
      isMouseDown = true;
      hasMoved = false;
      startX = e.clientX;
      startY = e.clientY;
      startTime = Date.now();
      document.body.classList.add('is-dragging');
    });

    // Mouse Move
    window.addEventListener('mousemove', (e) => {
      if (!isMouseDown) return;
      const dx = e.clientX - startX;
      const dy = e.clientY - startY;
      if (Math.abs(dx) > 10 || Math.abs(dy) > 10) {
        hasMoved = true;
      }
    });

    // Mouse Up: detect swipe/drag gesture or single click to turn page
    window.addEventListener('mouseup', (e) => {
      if (!isMouseDown) return;
      isMouseDown = false;
      document.body.classList.remove('is-dragging');

      if (state.isFlipping) return;

      const dx = e.clientX - startX;
      const dy = e.clientY - startY;
      const dt = Date.now() - startTime;

      // 1. If user dragged horizontally > 35px: SWAP PAGE!
      if (Math.abs(dx) > 35 && Math.abs(dx) > Math.abs(dy)) {
        if (dx < 0) {
          flipNext();
        } else {
          flipPrev();
        }
        return;
      }

      // 2. If it was a quick click without substantial drag on the book
      if (!hasMoved && dt < 450) {
        if (!el.bookContainer) return;
        const rect = el.bookContainer.getBoundingClientRect();
        if (
          e.clientX >= rect.left &&
          e.clientX <= rect.right &&
          e.clientY >= rect.top &&
          e.clientY <= rect.bottom
        ) {
          const isSinglePage = window.innerWidth < 768 || (state.pageFlip && state.pageFlip.getOrientation() === 'portrait');
          if (isSinglePage) {
            // In mobile single-page view: right half flips next, left half flips prev
            const midX = rect.left + rect.width / 2;
            if (e.clientX > midX) {
              flipNext();
            } else {
              flipPrev();
            }
          } else {
            if (state.currentPage === 0) {
              // Closed Cover: any click on the cover page opens the catalogue!
              flipNext();
            } else if (state.currentPage >= state.totalPages - 1) {
              // Closed Back Cover: any click flips back!
              flipPrev();
            } else {
              const midX = rect.left + rect.width / 2;
              if (e.clientX > midX) {
                flipNext();
              } else {
                flipPrev();
              }
            }
          }
        }
      }
    });

    // Touch Swipe Support on mobile — only handle swipe outside the book so PageFlip internal 3D gestures aren't duplicated
    let touchStartX = 0;
    let touchStartY = 0;
    targetElement.addEventListener('touchstart', (e) => {
      if (e.target.closest('button, a, input, .fb-dock, .fb-drawer, .stf__block, .stf__parent, .stf__wrapper, .flip-page')) return;
      touchStartX = e.changedTouches[0].screenX;
      touchStartY = e.changedTouches[0].screenY;
    }, { passive: true });

    targetElement.addEventListener('touchend', (e) => {
      if (e.target.closest('button, a, input, .fb-dock, .fb-drawer, .stf__block, .stf__parent, .stf__wrapper, .flip-page')) return;
      const touchEndX = e.changedTouches[0].screenX;
      const touchEndY = e.changedTouches[0].screenY;
      const diffX = touchEndX - touchStartX;
      const diffY = touchEndY - touchStartY;
      if (Math.abs(diffX) > 40 && Math.abs(diffX) > Math.abs(diffY)) {
        if (diffX < 0) flipNext();
        else flipPrev();
      }
    }, { passive: true });
  }

  // --- Keyboard Shortcuts ---
  window.addEventListener('keydown', (e) => {
    if (e.target.tagName === 'INPUT') return;

    switch (e.key) {
      case 'ArrowRight':
      case 'PageDown':
      case ' ':
        e.preventDefault();
        flipNext();
        break;
      case 'ArrowLeft':
      case 'PageUp':
        e.preventDefault();
        flipPrev();
        break;
      case 'Home':
        e.preventDefault();
        goToPage(1);
        break;
      case 'End':
        e.preventDefault();
        goToPage(state.totalPages);
        break;
      case 'f':
      case 'F':
        toggleFullscreen();
        break;
      case 'Escape':
        if (state.isZoomActive) closeZoomModal();
        if (state.isDrawerOpen) closeThumbnailDrawer();
        break;
    }
  });

  // --- Event Listeners Attachment ---
  function setupControlListeners() {
    el.prevBtn?.addEventListener('click', flipPrev);
    el.nextBtn?.addEventListener('click', flipNext);
    el.dockPrev?.addEventListener('click', flipPrev);
    el.dockNext?.addEventListener('click', flipNext);
    el.dockFirst?.addEventListener('click', () => goToPage(1));
    el.dockLast?.addEventListener('click', () => goToPage(state.totalPages));
    el.dockAutoplay?.addEventListener('click', toggleAutoplay);
    el.dockSound?.addEventListener('click', toggleSound);
    el.dockFullscreen?.addEventListener('click', toggleFullscreen);
    el.dockThumb?.addEventListener('click', toggleThumbnailDrawer);
    el.drawerClose?.addEventListener('click', closeThumbnailDrawer);
    el.dockZoom?.addEventListener('click', openZoomModal);
    el.zoomClose?.addEventListener('click', closeZoomModal);

    // Direct page input
    el.pageInput?.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        const val = parseInt(el.pageInput.value.replace(/[^0-9]/g, ''), 10);
        if (val >= 1 && val <= state.totalPages) {
          goToPage(val);
          el.pageInput.blur();
        }
      }
    });

    setupShareAction();
    setupCursorPageSwap();
  }

  // --- Main Initialization Flow ---
  async function init() {
    updateSoundButtonUI();
    setupControlListeners();

    try {
      updateProgress(5, 'Preparing Catalogue Viewer…');
      await ensureViewerLibraries();
      const PageFlipClass = (window.St && window.St.PageFlip) || window.PageFlip;
      updateProgress(15, 'Opening Catalogue…');

      // Use the pre-resolved absolute PDF URL (resolved against <base> tag for live server compatibility)
      const absolutePdfUrl = _resolvedPdfUrl || new URL(pdfUrl, window.location.href).href;

      // Load PDF via PDF.js with standard reliable streaming
      const loadingTask = pdfjsLib.getDocument({
        url: absolutePdfUrl,
        cMapPacked: true,
      });

      loadingTask.onProgress = function (progress) {
        if (progress.total > 0) {
          const pct = Math.round(15 + (progress.loaded / progress.total) * 45);
          updateProgress(pct, `Loading Catalogue…`);
        }
      };

      state.pdfDoc = await loadingTask.promise;
      state.totalPdfSheets = state.pdfDoc.numPages;

      // Inspect Page 1 for aspect ratio
      const page1 = await state.pdfDoc.getPage(1);
      const vp1 = page1.getViewport({ scale: 1.0 });
      state.aspectRatio = vp1.width / vp1.height;

      // Detect if sheets are panoramic 2-page spreads
      state.isSpreadPdf = state.aspectRatio > 1.8;
      state.singlePageRatio = state.isSpreadPdf ? (state.aspectRatio / 2) : state.aspectRatio;

      // Calculate total booklet pages
      if (state.isSpreadPdf) {
        state.totalPages = (state.totalPdfSheets - 1) * 2 + 2;
      } else {
        state.totalPages = state.totalPdfSheets;
      }

      updateProgress(70, `Readying ${state.totalPages} Catalogue Pages…`);

      // Build DOM elements for all pages
      createPageElements(state.totalPages);
      buildThumbnails(state.totalPages);

      // Compute full-screen dimensions
      const dims = computeBookDimensions();

      const isMobile = window.innerWidth < 768;

      // Initialize StPageFlip with silky smooth animation physics
      state.pageFlip = new PageFlipClass(el.bookContainer, {
        width: dims.width,
        height: dims.height,
        size: 'stretch',
        minWidth: isMobile ? Math.round(dims.width) : 260,
        maxWidth: isMobile ? Math.round(dims.width) : 1200,
        minHeight: isMobile ? Math.round(dims.height) : 380,
        maxHeight: isMobile ? Math.round(dims.height) : 1500,
        maxShadowOpacity: 0.32,
        showCover: true,
        usePortrait: true,
        flippingTime: isMobile ? 700 : 950,
        swipeDistance: 25,
        drawShadow: true,
        useMouseEvents: true,
        showPageCorners: true,
        clickEventForward: true,
      });

      // Load all pages into the flipbook engine
      const pageElements = el.bookContainer.querySelectorAll('.flip-page');
      state.pageFlip.loadFromHTML(pageElements);

      // Track flipping state to ensure zero jitter/double triggers
      state.pageFlip.on('changeState', (e) => {
        state.isFlipping = (e.data === 'flipping');
        if (e.data === 'flipping' || e.data === 'user_fold') {
          if (state.currentPage === 0) {
            updateBookCentering(1);
          }
        } else if (e.data === 'read') {
          if (state.pageFlip) {
            updateBookCentering(state.pageFlip.getCurrentPageIndex());
          }
        }
      });

      // Listen to page flip events
      state.pageFlip.on('flip', (e) => {
        playPaperTurnSound();
        updateNavUI(e.data);
        preloadNearbyPages(e.data + 1);
      });

      // Update UI on initial state
      updateNavUI(0);
      updateBookCentering(0);

      // Handle window resize gracefully
      window.addEventListener('resize', () => {
        const isMob = window.innerWidth < 768;
        if (state.pageFlip) {
          const s = state.pageFlip.getSettings();
          const newDims = computeBookDimensions();
          if (s) {
            s.minWidth = isMob ? Math.round(newDims.width) : 260;
            s.maxWidth = isMob ? Math.round(newDims.width) : 1200;
            s.minHeight = isMob ? Math.round(newDims.height) : 380;
            s.maxHeight = isMob ? Math.round(newDims.height) : 1500;
            s.flippingTime = isMob ? 700 : 950;
          }
        }
        updateBookCentering(state.currentPage);
      });
      updateProgress(90, 'Preparing the Front Cover…');
      if (!await applyPageImage(1)) {
        throw new Error('The catalogue front cover could not be rendered.');
      }
      updateProgress(100, 'Catalogue Ready!');

      // Only dismiss loading once the front cover has actually rendered.
      hideLoader();

      // Render Cover and initial spread asynchronously in background
      setTimeout(() => {
        applyPageImage(2);
        applyPageImage(3);
        preloadNearbyPages(4);
      }, 50);

    } catch (err) {
      console.error('Failed to load catalogue flipbook:', err);
      showLoadError();
    }
  }

  // Run on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
