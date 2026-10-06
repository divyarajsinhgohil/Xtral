/* =====================================================================
   X-TRAL — Site interactions
   Data comes from the xadmin Web API (see data.js + config.js).
   data.js calls window.XTRAL_RENDER() once data is loaded.
   ===================================================================== */
(function () {
  "use strict";

  /* ---- Graceful image fallback: hide broken images so empty product
     boxes read as clean placeholders until real images are added.
     Uses capture phase so it also catches dynamically inserted <img>. ---- */
  document.addEventListener("error", function (e) {
    const t = e.target;
    if (t && t.tagName === "IMG") { t.style.visibility = "hidden"; }
  }, true);

  /* ---- Header search icon: toggles a slide-down search panel ---- */
  const searchToggle = document.querySelector("[data-search-toggle]");
  const searchPanel = document.querySelector("[data-search-panel]");
  const headerSearchForm = document.querySelector("[data-header-search]");
  if (searchToggle && searchPanel) {
    const closeSearchPanel = () => {
      searchPanel.hidden = true;
      searchToggle.classList.remove("active");
      searchToggle.setAttribute("aria-expanded", "false");
    };
    const openSearchPanel = () => {
      searchPanel.hidden = false;
      searchToggle.classList.add("active");
      searchToggle.setAttribute("aria-expanded", "true");
      const input = document.getElementById("header-search");
      if (input) setTimeout(() => input.focus(), 50);
    };

    searchToggle.addEventListener("click", (e) => {
      e.stopPropagation();
      if (searchPanel.hidden) openSearchPanel(); else closeSearchPanel();
    });

    document.addEventListener("click", (e) => {
      if (!searchPanel.hidden && !searchPanel.contains(e.target) && e.target !== searchToggle) closeSearchPanel();
    });

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape" && !searchPanel.hidden) closeSearchPanel();
    });

    if (headerSearchForm) {
      headerSearchForm.addEventListener("submit", (e) => {
        e.preventDefault();
        const q = document.getElementById("header-search").value.trim();
        location.href = q ? `products?search=${encodeURIComponent(q)}` : "products";
      });
    }
  }

  /* ---- Mobile nav ---- */
  const toggle = document.querySelector(".nav-toggle");
  const links = document.querySelector(".nav-links");
  const dropdownWrappers = document.querySelectorAll(".nav-dropdown-wrapper");
  if (toggle && links) {
    const closeNav = () => {
      links.classList.remove("open");
      toggle.setAttribute("aria-expanded", "false");
      dropdownWrappers.forEach(w => w.classList.remove("open"));
    };

    toggle.addEventListener("click", () => {
      const isOpen = links.classList.toggle("open");
      toggle.setAttribute("aria-expanded", String(isOpen));
      if (!isOpen) dropdownWrappers.forEach(w => w.classList.remove("open"));
    });

    // On mobile, tapping the trigger toggles the submenu instead of navigating
    dropdownWrappers.forEach(wrapper => {
      const trigger = wrapper.querySelector(".nav-dropdown-trigger");
      if (trigger) {
        trigger.addEventListener("click", (e) => {
          if (window.matchMedia("(max-width: 768px)").matches) {
            e.preventDefault();
            wrapper.classList.toggle("open");
          }
        });
      }
    });

    links.addEventListener("click", (e) => {
      if (
        window.matchMedia("(max-width: 768px)").matches &&
        e.target.closest("a") &&
        !e.target.closest(".nav-dropdown-trigger")
      ) {
        closeNav();
      }
    });

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") closeNav();
    });

    window.addEventListener("resize", () => {
      if (!window.matchMedia("(max-width: 768px)").matches) closeNav();
    });
  }

  /* ---- Active nav link by filename ---- */
  const rawPath = (location.pathname.split("/").pop() || "index").split("?")[0].replace(/\.php$/i, "");
  const here = rawPath === "" ? "index" : rawPath;
  document.querySelectorAll(".nav-links a").forEach(a => {
    const rawHref = (a.getAttribute("href") || "").split("?")[0].replace(/\.php$/i, "");
    if (rawHref === here || (here === "index" && (rawHref === "" || rawHref === "index"))) {
      a.classList.add("active");
    }
    if (rawHref === "products" && (here === "products" || here === "pd")) {
      a.classList.add("active");
    }
  });

  /* ---- Reveal on scroll ---- */
  const revealEls = document.querySelectorAll("[data-reveal]");
  if ("IntersectionObserver" in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add("in"); io.unobserve(e.target); } });
    }, { threshold: 0.12, rootMargin: "0px 0px -5% 0px" });
    revealEls.forEach(el => io.observe(el));

    // Safety net: IntersectionObserver should catch every element, but if a
    // browser quirk, a layout race, or an observer that never fires leaves
    // something stuck at opacity:0, force it visible after a few seconds
    // rather than let real content stay permanently hidden.
    setTimeout(() => {
      revealEls.forEach(el => el.classList.add("in"));
    }, 2500);
  } else {
    // No IntersectionObserver support at all — just show everything.
    revealEls.forEach(el => el.classList.add("in"));
  }

  /* ---- Footer year ---- */
  const y = document.querySelector("[data-year]");
  if (y) y.textContent = new Date().getFullYear();

  /* ---- Contact form: sends to xadmin's contact webapi (SMTP -> inbox) ---- */
  const form = document.querySelector("[data-contact-form]");
  if (form) {
    const successEl = document.querySelector("[data-form-success]");
    const errorEl = document.querySelector("[data-form-error]");
    const submitBtn = form.querySelector("[data-submit-btn]");

    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      if (successEl) successEl.style.display = "none";
      if (errorEl) errorEl.style.display = "none";
      if (submitBtn) { submitBtn.disabled = true; submitBtn.style.opacity = "0.6"; }

      const phoneVal = form.phone.value.trim();
      const indianPhoneRegex = /^[6-9]\d{9}$/;
      if (!indianPhoneRegex.test(phoneVal)) {
        if (errorEl) {
          errorEl.textContent = "Please enter a valid 10-digit mobile number starting with 6, 7, 8, or 9.";
          errorEl.style.display = "block";
          errorEl.scrollIntoView({ behavior: "smooth", block: "center" });
        }
        if (submitBtn) { submitBtn.disabled = false; submitBtn.style.opacity = ""; }
        return;
      }

      let messageValue = form.message.value.trim();
      const extraLines = [];
      if (form.company && form.company.value.trim()) extraLines.push(`Company: ${form.company.value.trim()}`);
      if (form.country && form.country.value.trim()) extraLines.push(`Country: ${form.country.value.trim()}`);
      if (extraLines.length) messageValue = extraLines.join("\n") + "\n\n" + messageValue;

      const payload = {
        name: form.name.value.trim(),
        phone: form.phone.value.trim(),
        email: form.email.value.trim(),
        subject: form.subject.value,
        message: messageValue,
      };

      try {
        const res = await fetch(`${API_BASE}/contact.php`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload),
        });
        const json = await res.json();

        if (json.success) {
          form.reset();
          if (successEl) {
            successEl.textContent = "✓ " + json.message;
            successEl.style.display = "block";
            successEl.scrollIntoView({ behavior: "smooth", block: "center" });
          }
        } else if (errorEl) {
          errorEl.textContent = json.message || "Something went wrong. Please try again.";
          errorEl.style.display = "block";
          errorEl.scrollIntoView({ behavior: "smooth", block: "center" });
        }
      } catch (err) {
        if (errorEl) {
          errorEl.textContent = "Could not reach the server. Please check your connection and try again.";
          errorEl.style.display = "block";
        }
      } finally {
        if (submitBtn) { submitBtn.disabled = false; submitBtn.style.opacity = ""; }
      }
    });
  }

  /* ---- Auto-running brand strip ---- */
  const strip = document.querySelector(".strip");
  const stripTrack = strip ? strip.querySelector(".container") : null;
  if (strip && stripTrack && stripTrack.children.length && !strip.dataset.marqueeReady) {
    const items = Array.from(stripTrack.children);
    items.forEach(item => {
      const clone = item.cloneNode(true);
      clone.setAttribute("aria-hidden", "true");
      stripTrack.appendChild(clone);
    });
    strip.classList.add("strip--marquee");
    strip.dataset.marqueeReady = "true";
  }

  /* ---- Helpers shared by render functions ---- */
  const money = (n) => "₹" + Number(n).toLocaleString("en-IN");
  const dashCode = (code) => code ? String(code).trim().replace(/\s+/g, '-').replace(/-+/g, '-') : code;
  const mergeCode = (code) => code ? String(code).trim().replace(/[^a-zA-Z0-9]/g, '') : code;

  function renderPriceHtml(price1, price2, isDetail, customLabel1, customLabel2) {
    const sanitizeGlobal = (v, fallback) => {
      if (!v) return fallback;
      const s = String(v).trim().toLowerCase();
      if (s === 'fabio' || s === 'fabio:') return fallback;
      return v;
    };

    let curProd1 = null;
    let curProd2 = null;
    if (isDetail && window.XTRAL_CURRENT_PROD) {
      curProd1 = window.XTRAL_CURRENT_PROD.price_label_1 || null;
      curProd2 = window.XTRAL_CURRENT_PROD.price_label_2 || null;
    }

    const defaultLabel1 = sanitizeGlobal(window.XTRAL_PRICE_LABEL_1 || (window.XTRAL_SITE && window.XTRAL_SITE.price_label_1), 'Zone 1');
    const defaultLabel2 = sanitizeGlobal(window.XTRAL_PRICE_LABEL_2 || (window.XTRAL_SITE && window.XTRAL_SITE.price_label_2), 'Zone 2');
    const label1 = customLabel1 || curProd1 || defaultLabel1;
    const label2 = customLabel2 || curProd2 || defaultLabel2;
    const cleanLabel = (lbl) => String(lbl || '').trim().replace(/:+$/, '');
    const cleanL1 = cleanLabel(label1);
    const cleanL2 = cleanLabel(label2);

    const p1 = Number(price1);
    const p2 = Number(price2);
    const hasP1 = !isNaN(p1) && p1 > 0;
    const hasP2 = !isNaN(p2) && p2 > 0;

    if (hasP1 && hasP2) {
      if (isDetail) {
        return `
          <div class="pd-dual-prices">
            <div class="pd-dual-prices-inline">
              <div class="pd-price-row">
                <span class="pd-price-label">${cleanL1}:</span>
                <span class="pd-price-val">${money(p1)}</span>
              </div>
              <span class="pd-price-divider" aria-hidden="true">|</span>
              <div class="pd-price-row">
                <span class="pd-price-label">${cleanL2}:</span>
                <span class="pd-price-val">${money(p2)}</span>
              </div>
            </div>
            <small class="pd-tax-note">M.R.P. (incl. of all taxes)</small>
          </div>
        `;
      } else {
        return `
          <div class="dual-prices">
            <div class="dual-price-item"><span class="dp-label">${cleanL1}:</span> <span class="dp-val">${money(p1)}</span></div>
            <div class="dual-price-item"><span class="dp-label">${cleanL2}:</span> <span class="dp-val">${money(p2)}</span></div>
          </div>
        `;
      }
    }

    if (hasP1) {
      return isDetail
        ? `${money(p1)} <small>M.R.P. (incl. of all taxes)</small>`
        : `<span class="price">${money(p1)}</span>`;
    }

    if (hasP2) {
      return isDetail
        ? `${money(p2)} <small>M.R.P. (incl. of all taxes)</small>`
        : `<span class="price">${money(p2)}</span>`;
    }

    return isDetail ? 'On request' : '<span class="price">On request</span>';
  }

  function productCard(p) {
    const rawCode = p.code && p.code !== '—' ? mergeCode(p.code) : '';
    // URL = {id}-{code}  e.g. products/45-WB123016
    // The numeric ID is the real key; the code makes it human-readable.
    const linkId = rawCode ? `${p.id}-${rawCode}` : p.id;
    let sizeRange = "";
    if (p.variants && p.variants.type === "size" && p.variants.items.length > 0) {
      sizeRange = p.variants.items.map(v => v.name).join(", ");
    } else if (p.dimensions) {
      sizeRange = p.dimensions;
    }
    return `
      <a class="prod-card" href="products/${linkId}">
        <div class="media">
          ${p.tag ? `<span class="tag">${p.tag}</span>` : ""}
          <img src="${p.img}" alt="${p.name}" loading="lazy" decoding="async">
        </div>
        <div class="info">
          <span class="cat-lbl">${xtralCatName(p.cat)}</span>
          <h3>${p.name}</h3>
          <span class="code">Model ${dashCode(p.code)}</span>
          ${sizeRange ? `<span class="card-sizes" style="font-size:0.75rem; color:var(--muted); display:block; margin-top:2px;">Sizes: ${sizeRange}</span>` : ""}
          <div class="foot">
            ${renderPriceHtml(p.price, p.price_zone2, false, p.price_label_1, p.price_label_2)}
            <span class="view">View</span>
          </div>
        </div>
      </a>`;
  }

  /* =====================================================================
     Everything below needs catalogue data — data.js calls this once the
     API responds. Keep all data-driven rendering inside XTRAL_RENDER.
     ===================================================================== */
  window.XTRAL_RENDER = function () {

    /* ---- Home banner slider (banners managed in xadmin) ---- */
    const bannerSection = document.querySelector("[data-banner-section]");
    const bannerHost = document.querySelector("[data-banner-slider]");
    if (bannerSection && bannerHost && XTRAL_BANNERS.length) {
      bannerSection.hidden = false;
      const staticHero = document.querySelector(".hero");
      if (staticHero) staticHero.style.display = "none";

      bannerHost.innerHTML = XTRAL_BANNERS.map((b, i) => `
        ${b.link ? `<a class="banner-slide${i === 0 ? " active" : ""}" href="${b.link}">` : `<div class="banner-slide${i === 0 ? " active" : ""}">`}
          ${b.type === 'video' ? `
            <video src="${b.video}" ${i === 0 ? 'autoplay loop muted playsinline' : 'loop muted playsinline preload="none"'} style="width: 100%; height: 100%; object-fit: cover;"></video>
          ` : `
            <img src="${b.img}" alt="${b.title}" ${i === 0 ? 'fetchpriority="high"' : 'loading="lazy" decoding="async"'}>
          `}
        ${b.link ? "</a>" : "</div>"}`).join("");

      const slides = bannerHost.querySelectorAll(".banner-slide");
      let bannerIdx = 0, bannerTimer = null;

      /* Match the track's box to the active image's or video's real aspect ratio
         so the banner fills edge-to-edge with no side/letterbox gaps
         and nothing gets cropped, regardless of the upload's exact size. */
      const syncBannerAspect = () => {
        const activeSlide = slides[bannerIdx];
        if (!activeSlide) return;
        const img = activeSlide.querySelector("img");
        if (img && img.naturalWidth && img.naturalHeight) {
          bannerHost.style.aspectRatio = `${img.naturalWidth} / ${img.naturalHeight}`;
          bannerHost.style.minHeight = "";
          bannerSection.style.minHeight = "";
        } else {
          const video = activeSlide.querySelector("video");
          if (video && video.videoWidth && video.videoHeight) {
            bannerHost.style.aspectRatio = `${video.videoWidth} / ${video.videoHeight}`;
            bannerHost.style.minHeight = "";
            bannerSection.style.minHeight = "";
          }
        }
      };
      let bannerTimeout = null;

      slides.forEach((s, n) => {
        const img = s.querySelector("img");
        if (img) img.addEventListener("load", () => { if (n === bannerIdx) syncBannerAspect(); });
        const video = s.querySelector("video");
        if (video) {
          video.addEventListener("loadedmetadata", () => { if (n === bannerIdx) syncBannerAspect(); });
          video.addEventListener("timeupdate", () => {
            if (video.currentTime >= 10) {
              if (slides.length > 1 && n === bannerIdx) {
                showBanner(bannerIdx + 1);
              } else {
                video.currentTime = 0;
              }
            }
          });
          video.addEventListener("ended", () => {
            if (slides.length > 1 && n === bannerIdx) {
              showBanner(bannerIdx + 1);
            }
          });
        }
      });

      const showBanner = (i) => {
        if (bannerTimeout) clearTimeout(bannerTimeout);

        bannerIdx = (i + slides.length) % slides.length;
        let activeVideo = null;

        slides.forEach((s, n) => {
          const isActive = (n === bannerIdx);
          s.classList.toggle("active", isActive);
          const video = s.querySelector("video");
          if (video) {
            if (isActive) {
              video.currentTime = 0;
              video.play().catch(() => { });
              activeVideo = video;
            } else {
              video.pause();
            }
          }
        });
        syncBannerAspect();

        // Control autoplay transition
        if (slides.length > 1 && !activeVideo) {
          // If active slide is an image, slide in 5 seconds
          bannerTimeout = setTimeout(() => {
            showBanner(bannerIdx + 1);
          }, 5000);
        }
      };
      showBanner(0);
    }

    /* ---- Catalogue page: category PDFs (live from admin) ---- */
    const pdfHost = document.querySelector("[data-catalogue-list]");
    if (pdfHost) {
      fetch(`${API_BASE}/catalogues.php`, { cache: 'no-store' })
        .then(r => r.json())
        .then(j => {
          if (!j.success || !j.data.length) {
            pdfHost.classList.remove("cat-cards-grid--single");
            pdfHost.innerHTML = `<p class="muted" style="text-align:center;grid-column:1/-1;">No catalogues available yet.</p>`;
            return;
          }
          pdfHost.classList.toggle("cat-cards-grid--single", j.data.length === 1);
          pdfHost.innerHTML = j.data.map(c => {
            const catName = c.category || c.title.replace(' Catalogue', '');
            const coverStyle = c.thumb_url
              ? ` style="background-image:url('${c.thumb_url}');background-size:cover;background-position:center;"`
              : '';
            const sizeText = c.size_mb ? `PDF · ${c.size_mb} MB` : 'PDF Brochure';
            return `
            <div class="cat-mini-item">
              <a href="catalogue-view?pdf=${encodeURIComponent(c.url)}&title=${encodeURIComponent(c.title)}" class="cat-mini-cover-link">
                <div class="cat-mini-cover"${coverStyle}>
                  ${c.thumb_url ? '' : `
                  <div class="cat-mini-top">
                    <span>X-Tral · Look Book</span>
                    <h3>${catName}</h3>
                    <div class="cat-mini-bot">${sizeText}</div>
                  </div>
                  `}
                </div>
              </a>
              <div class="cat-mini-details">
                <h3 class="cat-mini-title">${catName}</h3>
                <span class="cat-mini-size">${sizeText}</span>
              </div>
              <div class="cat-mini-actions">
                <a class="btn btn--gold btn--sm" href="catalogue-view?pdf=${encodeURIComponent(c.url)}&title=${encodeURIComponent(c.title)}"><i class="fa-solid fa-book-open"></i> Open 3D Lookbook</a>
                <a class="btn btn--outline btn--sm" href="${c.url}" download>PDF <span class="ar">↓</span></a>
              </div>
            </div>`;
          }).join("");
        })
        .catch(err => {
          console.error("X-Tral: catalogues failed —", err);
          pdfHost.innerHTML = `<p class="muted" style="text-align:center;grid-column:1/-1;">Could not load catalogues.</p>`;
        });
    }

    /* ---- Footer: category links (live from admin) ---- */
    document.querySelectorAll("[data-footer-cats]").forEach(host => {
      host.innerHTML = XTRAL_CATEGORIES.slice(0, 4).map(c =>
        `<a href="products?cat=${c.id}">${c.name}</a>`
      ).join("") + `<a href="products">View all</a>`;
    });

    /* ---- Home: featured products + category grid ---- */
    const featuredHost = document.querySelector("[data-featured]");
    if (featuredHost) {
      // New arrivals first, then the rest
      const featured = XTRAL_PRODUCTS.slice()
        .sort((a, b) => (b.tag === "New") - (a.tag === "New"))
        .slice(0, 4);
      featuredHost.innerHTML = featured.map(productCard).join("");
    }
    const catHost = document.querySelector("[data-categories]");
    if (catHost) {
      catHost.innerHTML = XTRAL_CATEGORIES.map(c => {
        const nameLower = c.name.toLowerCase();
        let imageSrc = c.img;
        if (!imageSrc) {
          if (nameLower.includes("sanitary")) {
            imageSrc = "assets/img/sanitary_ware_premium.webp";
          } else if (nameLower.includes("fitting") || nameLower.includes("faucet")) {
            imageSrc = "assets/img/bath_fittings_premium.webp";
          } else if (nameLower.includes("sink")) {
            imageSrc = "assets/img/kitchen_sinks_premium.webp";
          } else if (nameLower.includes("wellness")) {
            imageSrc = "assets/img/wellness_premium.webp";
          } else {
            imageSrc = "assets/img/hero_premium_bg.webp";
          }
        }

        let iconSvg = "";
        if (nameLower.includes("sanitary")) {
          iconSvg = `
            <svg width="27" height="27" viewBox="0 0 24 24" fill="none">
              <path d="M7 10h10v5a5 5 0 0 1-5 5 5 5 0 0 1-5-5v-5Z" stroke="currentColor" stroke-width="1.8"/>
              <path d="M8 10V5h8v5M16 6h3M7 20h10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
          `;
        } else if (nameLower.includes("fitting") || nameLower.includes("faucet") || nameLower.includes("cock") || nameLower.includes("tap")) {
          iconSvg = `
            <svg width="27" height="27" viewBox="0 0 24 24" fill="none">
              <path d="M7 10h10M8 10V7a4 4 0 0 1 8 0v3M5 14h14M8 14v4M16 14v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
          `;
        } else if (nameLower.includes("sink")) {
          iconSvg = `
            <svg width="27" height="27" viewBox="0 0 24 24" fill="none">
              <path d="M4 11h16v5a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4v-5Z" stroke="currentColor" stroke-width="1.8"/>
              <path d="M8 11V7a4 4 0 0 1 8 0v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
          `;
        } else if (nameLower.includes("wellness") || nameLower.includes("tub") || nameLower.includes("bath")) {
          iconSvg = `
            <svg width="27" height="27" viewBox="0 0 24 24" fill="none">
              <path d="M5 12c5-8 9-8 14 0-5 8-9 8-14 0Z" stroke="currentColor" stroke-width="1.8"/>
              <path d="M12 8c-1.2 1.4-1.8 2.6-1.8 3.8 0 1.3.8 2.2 1.8 2.2s1.8-.9 1.8-2.2C13.8 10.6 13.2 9.4 12 8Z" stroke="currentColor" stroke-width="1.8"/>
            </svg>
          `;
        } else {
          iconSvg = `
            <svg width="27" height="27" viewBox="0 0 24 24" fill="none">
              <rect x="5" y="5" width="14" height="14" rx="4" stroke="currentColor" stroke-width="1.8"/>
              <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
            </svg>
          `;
        }

        return `
          <a class="cat-card cat-card--photo" href="collections?cat=${c.id}">
            <img src="${imageSrc}" alt="${c.name}" loading="lazy" decoding="async">
            <div class="cat-body">
              <h3>${c.name}</h3>
              <span class="link">Explore &rarr;</span>
            </div>
          </a>
        `;
      }).join("");

      const arrowLeft = document.querySelector("[data-cat-arrow-left]");
      const arrowRight = document.querySelector("[data-cat-arrow-right]");
      if (arrowLeft && arrowRight) {
        arrowLeft.addEventListener("click", () => {
          const cardWidth = catHost.querySelector(".cat-card")?.offsetWidth || 300;
          catHost.scrollBy({ left: -(cardWidth + 22), behavior: "smooth" });
        });
        arrowRight.addEventListener("click", () => {
          const cardWidth = catHost.querySelector(".cat-card")?.offsetWidth || 300;
          catHost.scrollBy({ left: cardWidth + 22, behavior: "smooth" });
        });
      }
    }

    /* ---- Home: new arrivals / collections series slider ---- */
    const newArrivalsHost = document.querySelector("[data-new-arrivals]");
    if (newArrivalsHost) {
      const newSeries = XTRAL_SERIES.filter(s => s.is_new_arrival);
      if (newSeries.length > 0) {
        newArrivalsHost.innerHTML = newSeries.map(s => {
          const imgUrl = s.img ? s.img : 'assets/img/hero_premium_bg.webp';
          return `
            <a class="cat-card cat-card--photo" href="products?series=${s.id}">
              <img src="${imgUrl}" alt="${s.name}" loading="lazy" decoding="async">
              <div class="cat-body">
                <h3>${s.name}</h3>
                <span class="link">Explore &rarr;</span>
              </div>
            </a>
          `;
        }).join("");

        const newSection = document.getElementById("new-arrivals-section");
        if (newSection) newSection.style.display = "block";

        const newArrowLeft = document.querySelector("[data-new-arrow-left]");
        const newArrowRight = document.querySelector("[data-new-arrow-right]");
        if (newArrowLeft && newArrowRight) {
          if (newSeries.length < 4) {
            newArrowLeft.style.display = "none";
            newArrowRight.style.display = "none";
            newArrivalsHost.style.setProperty('justify-content', 'center', 'important');
          } else {
            newArrowLeft.style.display = "block";
            newArrowRight.style.display = "block";
            newArrivalsHost.style.setProperty('justify-content', 'flex-start', 'important');

            newArrowLeft.onclick = () => {
              const cardWidth = newArrivalsHost.querySelector(".cat-card")?.offsetWidth || 300;
              newArrivalsHost.scrollBy({ left: -(cardWidth + 22), behavior: "smooth" });
            };
            newArrowRight.onclick = () => {
              const cardWidth = newArrivalsHost.querySelector(".cat-card")?.offsetWidth || 300;
              newArrivalsHost.scrollBy({ left: cardWidth + 22, behavior: "smooth" });
            };
          }
        }
      } else {
        const newSection = document.getElementById("new-arrivals-section");
        if (newSection) newSection.style.display = "none";
      }
    }

    /* ---- Category page: drill-down + filter chips + grid ----
       Category click → its sub-categories → a sub-category's series →
       that series' products → product detail. Levels come from the URL:
       ?cat=ID → sub-category tiles (or series/products if none exist)
       ?cat=ID&sub=ID → series tiles (or products if no series)
       ?series=ID → product list of that series */
    /* ---- Shared tile card renderer ---- */
    const tileCard = (href, img, name, hint) => {
      let imageSrc = img;
      if (!imageSrc) {
        const nameLower = (name || "").toLowerCase();
        if (nameLower.includes("sanitary")) {
          imageSrc = "assets/img/sanitary_ware_premium.webp";
        } else if (nameLower.includes("fitting") || nameLower.includes("faucet") || nameLower.includes("cock") || nameLower.includes("tap")) {
          imageSrc = "assets/img/bath_fittings_premium.webp";
        } else if (nameLower.includes("sink")) {
          imageSrc = "assets/img/kitchen_sinks_premium.webp";
        } else if (nameLower.includes("wellness") || nameLower.includes("tub") || nameLower.includes("bath")) {
          imageSrc = "assets/img/wellness_premium.webp";
        } else {
          imageSrc = "assets/img/hero_premium_bg.webp";
        }
      }
      return `
      <a class="cat-card cat-card--photo" href="${href}">
        <img src="${imageSrc}" alt="${name}" loading="lazy" decoding="async">
        <div class="cat-body">
          <h3>${name}</h3>
          <span class="link">${hint} &rarr;</span>
        </div>
      </a>`;
    };

    /* =====================================================================
       COLLECTIONS PAGE (collections.php)
       Shows all Categories -> on click shows Series -> on click goes to products.php?series=ID
       ===================================================================== */
    const collectionsHost = document.querySelector("[data-sub-tiles-grid]");
    if (collectionsHost) {
      const params = new URLSearchParams(location.search);
      const catId = params.get("cat");
      const titleEl = document.querySelector("[data-cat-title]");
      const subEl = document.querySelector("[data-cat-sub]");
      const eyebrowEl = document.querySelector("[data-cat-eyebrow]");

      if (catId) {
        const cat = XTRAL_CATEGORIES.find(c => c.id === catId);
        const catName = cat ? cat.name : "Category";
        const catSeries = XTRAL_SERIES.filter(s => s.cat === catId);

        if (catSeries.length > 0) {
          collectionsHost.innerHTML = catSeries.map(s =>
            tileCard(`products?cat=${catId}&series=${s.id}`, s.img, s.name, "Explore Series")
          ).join("");
          if (titleEl) titleEl.textContent = catName;
          if (subEl) subEl.textContent = (cat && cat.blurb) ? cat.blurb : "Select a series to explore designs and models.";
          if (eyebrowEl) {
            eyebrowEl.innerHTML = `<a href="collections" style="color:inherit;text-decoration:none;">Our Collection</a> <span style="opacity:0.6;">/</span> <span>${catName}</span>`;
          }
        } else {
          // If no series under category, show category products directly
          window.location.replace(`products?cat=${catId}`);
        }
      } else {
        // Top-level: All Categories
        collectionsHost.innerHTML = XTRAL_CATEGORIES.map(c =>
          tileCard(`collections?cat=${c.id}`, c.img, c.name, "Explore Collection")
        ).join("");
        if (titleEl) titleEl.textContent = "Our Collection";
        if (subEl) subEl.textContent = "Explore X-Tral's thoughtfully designed collections and series.";
        if (eyebrowEl) eyebrowEl.textContent = "Our Collection";
      }
    }

    /* =====================================================================
       PRODUCTS PAGE (products.php)
       Directly lists products with instant search, filter drawer & pagination
       ===================================================================== */
    const grid = document.querySelector("[data-product-grid]");
    if (grid) {
      const params = new URLSearchParams(location.search);
      const seriesParam = params.get("series");
      const subParam = params.get("sub");
      const catParam = params.get("cat");

      const seriesObj = seriesParam ? (XTRAL_SERIES.find(s => String(s.id) === String(seriesParam)) || null) : null;
      const subObj = (!seriesObj && subParam) ? (XTRAL_SUBCATS.find(s => String(s.id) === String(subParam)) || null) : null;

      let selectedCategory = catParam || (seriesObj ? String(seriesObj.cat) : (subObj ? String(subObj.cat) : "all"));
      if (selectedCategory !== "all" && !XTRAL_CATEGORIES.some(c => String(c.id) === String(selectedCategory))) {
        selectedCategory = "all";
      }

      let selectedSeries = seriesObj ? String(seriesObj.id) : (seriesParam || "all");
      if (selectedSeries !== "all" && !XTRAL_SERIES.some(s => String(s.id) === String(selectedSeries))) {
        selectedSeries = "all";
      }

      let selectedSub = subObj ? String(subObj.id) : (subParam || "all");

      let minPrice = null;
      let maxPrice = null;
      let currentSort = "default";

      const titleEl = document.querySelector("[data-cat-title]");
      const subEl = document.querySelector("[data-cat-sub]");
      const eyebrowEl = document.querySelector("[data-cat-eyebrow]");
      const pagHost = document.querySelector("[data-pagination]");
      const toolbarEl = document.querySelector(".toolbar");
      const filterDrawer = document.getElementById("filter-drawer");
      const toggleFilterBtn = document.getElementById("toggle-filter-btn");

      const rawPageParam = (params.get("page") || "").toLowerCase().trim();
      const pageParam = parseInt(rawPageParam, 10);
      let isLastPageRequested = rawPageParam === "last" || rawPageParam === "end";
      let currentPage = isLastPageRequested ? Infinity : ((!isNaN(pageParam) && pageParam > 0) ? pageParam : 1);
      const itemsPerPage = 12;
      let searchVal = (params.get("search") || "").toLowerCase().trim();

      function updateUrlPage(page, replace = false) {
        const url = new URL(location.href);
        const prevPageInUrl = url.searchParams.get("page");
        if (page > 1) {
          url.searchParams.set("page", page);
        } else {
          url.searchParams.delete("page");
        }
        const newPageInUrl = url.searchParams.get("page");
        if (replace && prevPageInUrl === newPageInUrl) {
          return;
        }
        if (replace) {
          history.replaceState({ page }, "", url);
        } else {
          history.pushState({ page }, "", url);
        }
      }

      function populateCategories() {
        const categoryOptionsContainer = document.querySelector("#select-category .custom-options");
        const catTriggerLabel = document.querySelector("#select-category .custom-select-trigger [data-selected-label]");
        if (!categoryOptionsContainer) return;

        let html = `<div class="custom-option${selectedCategory === "all" ? " selected" : ""}" data-value="all">All Categories</div>`;
        html += XTRAL_CATEGORIES.map(c =>
          `<div class="custom-option${String(c.id) === String(selectedCategory) ? " selected" : ""}" data-value="${c.id}">${c.name}</div>`
        ).join("");
        categoryOptionsContainer.innerHTML = html;

        if (catTriggerLabel) {
          const activeCat = XTRAL_CATEGORIES.find(c => String(c.id) === String(selectedCategory));
          catTriggerLabel.textContent = activeCat ? activeCat.name : "All Categories";
        }
      }

      function updateFilterVisibility() {
        const filterGroupCategory = document.getElementById("filter-group-category");
        const filterGroupSeries = document.getElementById("filter-group-series");
        if (!filterGroupCategory || !filterGroupSeries) return;

        // When into a series or category: hide Category section, show Series section
        if (selectedSeries !== "all" || selectedCategory !== "all") {
          filterGroupCategory.style.display = "none";
          filterGroupSeries.style.display = "flex";
        } else {
          // When on all products: show Category section, hide Series section
          filterGroupCategory.style.display = "flex";
          filterGroupSeries.style.display = "none";
        }
      }

      function populateSeries() {
        const seriesOptionsContainer = document.querySelector("#select-series .custom-options");
        const seriesTriggerLabel = document.querySelector("#select-series .custom-select-trigger [data-selected-label]");
        if (!seriesOptionsContainer) return;

        let availableSeries = XTRAL_SERIES;
        if (selectedCategory !== "all") {
          availableSeries = XTRAL_SERIES.filter(s => String(s.cat) === String(selectedCategory));
        }

        if (selectedSeries !== "all" && !availableSeries.some(s => String(s.id) === String(selectedSeries))) {
          selectedSeries = "all";
        }

        let html = `<div class="custom-option${selectedSeries === "all" ? " selected" : ""}" data-value="all">All Series</div>`;
        if (availableSeries.length > 0) {
          html += availableSeries.map(s =>
            `<div class="custom-option${String(s.id) === String(selectedSeries) ? " selected" : ""}" data-value="${s.id}">${s.name}</div>`
          ).join("");
        }

        // Option to switch category back to All Categories from the Series dropdown
        if (selectedCategory !== "all") {
          html += `<div class="custom-option" data-value="__all_categories__" style="border-top: 1px solid var(--line); font-weight: 600; color: var(--teal-700);">&larr; All Categories</div>`;
        }

        seriesOptionsContainer.innerHTML = html;

        if (seriesTriggerLabel) {
          const activeSeries = XTRAL_SERIES.find(s => String(s.id) === String(selectedSeries));
          seriesTriggerLabel.textContent = activeSeries ? activeSeries.name : "All Series";
        }

        updateFilterVisibility();
      }

      populateCategories();
      populateSeries();

      // Collapsible Filters Panel Toggle
      if (toggleFilterBtn && filterDrawer) {
        toggleFilterBtn.onclick = function (e) {
          e.preventDefault();
          const isHidden = filterDrawer.style.display === "none" || getComputedStyle(filterDrawer).display === "none";
          filterDrawer.style.display = isHidden ? "block" : "none";
          toggleFilterBtn.classList.toggle("active", isHidden);
        };
      }

      // Toggle Custom Dropdown Open/Close
      document.querySelectorAll(".custom-select").forEach(select => {
        const trigger = select.querySelector(".custom-select-trigger");
        if (trigger) {
          trigger.onclick = function (e) {
            e.stopPropagation();
            const wasOpen = select.classList.contains("open");
            document.querySelectorAll(".custom-select").forEach(s => s.classList.remove("open"));
            if (!wasOpen) select.classList.add("open");
          };
        }
      });

      // Click outside to close custom select dropdowns
      if (!window.__xtralSelectClickBound) {
        window.__xtralSelectClickBound = true;
        document.addEventListener("click", () => {
          document.querySelectorAll(".custom-select").forEach(select => {
            select.classList.remove("open");
          });
        });
      }

      // Bind Category Option Click
      const categoryOptionsContainer = document.querySelector("#select-category .custom-options");
      if (categoryOptionsContainer) {
        categoryOptionsContainer.onclick = function (e) {
          const opt = e.target.closest(".custom-option");
          if (!opt) return;

          selectedCategory = opt.dataset.value;
          selectedSeries = "all";
          selectedSub = "all";

          populateCategories();
          populateSeries();
          updateFilterVisibility();

          opt.closest(".custom-select").classList.remove("open");

          // Sync URL search params
          const url = new URL(location);
          if (selectedCategory !== "all") {
            url.searchParams.set("cat", selectedCategory);
          } else {
            url.searchParams.delete("cat");
          }
          url.searchParams.delete("sub");
          url.searchParams.delete("series");
          url.searchParams.delete("page");
          history.replaceState(null, "", url);

          currentPage = 1;
          render();
        };
      }

      // Bind Series Option Click
      const seriesOptionsContainer = document.querySelector("#select-series .custom-options");
      if (seriesOptionsContainer) {
        seriesOptionsContainer.onclick = function (e) {
          const opt = e.target.closest(".custom-option");
          if (!opt) return;

          const val = opt.dataset.value;

          if (val === "__all_categories__") {
            // User requested to switch back to All Categories
            selectedCategory = "all";
            selectedSeries = "all";
            selectedSub = "all";

            populateCategories();
            populateSeries();
            updateFilterVisibility();

            opt.closest(".custom-select").classList.remove("open");

            const url = new URL(location);
            url.searchParams.delete("cat");
            url.searchParams.delete("series");
            url.searchParams.delete("sub");
            url.searchParams.delete("page");
            history.replaceState(null, "", url);

            currentPage = 1;
            render();
            return;
          }

          selectedSeries = val;

          if (selectedSeries !== "all") {
            const foundSeries = XTRAL_SERIES.find(s => String(s.id) === String(selectedSeries));
            if (foundSeries && String(foundSeries.cat) !== String(selectedCategory)) {
              selectedCategory = String(foundSeries.cat);
              populateCategories();
            }
          }

          populateSeries();
          updateFilterVisibility();

          opt.closest(".custom-select").classList.remove("open");

          // Sync URL search params
          const url = new URL(location);
          if (selectedSeries !== "all") {
            url.searchParams.set("series", selectedSeries);
          } else {
            url.searchParams.delete("series");
          }
          if (selectedCategory !== "all") {
            url.searchParams.set("cat", selectedCategory);
          } else {
            url.searchParams.delete("cat");
          }
          url.searchParams.delete("sub");
          url.searchParams.delete("page");
          history.replaceState(null, "", url);

          currentPage = 1;
          render();
        };
      }

      const minPriceInput = document.getElementById("filter-price-min");
      if (minPriceInput) {
        minPriceInput.oninput = function (e) {
          const val = e.target.value.trim();
          minPrice = val !== "" ? Number(val) : null;
          currentPage = 1;
          updateUrlPage(1, true);
          render();
        };
      }

      const maxPriceInput = document.getElementById("filter-price-max");
      if (maxPriceInput) {
        maxPriceInput.oninput = function (e) {
          const val = e.target.value.trim();
          maxPrice = val !== "" ? Number(val) : null;
          currentPage = 1;
          updateUrlPage(1, true);
          render();
        };
      }

      const searchInput = document.getElementById("catalog-search");
      if (searchInput) {
        if (searchVal) searchInput.value = params.get("search");
        searchInput.oninput = function (e) {
          searchVal = e.target.value.toLowerCase().trim();
          currentPage = 1;
          const url = new URL(location.href);
          if (searchVal) {
            url.searchParams.set("search", e.target.value.trim());
          } else {
            url.searchParams.delete("search");
          }
          url.searchParams.delete("page");
          history.replaceState({ page: 1 }, "", url);
          render();
        };
      }

      // Bind Sort By
      const sortOptionsContainer = document.querySelector("#select-sort .custom-options");
      const sortTriggerLabel = document.querySelector("#select-sort .custom-select-trigger [data-selected-label]");
      if (sortOptionsContainer) {
        sortOptionsContainer.onclick = function (e) {
          const opt = e.target.closest(".custom-option");
          if (!opt) return;

          currentSort = opt.dataset.value;
          sortOptionsContainer.querySelectorAll(".custom-option").forEach(o => o.classList.remove("selected"));
          opt.classList.add("selected");
          if (sortTriggerLabel) sortTriggerLabel.textContent = opt.textContent;

          opt.closest(".custom-select").classList.remove("open");
          currentPage = 1;
          updateUrlPage(1, true);
          render();
        };
      }

      // Apply button (Done) - closes the drawer
      const applyBtn = document.getElementById("filter-apply");
      if (applyBtn) {
        applyBtn.onclick = function () {
          if (filterDrawer) filterDrawer.style.display = "none";
          if (toggleFilterBtn) toggleFilterBtn.classList.remove("active");
        };
      }

      // Reset button
      const resetBtn = document.getElementById("filter-reset");
      if (resetBtn) {
        resetBtn.onclick = function () {
          selectedCategory = "all";
          selectedSeries = "all";
          selectedSub = "all";
          minPrice = null;
          maxPrice = null;
          currentSort = "default";
          searchVal = "";

          if (minPriceInput) minPriceInput.value = "";
          if (maxPriceInput) maxPriceInput.value = "";
          if (searchInput) searchInput.value = "";

          if (sortTriggerLabel) sortTriggerLabel.textContent = "Default / Featured";
          if (sortOptionsContainer) {
            sortOptionsContainer.querySelectorAll(".custom-option").forEach(o => {
              if (o.dataset.value === "default") o.classList.add("selected");
              else o.classList.remove("selected");
            });
          }

          populateCategories();
          populateSeries();

          const url = new URL(location);
          url.searchParams.delete("cat");
          url.searchParams.delete("series");
          url.searchParams.delete("sub");
          url.searchParams.delete("search");
          url.searchParams.delete("page");
          history.replaceState(null, "", url);

          currentPage = 1;
          render();

          if (filterDrawer) filterDrawer.style.display = "none";
          if (toggleFilterBtn) toggleFilterBtn.classList.remove("active");
        };
      }

      function render() {
        if (toolbarEl) toolbarEl.style.display = "flex";
        if (pagHost) pagHost.style.display = "flex";
        if (grid) grid.style.display = "grid";

        let list = XTRAL_PRODUCTS.slice();

        // 1. Category Filter
        if (selectedCategory !== "all") {
          list = list.filter(p => String(p.cat) === String(selectedCategory));
        }

        // 2. Series Filter
        if (selectedSeries !== "all") {
          const targetSeries = XTRAL_SERIES.find(s => String(s.id) === String(selectedSeries));
          const targetName = targetSeries ? targetSeries.name.trim().toLowerCase() : "";
          list = list.filter(p => {
            if (p.seriesId && String(p.seriesId) === String(selectedSeries)) return true;
            if (targetName && p.series && p.series.trim().toLowerCase() === targetName) return true;
            return false;
          });
        }

        // 3. Subcategory Filter if any
        if (selectedSub !== "all") {
          list = list.filter(p => p.subId && String(p.subId) === String(selectedSub));
        }

        function cleanAlnum(str) {
          return (str || "").toLowerCase().replace(/[^a-z0-9]/g, "");
        }

        function productMatchesSearch(p, query) {
          if (!query) return true;

          const rawQuery = query.trim().toLowerCase();
          const cleanQuery = cleanAlnum(rawQuery);
          if (!cleanQuery) return true;

          // 1. Gather all product & variant codes
          const codes = [];
          if (p.code && p.code !== "—") codes.push(String(p.code));
          if (p.variants && p.variants.items) {
            p.variants.items.forEach(v => {
              if (v.code && v.code !== "—") codes.push(String(v.code));
            });
          }

          const cleanCodes = codes.map(c => cleanAlnum(c)).filter(Boolean);

          const codeDirectMatch = cleanCodes.some(c => c.includes(cleanQuery) || cleanQuery.includes(c)) ||
            codes.some(c => c.toLowerCase().includes(rawQuery));

          if (codeDirectMatch) return true;

          // 2. Multi-token match across name, series, category, subcategory, codes and variants
          const tokens = rawQuery.split(/[\s,]+/).filter(Boolean);

          const name = (p.name || "").toLowerCase();
          const cleanName = cleanAlnum(name);

          const catName = p.cat ? xtralCatName(p.cat).toLowerCase() : "";
          const cleanCat = cleanAlnum(catName);

          const curSeriesObj = p.seriesId ? XTRAL_SERIES.find(s => s.id === p.seriesId) : null;
          const series = curSeriesObj ? curSeriesObj.name.toLowerCase() : (p.series ? p.series.toLowerCase() : "");
          const cleanSeries = cleanAlnum(series);

          const curSubObj = p.subId ? XTRAL_SUBCATS.find(s => s.id === p.subId) : null;
          const subName = curSubObj ? curSubObj.name.toLowerCase() : "";
          const cleanSub = cleanAlnum(subName);

          const variantNames = (p.variants && p.variants.items)
            ? p.variants.items.map(v => (v.name || "").toLowerCase()).join(" ")
            : "";

          const allCodesStr = codes.join(" ").toLowerCase();
          const combinedText = `${name} ${series} ${catName} ${subName} ${allCodesStr} ${variantNames}`.toLowerCase();
          const combinedClean = cleanAlnum(combinedText);

          return tokens.every(token => {
            const cleanToken = cleanAlnum(token);
            if (!cleanToken) return true;

            return combinedText.includes(token) ||
              cleanCodes.some(c => c.includes(cleanToken)) ||
              cleanName.includes(cleanToken) ||
              cleanSeries.includes(cleanToken) ||
              cleanCat.includes(cleanToken) ||
              cleanSub.includes(cleanToken) ||
              combinedClean.includes(cleanToken);
          });
        }

        if (searchVal) {
          let filtered = list.filter(p => productMatchesSearch(p, searchVal));
          if (filtered.length === 0 && list.length < XTRAL_PRODUCTS.length) {
            const allMatches = XTRAL_PRODUCTS.filter(p => productMatchesSearch(p, searchVal));
            if (allMatches.length > 0) {
              filtered = allMatches;
            }
          }
          list = filtered;
        }

        if (minPrice !== null || maxPrice !== null) {
          list = list.filter(p => {
            const matchesMainPrice = p.price &&
              (minPrice === null || p.price >= minPrice) &&
              (maxPrice === null || p.price <= maxPrice);

            const matchesVariantPrices = p.variants && p.variants.items &&
              p.variants.items.some(v => v.price &&
                (minPrice === null || v.price >= minPrice) &&
                (maxPrice === null || v.price <= maxPrice)
              );

            return matchesMainPrice || matchesVariantPrices;
          });
        }

        // Apply Sorting
        if (currentSort === "price-low") {
          list.sort((a, b) => (a.price || 0) - (b.price || 0));
        } else if (currentSort === "price-high") {
          list.sort((a, b) => (b.price || 0) - (a.price || 0));
        } else if (currentSort === "newest") {
          list.sort((a, b) => {
            const aNew = a.tag === "New" ? 1 : 0;
            const bNew = b.tag === "New" ? 1 : 0;
            if (bNew !== aNew) return bNew - aNew;
            return Number(b.id) - Number(a.id);
          });
        } else {
          // Default sorting:
          // In a series: sort by custom display_order set for that series
          // On main product section (all products / no series): sort by primary database ID (id ASC)
          if (selectedSeries !== "all") {
            list.sort((a, b) => (a.displayOrder || 0) - (b.displayOrder || 0) || Number(a.id) - Number(b.id));
          } else {
            list.sort((a, b) => Number(a.id) - Number(b.id));
          }
        }

        // Update match counter
        const matchCountEl = document.querySelector("[data-match-count]");
        if (matchCountEl) {
          if (list.length === 0) {
            matchCountEl.textContent = "No products found";
          } else if (list.length === 1) {
            matchCountEl.textContent = "1 product found";
          } else {
            matchCountEl.textContent = `${list.length} products found`;
          }
        }

        // Dynamic banner titles and breadcrumbs
        if (selectedSeries !== "all") {
          const curSeries = XTRAL_SERIES.find(s => String(s.id) === String(selectedSeries));
          const curCat = XTRAL_CATEGORIES.find(c => String(c.id) === String(curSeries ? curSeries.cat : selectedCategory));
          const catName = curCat ? curCat.name : "Category";
          const sName = curSeries ? curSeries.name : "Series";
          if (titleEl) titleEl.textContent = sName;
          if (subEl) subEl.textContent = (curSeries && curSeries.blurb) ? curSeries.blurb : (curCat && curCat.blurb ? curCat.blurb : `All products in the ${sName} series.`);
          if (eyebrowEl) {
            eyebrowEl.innerHTML = `<a href="collections" style="color:inherit;text-decoration:none;">Our Collection</a> <span style="opacity:0.6;">/</span> <a href="products?cat=${curCat ? curCat.id : ''}" style="color:inherit;text-decoration:none;">${catName}</a> <span style="opacity:0.6;">/</span> <span>${sName}</span>`;
          }
        } else if (selectedCategory !== "all") {
          const curCat = XTRAL_CATEGORIES.find(c => String(c.id) === String(selectedCategory));
          const catName = curCat ? curCat.name : "Category";
          if (titleEl) titleEl.textContent = catName;
          if (subEl) subEl.textContent = (curCat && curCat.blurb) ? curCat.blurb : "Browse the complete range.";
          if (eyebrowEl) {
            eyebrowEl.innerHTML = `<a href="collections" style="color:inherit;text-decoration:none;">Our Collection</a> <span style="opacity:0.6;">/</span> <span>${catName}</span>`;
          }
        } else {
          if (titleEl) titleEl.textContent = "Our Products";
          if (subEl) subEl.textContent = "Browse the complete X-Tral range across every category.";
          if (eyebrowEl) eyebrowEl.textContent = "Our Products";
        }

        const totalPages = Math.ceil(list.length / itemsPerPage);
        if (totalPages > 0 && currentPage > totalPages) {
          currentPage = totalPages;
          updateUrlPage(currentPage, true);
        } else if (currentPage < 1) {
          currentPage = 1;
          updateUrlPage(currentPage, true);
        } else if (params.has("page") && (isNaN(pageParam) || pageParam <= 0) && !isLastPageRequested) {
          currentPage = 1;
          updateUrlPage(currentPage, true);
        }

        // Slice list for the current page
        const start = (currentPage - 1) * itemsPerPage;
        const paginatedList = list.slice(start, start + itemsPerPage);

        grid.innerHTML = paginatedList.length
          ? paginatedList.map(productCard).join("")
          : `<div class="empty">No products match the selected criteria.</div>`;

        // Render pagination buttons
        if (pagHost) {
          if (totalPages <= 1) {
            pagHost.innerHTML = "";
          } else {
            let html = "";
            html += `<button class="pagination-btn${currentPage === 1 ? ' disabled' : ''}" data-page="${currentPage - 1}">&lt;</button>`;

            const range = [];
            const delta = 1;
            for (let i = 1; i <= totalPages; i++) {
              if (i === 1 || i === totalPages || (i >= currentPage - delta && i <= currentPage + delta)) {
                range.push(i);
              } else if (range[range.length - 1] !== "...") {
                range.push("...");
              }
            }

            range.forEach(p => {
              if (p === "...") {
                html += `<span class="pagination-ellipsis">...</span>`;
              } else {
                html += `<button class="pagination-btn${p === currentPage ? ' active' : ''}" data-page="${p}">${p}</button>`;
              }
            });

            html += `<button class="pagination-btn${currentPage === totalPages ? ' disabled' : ''}" data-page="${currentPage + 1}">&gt;</button>`;
            pagHost.innerHTML = html;
          }
        }
      }

      if (pagHost) {
        pagHost.onclick = function (e) {
          const btn = e.target.closest(".pagination-btn");
          if (!btn || btn.classList.contains("disabled")) return;
          const targetPage = parseInt(btn.dataset.page, 10);
          if (isNaN(targetPage) || targetPage === currentPage) return;
          currentPage = targetPage;
          updateUrlPage(currentPage, false);
          render();

          const scrollTarget = document.querySelector(".toolbar") || grid;
          if (scrollTarget) {
            scrollTarget.scrollIntoView({ behavior: "smooth", block: "start" });
          }
        };
      }

      if (!window.__xtralPopstateBound) {
        window.__xtralPopstateBound = true;
        window.addEventListener("popstate", () => {
          const popParams = new URLSearchParams(location.search);
          const rawP = (popParams.get("page") || "").toLowerCase().trim();
          const p = parseInt(rawP, 10);
          currentPage = (rawP === "last" || rawP === "end") ? Infinity : ((!isNaN(p) && p > 0) ? p : 1);

          const s = popParams.get("search") || "";
          if (searchInput && searchInput.value !== s) {
            searchInput.value = s;
            searchVal = s.toLowerCase().trim();
          }

          const c = popParams.get("cat");
          const ser = popParams.get("series");
          const seriesMatch = ser ? XTRAL_SERIES.find(x => String(x.id) === String(ser)) : null;

          selectedCategory = c || (seriesMatch ? String(seriesMatch.cat) : "all");
          selectedSeries = ser || "all";

          populateCategories();
          populateSeries();
          render();
        });
      }

      render();
    }

    /* ---- Product detail page ---- */
    const pdHost = document.querySelector("[data-product-detail]");
    if (pdHost && XTRAL_PRODUCTS.length) {
      // New URL format: /products/{id}-{code}  e.g. /products/45-WB123016
      // Legacy format still supported: /products/{code}  e.g. /products/WB123016
      let rawId = new URLSearchParams(location.search).get("id");
      if (!rawId) {
        const pathMatch = location.pathname.match(/(?:products|product)\/([^/?#]+)/i);
        if (pathMatch) {
          rawId = decodeURIComponent(pathMatch[1]);
        }
      }
      const rawTrim = (rawId || "").trim();
      const id = dashCode(rawTrim).toLowerCase();
      const cleanId = rawTrim.replace(/[^a-zA-Z0-9]/g, '').toLowerCase();

      // Check if URL starts with a numeric ID (new format: "45-WB123016" or just "45")
      const numericPrefixMatch = rawTrim.match(/^(\d+)(?:-.*)?$/);
      let p;
      if (numericPrefixMatch) {
        // New format: find by DB id first — always unique
        const numericId = numericPrefixMatch[1];
        p = XTRAL_PRODUCTS.find(x => String(x.id) === numericId);
      }
      if (!p) {
        // Legacy fallback: match by product code or variant code
        p = XTRAL_PRODUCTS.find(x => {
          const prodId = String(x.id).trim().toLowerCase();
          const prodCode = x.code && x.code !== '—' ? dashCode(String(x.code).trim()).toLowerCase() : "";
          const cleanProdCode = x.code && x.code !== '—' ? String(x.code).replace(/[^a-zA-Z0-9]/g, '').toLowerCase() : "";
          if (prodId === id || (prodCode && prodCode === id) || (cleanId && cleanProdCode === cleanId)) return true;
          if (x.variants && x.variants.items && x.variants.items.length > 0) {
            return x.variants.items.some(v => {
              if (!v.code) return false;
              const vCode = dashCode(String(v.code).trim()).toLowerCase();
              const cleanVCode = String(v.code).replace(/[^a-zA-Z0-9]/g, '').toLowerCase();
              return vCode === id || (cleanId && cleanVCode === cleanId);
            });
          }
          return false;
        });
      }
      p = p || XTRAL_PRODUCTS[0];
      if (window.XTRAL_CURRENT_PROD) {
        if (!p) p = {};
        if (window.XTRAL_CURRENT_PROD.price_label_1 && (!p.price_label_1 || window.XTRAL_CURRENT_PROD.price_label_1 !== 'Zone 1')) {
          p.price_label_1 = window.XTRAL_CURRENT_PROD.price_label_1;
        }
        if (window.XTRAL_CURRENT_PROD.price_label_2 && (!p.price_label_2 || window.XTRAL_CURRENT_PROD.price_label_2 !== 'Zone 2')) {
          p.price_label_2 = window.XTRAL_CURRENT_PROD.price_label_2;
        }
      }

      document.title = (p.name || "Product") + " — X-Tral";
      // Fill EVERY matching element (name appears in both breadcrumb and title)
      const set = (sel, val) => { document.querySelectorAll(sel).forEach(el => { el.innerHTML = val; }); };
      // Keep address bar in new {id}-{code} form (e.g. products/45-WB123016)
      const syncUrlId = (newRawId) => {
        if (!p) return;
        const code = newRawId && String(newRawId).trim() !== '' ? mergeCode(String(newRawId).trim()) : '';
        const slug = code ? `${p.id}-${code}` : p.id;
        const currentPath = location.pathname;
        if (/(?:products|product)\//i.test(currentPath)) {
          const newPath = currentPath.replace(/(?:products|product)\/[^/?#]+/i, `products/${encodeURIComponent(slug)}`);
          history.replaceState(null, "", newPath);
        } else {
          history.replaceState(null, "", `products/${encodeURIComponent(slug)}`);
        }
      };

      set("[data-pd-cat]", xtralCatName(p.cat));
      const representationNotice = document.querySelector("[data-pd-representation-notice]");
      if (representationNotice) {
        const rangeName = `${p.series || ''} ${XTRAL_SERIES.find(s => s.id === p.seriesId)?.name || ''} ${xtralCatName(p.cat)}`;
        representationNotice.hidden = !/\b(?:vanitys|vanities|vanity|(?:quardz|quartz)\s+sinks?)\b/i.test(rangeName);
      }
      set("[data-pd-name]", p.name);
      set("[data-pd-code]", "Model " + dashCode(p.code));

      const pdCrumb = document.querySelector("[data-pd-crumb]");
      if (pdCrumb && pdCrumb.parentElement) {
        const catName = xtralCatName(p.cat);
        const seriesObj = XTRAL_SERIES.find(s => s.id === p.seriesId);
        if (seriesObj) {
          pdCrumb.parentElement.innerHTML = `
            <a href="index">Home</a><span class="sep">/</span>
            <a href="products">Collections</a><span class="sep">/</span>
            <a href="products?cat=${p.cat}">${catName}</a><span class="sep">/</span>
            <a href="products?cat=${p.cat}&series=${seriesObj.id}">${seriesObj.name}</a><span class="sep">/</span>
            <span data-pd-name>${p.name}</span>
          `;
        } else {
          pdCrumb.textContent = catName;
          pdCrumb.href = `products?cat=${p.cat}`;
        }
      }
      set("[data-pd-price]", renderPriceHtml(p.price, p.price_zone2, true, p.price_label_1, p.price_label_2));

      const updateDimensions = (dimensionsVal) => {
        const dimEl = document.querySelector("[data-pd-dimensions]");
        if (dimEl) {
          if (dimensionsVal && dimensionsVal.trim() !== "" && dimensionsVal.trim() !== "—") {
            dimEl.innerHTML = "Dimensions: " + dimensionsVal;
            dimEl.style.display = "block";
          } else {
            dimEl.style.display = "none";
          }
        }
      };

      // Get dimensions with fallback to parsing specs
      let productDimensions = p.dimensions;
      if (!productDimensions || productDimensions.trim() === "" || productDimensions.trim() === "—") {
        if (Array.isArray(p.specs)) {
          const len = p.specs.find(s => s && s[0] && s[0].toLowerCase().includes("length"))?.[1];
          const wid = p.specs.find(s => s && s[0] && s[0].toLowerCase().includes("width"))?.[1];
          const hei = p.specs.find(s => s && s[0] && s[0].toLowerCase().includes("height"))?.[1];
          if (len && wid && hei) {
            productDimensions = `${len} x ${wid} x ${hei}`;
          } else if (len && wid) {
            productDimensions = `${len} x ${wid}`;
          }
        }
      }
      updateDimensions(productDimensions);

      /* ---- Description: divider block, 3-line clamp, click "…" to expand ---- */
      const descBlock = document.querySelector("[data-pd-desc-block]");
      const descEl = document.querySelector("[data-pd-short-desc]");
      const descToggle = document.querySelector("[data-pd-desc-toggle]");
      if (descBlock && descEl) {
        const descHtml = p.descHtml || p.short || "";
        if (descHtml) {
          descBlock.hidden = false;
          descEl.innerHTML = descHtml;
          // Show the Read more toggle only when the text actually overflows 3 lines
          requestAnimationFrame(() => {
            if (descToggle) descToggle.hidden = descEl.scrollHeight <= descEl.clientHeight + 2;
          });
          const toggleDesc = (e) => {
            if (e) {
              e.preventDefault();
              e.stopPropagation();
            }
            const expanded = descEl.classList.toggle("expanded");
            if (descToggle) descToggle.textContent = expanded ? "Read less" : "Read more";
          };
          if (descToggle) descToggle.onclick = toggleDesc;
          // Clicking the clamped text (the "…") also opens the full description
          descEl.onclick = () => {
            if (!descEl.classList.contains("expanded")) toggleDesc();
          };
        } else {
          descBlock.hidden = true;
        }
      }
      set("[data-pd-dimensions-val]", p.dimensions || "—");
      set("[data-pd-hsn-val]", p.hsn || "—");
      let updateMediaGallery;
      let initialSelectedVariantImg = null;

      /* ---- Product variants ---- */
      const varHost = document.querySelector("[data-pd-variants]");
      if (varHost) {
        if (p.variants && p.variants.items && p.variants.items.length > 0) {
          const v = p.variants;
          let initialIdx = 0;
          if (id || cleanId) {
            const matchIdx = v.items.findIndex(item => {
              if (!item.code) return false;
              const ic = String(item.code).trim().toLowerCase();
              const icClean = ic.replace(/[^a-zA-Z0-9]/g, '');
              return dashCode(ic) === id || (cleanId && icClean === cleanId);
            });
            if (matchIdx >= 0) initialIdx = matchIdx;
          }

          let optionsHTML = "";

          if (v.type === "color") {
            optionsHTML = v.items.map((item, idx) => {
              const swatchStyle = item.textureImg
                ? `background-image: url('${item.textureImg}'); background-size: cover; background-position: center;`
                : `background-color: ${item.val};`;
              return `
              <button class="color-swatch${idx === initialIdx ? ' active' : ''}"
                      title="${item.name}"
                      data-index="${idx}"
                      style="${swatchStyle}"
                      aria-label="${item.name}">
              </button>
            `;
            }).join("");
          } else if (v.type === "size") {
            optionsHTML = v.items.map((item, idx) => `
              <button class="size-pill${idx === initialIdx ? ' active' : ''}"
                      data-index="${idx}">
                ${item.name}
              </button>
            `).join("");
          }

          varHost.innerHTML = `
            <div class="variant-group">
              <span class="variant-label">${v.label}</span>
              <div class="variant-options">
                ${optionsHTML}
              </div>
            </div>
          `;

          const updateColorSizes = (item) => {
            let sizeGroup = varHost.querySelector(".color-sizes-group");
            if (!item || !item.sizes || item.sizes.length === 0) {
              if (sizeGroup) sizeGroup.remove();
              updateDimensions(productDimensions);
              return;
            }

            // A single size is already shown in the "Dimensions:" line — no picker needed
            if (item.sizes.length === 1) {
              if (sizeGroup) sizeGroup.remove();
              const sz = item.sizes[0];
              const p1 = (sz.price !== null && sz.price !== undefined && sz.price !== '') ? sz.price : item.price;
              const p2 = (sz.price_zone2 !== null && sz.price_zone2 !== undefined && sz.price_zone2 !== '') ? sz.price_zone2 : item.price_zone2;
              set("[data-pd-price]", renderPriceHtml(p1, p2, true, item.price_label_1 || p.price_label_1, item.price_label_2 || p.price_label_2));
              updateDimensions(sz.dimension || productDimensions);
              return;
            }

            if (!sizeGroup) {
              sizeGroup = document.createElement("div");
              sizeGroup.className = "variant-group color-sizes-group";
              sizeGroup.style.marginTop = "14px";
              varHost.appendChild(sizeGroup);
            }

            sizeGroup.innerHTML = `
              <span class="variant-label">Size / Dimension</span>
              <div class="variant-options">
                ${item.sizes.map((sz, sIdx) => `
                  <button type="button" class="size-pill color-size-pill${sIdx === 0 ? ' active' : ''}" data-size-idx="${sIdx}">
                    ${sz.dimension}
                  </button>
                `).join("")}
              </div>
            `;

            const applySize = (sz) => {
              const p1 = (sz.price !== null && sz.price !== undefined && sz.price !== '') ? sz.price : item.price;
              const p2 = (sz.price_zone2 !== null && sz.price_zone2 !== undefined && sz.price_zone2 !== '') ? sz.price_zone2 : item.price_zone2;
              set("[data-pd-price]", renderPriceHtml(p1, p2, true, item.price_label_1 || p.price_label_1, item.price_label_2 || p.price_label_2));
              if (sz.dimension) {
                updateDimensions(sz.dimension);
              }
            };

            applySize(item.sizes[0]);

            sizeGroup.onclick = (e) => {
              const sBtn = e.target.closest(".color-size-pill");
              if (!sBtn) return;
              sizeGroup.querySelectorAll(".color-size-pill").forEach(el => el.classList.remove("active"));
              sBtn.classList.add("active");
              const sIdx = parseInt(sBtn.dataset.sizeIdx, 10);
              if (item.sizes[sIdx]) {
                applySize(item.sizes[sIdx]);
              }
            };
          };

          // Initialize with matching variant
          const applyVariant = (item) => {
            set("[data-pd-code]", "Model " + dashCode(item.code));
            set("[data-pd-price]", renderPriceHtml(item.price, item.price_zone2, true, item.price_label_1 || p.price_label_1, item.price_label_2 || p.price_label_2));

            // Update main image and gallery for the variant (or fallback to primary)
            if (typeof updateMediaGallery === 'function') {
              updateMediaGallery(item ? item.img : null);
            }

            // If it's a size variant, update dimensions display with variant's size
            if (v.type === "size" && item && item.name) {
              updateDimensions(item.name);
            } else if (v.type === "color") {
              updateColorSizes(item);
            }
          };

          if (v.items[initialIdx]) {
            initialSelectedVariantImg = v.items[initialIdx].img || null;
            applyVariant(v.items[initialIdx]);
            syncUrlId(v.items[initialIdx].code || p.code);
          } else if (p.code && p.code !== '—') {
            syncUrlId(p.code);
          }

          // Handle variant selection click
          varHost.onclick = (e) => {
            if (e.target.closest(".color-sizes-group")) return;
            const btn = e.target.closest(".color-swatch, .size-pill");
            if (!btn) return;
            varHost.querySelectorAll(".color-swatch, .size-pill:not(.color-size-pill)").forEach(el => el.classList.remove("active"));
            btn.classList.add("active");
            const idx = parseInt(btn.dataset.index, 10);
            if (v.items[idx]) {
              applyVariant(v.items[idx]);
              syncUrlId(v.items[idx].code);
            }
          };
        } else {
          varHost.innerHTML = ""; // Clear if no variants
        }
      }

      const crumbCat = document.querySelector("[data-pd-crumb]");
      if (crumbCat) { crumbCat.textContent = xtralCatName(p.cat); crumbCat.href = "products?cat=" + p.cat; }

      set("[data-pd-specs]", (Array.isArray(p.specs) ? p.specs : []).map(s => `<tr><th>${s[0]}</th><td>${s[1]}</td></tr>`).join(""));

      // Render features with custom icons
      const featuresHost = document.querySelector("[data-pd-features]");
      const featuresWrap = document.querySelector("[data-pd-features-wrap]");
      if (featuresHost) {
        if (p.features && p.features.length > 0) {
          featuresHost.innerHTML = p.features.map(f => {
            const hasIconUrl = f[0] && (f[0].includes('/') || f[0].startsWith('http'));
            const iconHtml = hasIconUrl
              ? `<img src="${f[0]}" alt="${f[1]}">`
              : f[0];

            // Truncate name at 20 chars so it never overflows the icon card
            const name = f[1] || '';
            const displayName = name.length > 20 ? name.slice(0, 17) + '...' : name;

            return `
              <article class="feature">
                <div class="ic">${iconHtml}</div>
                <h4>${displayName}</h4>
              </article>
            `;
          }).join("");
          if (featuresWrap) featuresWrap.style.display = "block";
        } else {
          featuresHost.innerHTML = "";
          if (featuresWrap) featuresWrap.style.display = "none";
        }
      }

      /* ---- Media Gallery & Lightbox Popup (Zoom View) ---- */
      const thumbsHost = document.querySelector("[data-pd-thumbs]");
      const mainImgEl = document.querySelector("[data-pd-img]");
      const videoContainer = document.querySelector("[data-pd-video-container]");
      const mediaContainer = document.querySelector("[data-pd-media-container]");
      const thumbsWrapper = document.querySelector(".pd-thumbs-wrapper");

      const prevMediaBtn = mediaContainer ? mediaContainer.querySelector(".pd-media-arrow--prev") : null;
      const nextMediaBtn = mediaContainer ? mediaContainer.querySelector(".pd-media-arrow--next") : null;
      const prevThumbArrow = thumbsWrapper ? thumbsWrapper.querySelector(".pd-thumbs-arrow--prev") : null;
      const nextThumbArrow = thumbsWrapper ? thumbsWrapper.querySelector(".pd-thumbs-arrow--next") : null;

      const mediaList = [];
      const productImages = p.imgs && p.imgs.length > 0 ? p.imgs : [p.img];
      const defaultPrimaryImg = (productImages.length > 0 && productImages[0]) ? productImages[0] : (p.img || '');
      const baseGalleryImgs = productImages.length > 1 ? productImages.slice(1) : [];
      let currentMainIdx = 0;

      const getYouTubeEmbedUrl = (url) => {
        if (!url) return null;
        const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
        const match = url.match(regExp);
        if (match && match[2].length === 11) {
          const videoId = match[2];
          return `https://www.youtube.com/embed/${videoId}?autoplay=1&mute=1&loop=1&playlist=${videoId}`;
        }
        return null;
      };

      const getVimeoEmbedUrl = (url) => {
        if (!url) return null;
        const regExp = /vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/;
        const match = url.match(regExp);
        if (match && match[3]) {
          const videoId = match[3];
          return `https://player.vimeo.com/video/${videoId}?autoplay=1&muted=1&loop=1`;
        }
        return null;
      };

      const getVideoHtml = (url, isLightbox = false) => {
        const ytUrl = getYouTubeEmbedUrl(url);
        if (ytUrl) {
          const embedUrl = ytUrl.includes('?') 
            ? `${ytUrl}&mute=1` 
            : `${ytUrl}?autoplay=1&mute=1&loop=1`;
          return `<iframe src="${embedUrl}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="width:100%; height:100%; display:block;"></iframe>`;
        }

        const vimeoUrl = getVimeoEmbedUrl(url);
        if (vimeoUrl) {
          const embedUrl = vimeoUrl.includes('?') 
            ? `${vimeoUrl}&muted=1` 
            : `${vimeoUrl}?autoplay=1&muted=1&loop=1`;
          return `<iframe src="${embedUrl}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="width:100%; height:100%; display:block;"></iframe>`;
        }

        if (isLightbox) {
          return `<video src="${url}" controls autoplay muted playsinline class="rounded bg-black"></video>`;
        } else {
          return `<video src="${url}" autoplay muted loop playsinline></video>`;
        }
      };

      const showMedia = (mediaItem) => {
        if (!mediaItem) return;
        currentMainIdx = mediaItem.index;

        // Auto-select corresponding thumbnail
        if (thumbsHost) {
          thumbsHost.querySelectorAll(".pd-thumb").forEach(t => t.classList.remove("active"));
          const activeThumb = thumbsHost.querySelector(`.pd-thumb[data-index="${currentMainIdx}"]`);
          if (activeThumb) {
            activeThumb.classList.add("active");
            activeThumb.scrollIntoView({ behavior: "smooth", block: "nearest", inline: "nearest" });
          }
        }

        if (mediaItem.type === 'video') {
          if (mainImgEl) mainImgEl.style.display = "none";
          if (videoContainer) {
            videoContainer.style.display = "block";
            const videoContentHtml = getVideoHtml(mediaItem.url, false);
            const isIframe = videoContentHtml.includes("<iframe");

            videoContainer.innerHTML = `
              <div class="pd-media-video-container">
                ${videoContentHtml}
                ${!isIframe ? `
                  <div class="pd-media-video-play-overlay">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                  </div>
                  <button type="button" class="pd-media-video-lightbox-btn" title="View Fullscreen" aria-label="Fullscreen">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="15 3 21 3 21 9"></polyline>
                      <polyline points="9 21 3 21 3 15"></polyline>
                      <line x1="21" y1="3" x2="14" y2="10"></line>
                      <line x1="3" y1="21" x2="10" y2="14"></line>
                    </svg>
                  </button>
                ` : ''}
                <div class="pd-media-video-click-overlay" style="position: absolute; top:0; left:0; width:100%; height:100%; cursor:pointer; background:transparent; z-index:2;"></div>
              </div>
            `;

            const clickOverlay = videoContainer.querySelector(".pd-media-video-click-overlay");
            const playOverlay = videoContainer.querySelector(".pd-media-video-play-overlay");
            const videoEl = videoContainer.querySelector("video");
            const lbBtn = videoContainer.querySelector(".pd-media-video-lightbox-btn");

            if (lbBtn) {
              lbBtn.addEventListener("click", (e) => {
                e.stopPropagation();
                openLightbox(mediaItem.index);
              });
            }

            if (videoEl && playOverlay) {
              let hideTimer = null;

              // Strictly enforce mute and zero volume so no audio can play
              videoEl.muted = true;
              videoEl.volume = 0;
              videoEl.addEventListener("volumechange", () => {
                if (!videoEl.muted || videoEl.volume > 0) {
                  videoEl.muted = true;
                  videoEl.volume = 0;
                }
              });

              const setIcon = (isPlaying) => {
                if (isPlaying) {
                  playOverlay.innerHTML = `<svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>`;
                } else {
                  playOverlay.innerHTML = `<svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor" style="margin-left:3px;"><path d="M8 5v14l11-7z"/></svg>`;
                }
              };

              const hideOverlay = (delay = 1200) => {
                if (hideTimer) clearTimeout(hideTimer);
                hideTimer = setTimeout(() => {
                  if (videoEl && !videoEl.paused) {
                    playOverlay.classList.add("is-hidden");
                  }
                }, delay);
              };

              const showOverlay = () => {
                if (hideTimer) clearTimeout(hideTimer);
                playOverlay.classList.remove("is-hidden");
              };

              // When video starts playing, fade out within 1-2 seconds
              videoEl.addEventListener("play", () => {
                videoEl.muted = true;
                videoEl.volume = 0;
                setIcon(true);
                hideOverlay(1200);
              });

              // When paused, show play button and keep visible
              videoEl.addEventListener("pause", () => {
                setIcon(false);
                showOverlay();
              });

              // Check current playing state on initialization
              if (!videoEl.paused) {
                setIcon(true);
                hideOverlay(1200);
              } else {
                setIcon(false);
                showOverlay();
              }

              // Click to toggle play / pause
              if (clickOverlay) {
                clickOverlay.addEventListener("click", (e) => {
                  e.stopPropagation();
                  if (videoEl.paused) {
                    videoEl.muted = true;
                    videoEl.volume = 0;
                    videoEl.play().catch(() => {});
                    setIcon(true);
                    hideOverlay(1200);
                  } else {
                    videoEl.pause();
                    setIcon(false);
                    showOverlay();
                  }
                });

                // Mouse movement over video briefly shows controls, then hides after 1.5s
                clickOverlay.addEventListener("mousemove", () => {
                  if (!videoEl.paused) {
                    showOverlay();
                    hideOverlay(1500);
                  }
                });

                clickOverlay.addEventListener("mouseleave", () => {
                  if (!videoEl.paused) {
                    hideOverlay(300);
                  }
                });
              }
            } else if (clickOverlay) {
              clickOverlay.addEventListener("click", (e) => {
                e.stopPropagation();
                openLightbox(mediaItem.index);
              });
            }
          }
          if (mediaContainer) mediaContainer.style.cursor = "pointer";
        } else {
          if (videoContainer) {
            videoContainer.innerHTML = "";
            videoContainer.style.display = "none";
          }
          if (mainImgEl) {
            mainImgEl.style.display = "block";
            mainImgEl.src = mediaItem.url;
            mainImgEl.alt = p.name;
          }
          if (mediaContainer) mediaContainer.style.cursor = "zoom-in";
        }
      };

      const updateThumbArrows = () => {
        if (!thumbsWrapper || !thumbsHost) return;
        const scrollLeft = thumbsHost.scrollLeft;
        const maxScroll = thumbsHost.scrollWidth - thumbsHost.clientWidth;
        if (prevThumbArrow) {
          prevThumbArrow.style.display = scrollLeft > 5 ? "flex" : "none";
        }
        if (nextThumbArrow) {
          nextThumbArrow.style.display = scrollLeft < maxScroll - 5 ? "flex" : "none";
        }
      };

      const updateMediaArrows = () => {
        if (prevMediaBtn && nextMediaBtn) {
          const show = mediaList.length > 1 ? "flex" : "none";
          prevMediaBtn.style.display = show;
          nextMediaBtn.style.display = show;
        }
      };

      const renderThumbs = () => {
        if (!thumbsHost) return;
        if (mediaList.length > 1) {
          thumbsHost.innerHTML = mediaList.map((item, idx) => {
            const isActive = idx === currentMainIdx ? ' active' : '';
            if (item.type === 'video') {
              const videoThumbImg = defaultPrimaryImg || '';
              return `
                <div class="pd-thumb pd-thumb-video${isActive}" data-index="${idx}">
                  ${videoThumbImg ? `<img src="${videoThumbImg}" alt="${p.name} video thumbnail" style="opacity: 0.85;">` : `
                    <div class="pd-thumb-video-placeholder" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: var(--paper-bg);">
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="var(--teal-700)"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/></svg>
                    </div>
                  `}
                </div>
              `;
            } else {
              return `
                <div class="pd-thumb${isActive}" data-index="${idx}">
                  <img src="${item.url}" alt="${p.name} thumb ${idx + 1}">
                </div>
              `;
            }
          }).join("");
          if (thumbsWrapper) thumbsWrapper.style.display = "";
        } else {
          thumbsHost.innerHTML = "";
          if (thumbsWrapper) thumbsWrapper.style.display = "none";
        }
        updateThumbArrows();
      };

      updateMediaGallery = (variantImg = null) => {
        // If variant image is present, set it as main image; otherwise fallback to primary image
        const activeImg = (variantImg && String(variantImg).trim() !== '') ? String(variantImg).trim() : defaultPrimaryImg;

        mediaList.length = 0;

        // 1. Primary slot: active variant image or base primary image
        if (activeImg) {
          mediaList.push({ type: 'image', url: activeImg, isVariant: !!(variantImg && String(variantImg).trim() !== '') });
        }

        // 2. Video if present
        if (p.video) {
          mediaList.push({ type: 'video', url: p.video });
        }

        // 3. Other base gallery images (prevent duplicates)
        baseGalleryImgs.forEach((imgUrl) => {
          if (imgUrl && imgUrl !== activeImg && !mediaList.some(m => m.type === 'image' && m.url === imgUrl)) {
            mediaList.push({ type: 'image', url: imgUrl });
          }
        });

        // Assign clean 0-based indices
        mediaList.forEach((m, idx) => { m.index = idx; });

        // Update thumbnails UI
        renderThumbs();

        // Update main arrows
        updateMediaArrows();

        // Display index 0 (the active variant or primary image)
        if (mediaList.length > 0) {
          showMedia(mediaList[0]);
        }
      };

      // Navigate main image via next/prev arrows
      const navigateMainMedia = (direction) => {
        if (mediaList.length <= 1) return;
        currentMainIdx = (currentMainIdx + direction + mediaList.length) % mediaList.length;
        showMedia(mediaList[currentMainIdx]);
      };

      if (prevMediaBtn && nextMediaBtn) {
        prevMediaBtn.addEventListener("click", (e) => {
          e.stopPropagation();
          navigateMainMedia(-1);
        });
        nextMediaBtn.addEventListener("click", (e) => {
          e.stopPropagation();
          navigateMainMedia(1);
        });
      }

      // Mobile swipe gesture on main media
      if (mediaContainer) {
        let touchStartX = 0;
        let touchEndX = 0;

        mediaContainer.addEventListener("touchstart", (e) => {
          touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        mediaContainer.addEventListener("touchend", (e) => {
          touchEndX = e.changedTouches[0].screenX;
          if (mediaList.length <= 1) return;
          const diffX = touchEndX - touchStartX;
          if (diffX < -50) {
            navigateMainMedia(1);
          } else if (diffX > 50) {
            navigateMainMedia(-1);
          }
        }, { passive: true });
      }

      // Delegate thumbnail clicks
      if (thumbsHost) {
        thumbsHost.addEventListener("click", (e) => {
          const thumb = e.target.closest(".pd-thumb");
          if (!thumb) return;
          const idx = parseInt(thumb.dataset.index, 10);
          if (mediaList[idx]) {
            showMedia(mediaList[idx]);
          }
        });
      }

      // Thumbnail arrow controls
      if (prevThumbArrow && nextThumbArrow && thumbsHost) {
        prevThumbArrow.addEventListener("click", () => {
          thumbsHost.scrollBy({ left: -120, behavior: "smooth" });
        });
        nextThumbArrow.addEventListener("click", () => {
          thumbsHost.scrollBy({ left: 120, behavior: "smooth" });
        });
        thumbsHost.addEventListener("scroll", updateThumbArrows);
        window.addEventListener("resize", updateThumbArrows);
      }

      // Lightbox (Big Image Popup) Implementation
      let activeLightboxIdx = 0;

      const getLightbox = () => {
        let lightbox = document.querySelector(".pd-lightbox");
        if (!lightbox) {
          lightbox = document.createElement("div");
          lightbox.className = "pd-lightbox";
          lightbox.innerHTML = `
            <div class="pd-lightbox-content">
              <span class="pd-lightbox-close">&times;</span>
              <button class="pd-lightbox-nav pd-lightbox-nav--prev" aria-label="Previous image">&lt;</button>
              <button class="pd-lightbox-nav pd-lightbox-nav--next" aria-label="Next image">&gt;</button>
            </div>
          `;
          document.body.appendChild(lightbox);

          lightbox.querySelector(".pd-lightbox-close").addEventListener("click", () => {
            closeLightbox();
          });
          lightbox.addEventListener("click", (e) => {
            if (e.target === lightbox) closeLightbox();
          });

          lightbox.querySelector(".pd-lightbox-nav--prev").addEventListener("click", (e) => {
            e.stopPropagation();
            navigateLightbox(-1);
          });
          lightbox.querySelector(".pd-lightbox-nav--next").addEventListener("click", (e) => {
            e.stopPropagation();
            navigateLightbox(1);
          });

          document.addEventListener("keydown", (e) => {
            if (!lightbox.classList.contains("open")) return;
            if (e.key === "Escape") closeLightbox();
            if (e.key === "ArrowLeft") navigateLightbox(-1);
            if (e.key === "ArrowRight") navigateLightbox(1);
          });
        }
        return lightbox;
      };

      const closeLightbox = () => {
        const lb = document.querySelector(".pd-lightbox");
        if (!lb) return;
        const activeVideo = lb.querySelector("video");
        if (activeVideo) activeVideo.pause();
        const activeIframe = lb.querySelector("iframe");
        if (activeIframe) activeIframe.src = "";
        lb.classList.remove("open");
      };

      const navigateLightbox = (direction) => {
        if (!mediaList.length) return;
        activeLightboxIdx = (activeLightboxIdx + direction + mediaList.length) % mediaList.length;
        renderLightboxContent();
      };

      const renderLightboxContent = () => {
        const lb = getLightbox();
        const currentItem = mediaList[activeLightboxIdx];
        if (!currentItem) return;

        const contentEl = lb.querySelector(".pd-lightbox-content");
        const oldImg = contentEl.querySelector("img");
        const oldVid = contentEl.querySelector(".pd-lightbox-video-container");
        if (oldImg) oldImg.remove();
        if (oldVid) oldVid.remove();

        if (currentItem.type === 'video') {
          const videoDiv = document.createElement("div");
          videoDiv.className = "pd-lightbox-video-container";
          videoDiv.innerHTML = getVideoHtml(currentItem.url, true);
          contentEl.insertBefore(videoDiv, contentEl.querySelector(".pd-lightbox-nav--next"));

          const lbVideo = videoDiv.querySelector("video");
          if (lbVideo) {
            lbVideo.muted = true;
            lbVideo.defaultMuted = true;
            lbVideo.volume = 0;
            lbVideo.addEventListener("volumechange", () => {
              if (!lbVideo.muted || lbVideo.volume > 0) {
                lbVideo.muted = true;
                lbVideo.volume = 0;
              }
            });
            lbVideo.addEventListener("play", () => {
              lbVideo.muted = true;
              lbVideo.volume = 0;
            });
            lbVideo.play().catch(() => {});
          }
        } else {
          const newImg = document.createElement("img");
          newImg.src = currentItem.url;
          newImg.alt = "Zoomed Product";
          contentEl.insertBefore(newImg, contentEl.querySelector(".pd-lightbox-nav--next"));
        }

        const navs = lb.querySelectorAll(".pd-lightbox-nav");
        navs.forEach(n => n.style.display = mediaList.length > 1 ? "grid" : "none");
      };

      const openLightbox = (startIndex) => {
        if (!mediaList.length) return;
        activeLightboxIdx = (startIndex >= 0 && startIndex < mediaList.length) ? startIndex : 0;
        const lb = getLightbox();
        renderLightboxContent();
        lb.classList.add("open");
      };

      // Clicking main image always opens Lightbox on the exact active image
      if (mainImgEl) {
        mainImgEl.addEventListener("click", () => {
          openLightbox(currentMainIdx >= 0 ? currentMainIdx : 0);
        });
      }

      // Initialize media gallery with the initially selected variant image (or fallback to primary image)
      updateMediaGallery(initialSelectedVariantImg);



      /* ---- Related products (Series first, then Category) ---- */
      const rel = document.querySelector("[data-pd-related]");
      if (rel) {
        const sameSeries = XTRAL_PRODUCTS.filter(x => 
          x.id !== p.id && (
            (x.seriesId && p.seriesId && x.seriesId === p.seriesId) ||
            (x.series && p.series && x.series === p.series)
          )
        );
        const sameCategory = XTRAL_PRODUCTS.filter(x => 
          x.id !== p.id && x.cat === p.cat && !sameSeries.some(s => s.id === x.id)
        );
        const anyOthers = XTRAL_PRODUCTS.filter(x => 
          x.id !== p.id && !sameSeries.some(s => s.id === x.id) && !sameCategory.some(c => c.id === x.id)
        );

        const others = sameSeries
          .concat(sameCategory)
          .concat(anyOthers)
          .slice(0, 4);

        rel.innerHTML = others.length
          ? others.map(productCard).join("")
          : '';
      }

      /* ---- Share: name, model, colour, dimensions, price + link (reads the live
         DOM so the currently selected colour/size is what gets shared) ---- */
      const shareBtn = document.querySelector("[data-pd-share]");
      if (shareBtn) {
        const textOf = (sel) => {
          const el = document.querySelector(sel);
          if (!el || el.style.display === "none") return "";
          const clone = el.cloneNode(true);
          clone.querySelectorAll("small").forEach(s => s.remove());
          return clone.textContent.replace(/\s+/g, " ").trim();
        };
        const buildShareText = () => {
          const name = textOf("h1[data-pd-name]");
          const price = textOf("[data-pd-price]");
          const colour = document.querySelector(".color-swatch.active")?.title || "";
          const details = [
            `*${name}*`,
            textOf("[data-pd-code]"),
            colour ? `Colour: ${colour}` : "",
            textOf("[data-pd-dimensions]"),
            price.includes("₹") ? `Price: ${price} (M.R.P. incl. of all taxes)` : `Price: ${price}`,
          ].filter(Boolean);
          return { name, text: details.join("\n") + "\n\n" + location.href };
        };

        const shareMenu = document.querySelector("[data-pd-share-menu]");
        const shareWrap = document.querySelector("[data-pd-share-wrap]");
        const nativeItem = shareMenu.querySelector('[data-share-action="native"]');
        const copyLabel = shareMenu.querySelector("[data-share-copy-label]");
        if (navigator.share) nativeItem.hidden = false;

        const setMenu = (open) => {
          shareMenu.hidden = !open;
          shareBtn.setAttribute("aria-expanded", String(open));
        };
        shareBtn.onclick = () => setMenu(shareMenu.hidden);
        document.addEventListener("click", (e) => {
          if (!shareWrap.contains(e.target)) setMenu(false);
        });
        document.addEventListener("keydown", (e) => {
          if (e.key === "Escape") setMenu(false);
        });

        const copyText = async (str) => {
          try {
            await navigator.clipboard.writeText(str);
          } catch (e) {
            // Fallback for http:// pages where the Clipboard API is unavailable
            const ta = document.createElement("textarea");
            ta.value = str;
            ta.style.position = "fixed";
            ta.style.opacity = "0";
            document.body.appendChild(ta);
            ta.select();
            document.execCommand("copy");
            ta.remove();
          }
        };

        shareMenu.onclick = async (e) => {
          const item = e.target.closest("[data-share-action]");
          if (!item) return;
          const { name, text } = buildShareText();
          const action = item.dataset.shareAction;

          if (action === "whatsapp") {
            window.open("https://wa.me/?text=" + encodeURIComponent(text), "_blank", "noopener");
            setMenu(false);
          } else if (action === "copy") {
            await copyText(text);
            copyLabel.textContent = "Copied!";
            setTimeout(() => {
              copyLabel.textContent = "Copy details & link";
              setMenu(false);
            }, 1200);
          } else if (action === "native") {
            setMenu(false);
            const data = { title: name, text };
            // Attach the photo currently on screen so it's sent as an image with the details as caption
            const img = document.querySelector("[data-pd-img]");
            if (img && img.src && img.style.display !== "none" && navigator.canShare) {
              try {
                const blob = await (await fetch(img.src)).blob();
                const ext = (blob.type.split("/")[1] || "jpg").replace("jpeg", "jpg");
                const file = new File([blob], `${name.replace(/[^\w-]+/g, "-")}.${ext}`, { type: blob.type });
                if (navigator.canShare({ files: [file] })) data.files = [file];
              } catch (err) { /* image unavailable — share text + link only */ }
            }
            try {
              await navigator.share(data);
            } catch (err) { /* user cancelled */ }
          }
        };
      }


    }
  };
})();
