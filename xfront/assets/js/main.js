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
        location.href = q ? `products.php?search=${encodeURIComponent(q)}` : "products.php";
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
  const here = (location.pathname.split("/").pop() || "index.php");
  document.querySelectorAll(".nav-links a").forEach(a => {
    const href = a.getAttribute("href");
    if (href === here || (here === "" && href === "index.php")) a.classList.add("active");
    if ((href === "products.php") && (here === "products.php" || here === "pd.php")) a.classList.add("active");
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
  // Model codes like "MC 445" display as "MC-445" — spaces read as a typo/gap.
  const dashCode = (code) => code ? String(code).trim().replace(/\s+/g, '-').replace(/-+/g, '-') : code;
  function productCard(p) {
    const linkId = encodeURIComponent(dashCode(p.code && p.code !== '—' ? p.code : p.id));
    let sizeRange = "";
    if (p.variants && p.variants.type === "size" && p.variants.items.length > 0) {
      sizeRange = p.variants.items.map(v => v.name).join(", ");
    } else if (p.dimensions) {
      sizeRange = p.dimensions;
    }
    return `
      <a class="prod-card" href="pd.php?id=${linkId}">
        <div class="media">
          ${p.tag ? `<span class="tag">${p.tag}</span>` : ""}
          <img src="${p.img}" alt="${p.name}">
        </div>
        <div class="info">
          <span class="cat-lbl">${xtralCatName(p.cat)}</span>
          <h3>${p.name}</h3>
          <span class="code">Model ${dashCode(p.code)}</span>
          ${sizeRange ? `<span class="card-sizes" style="font-size:0.75rem; color:var(--muted); display:block; margin-top:2px;">Sizes: ${sizeRange}</span>` : ""}
          <div class="foot">
            ${p.price ? `<span class="price">${money(p.price)}</span>` : `<span class="price">On request</span>`}
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
            <video src="${b.video}" autoplay loop muted playsinline ${i === 0 ? "" : 'preload="metadata"'} style="width: 100%; height: 100%; object-fit: cover;"></video>
          ` : `
            <img src="${b.img}" alt="${b.title}" ${i === 0 ? "" : 'loading="lazy"'}>
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
        } else {
          const video = activeSlide.querySelector("video");
          if (video && video.videoWidth && video.videoHeight) {
            bannerHost.style.aspectRatio = `${video.videoWidth} / ${video.videoHeight}`;
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
      fetch(`${API_BASE}/catalogues.php`)
        .then(r => r.json())
        .then(j => {
          if (!j.success || !j.data.length) {
            pdfHost.innerHTML = `<p class="muted" style="text-align:center;grid-column:1/-1;">No catalogues available yet.</p>`;
            return;
          }
          pdfHost.innerHTML = j.data.map(c => {
            const catName = c.category || c.title.replace(' Catalogue', '');
            const coverStyle = c.thumb_url
              ? ` style="background-image:url('${c.thumb_url}');background-size:cover;background-position:center;"`
              : '';
            return `
            <div class="cat-mini-item">
              <div class="cat-mini-cover"${coverStyle}>
                ${c.thumb_url ? '' : `
                <div class="cat-mini-top">
                  <span>X-Tral · Look Book</span>
                  <h3>${catName}</h3>
                </div>
                <div class="cat-mini-bot">PDF · ${c.size_mb} MB</div>
                `}
              </div>
              <div class="cat-mini-actions">
                <a class="btn btn--gold btn--sm" href="${c.url}" download>Download <span class="ar">↓</span></a>
                <a class="btn btn--outline btn--sm" href="catalogue-view.php?pdf=${encodeURIComponent(c.url)}&title=${encodeURIComponent(c.title)}">View Online</a>
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
        `<a href="products.php?cat=${c.id}">${c.name}</a>`
      ).join("") + `<a href="products.php">View all</a>`;
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
            imageSrc = "assets/img/sanitary_ware_premium.png";
          } else if (nameLower.includes("fitting") || nameLower.includes("faucet")) {
            imageSrc = "assets/img/bath_fittings_premium.png";
          } else if (nameLower.includes("sink")) {
            imageSrc = "assets/img/kitchen_sinks_premium.png";
          } else if (nameLower.includes("wellness")) {
            imageSrc = "assets/img/wellness_premium.png";
          } else {
            imageSrc = "assets/img/hero_premium_bg.png";
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
          <a class="cat-card cat-card--photo" href="products.php?cat=${c.id}">
            <img src="${imageSrc}" alt="${c.name}">
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
          const imgUrl = s.img ? s.img : 'assets/img/hero_premium_bg.png';
          return `
            <a class="cat-card cat-card--photo" href="products.php?series=${s.id}">
              <img src="${imgUrl}" alt="${s.name}">
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
    const grid = document.querySelector("[data-product-grid]");
    if (grid) {
      const params = new URLSearchParams(location.search);
      let current = params.get("cat") || "all";
      // Unknown category in the URL → show everything
      if (current !== "all" && !XTRAL_CATEGORIES.some(c => c.id === current)) current = "all";

      const titleEl = document.querySelector("[data-cat-title]");
      const subEl = document.querySelector("[data-cat-sub]");
      const chipHost = document.querySelector("[data-filters]");
      const pagHost = document.querySelector("[data-pagination]");
      const toolbarEl = document.querySelector(".toolbar");

      const seriesObj = XTRAL_SERIES.find(s => s.id === params.get("series")) || null;
      const subObj = !seriesObj ? (XTRAL_SUBCATS.find(s => s.id === params.get("sub")) || null) : null;

      const tileCard = (href, img, name, hint) => `
        <a class="cat-card cat-card--photo" href="${href}">
          <img src="${img || 'assets/img/hero_premium_bg.png'}" alt="${name}">
          <div class="cat-body">
            <h3>${name}</h3>
            <span class="link">${hint} &rarr;</span>
          </div>
        </a>`;

      // ---- Drill-down levels (tile pages) ----
      const subTilesHost = document.querySelector("[data-sub-tiles-grid]");
      let tilesToShow = null;
      let tilesTitle = "";
      let tilesBlurb = "";

      if (subObj) {
        const sers = XTRAL_SERIES.filter(s => s.sub === subObj.id);
        if (sers.length) {
          tilesToShow = sers.map(s => tileCard(`products.php?series=${s.id}`, s.img, s.name, "Explore"));
          tilesTitle = subObj.name;
          tilesBlurb = subObj.blurb || "Select a series to explore.";
        }
      } else if (!seriesObj && current !== "all") {
        const subs = XTRAL_SUBCATS.filter(s => s.cat === current);
        const directSeries = XTRAL_SERIES.filter(s => s.cat === current && !s.sub);
        const cat = XTRAL_CATEGORIES.find(c => c.id === current);
        const catBlurb = (cat && cat.blurb) ? cat.blurb : "Choose a range to explore.";
        if (subs.length || directSeries.length) {
          tilesToShow = subs.map(s => tileCard(`products.php?cat=${current}&sub=${s.id}`, s.img, s.name, "Explore"))
            .concat(directSeries.map(s => tileCard(`products.php?series=${s.id}`, s.img, s.name, "Explore")));
          tilesTitle = xtralCatName(current);
          tilesBlurb = catBlurb;
        }
      }

      if (subTilesHost) {
        if (tilesToShow && tilesToShow.length) {
          subTilesHost.innerHTML = tilesToShow.join("");
          subTilesHost.style.display = "grid";
          if (titleEl) titleEl.textContent = tilesTitle;
          if (subEl) subEl.textContent = tilesBlurb;
        } else {
          subTilesHost.innerHTML = "";
          subTilesHost.style.display = "none";
        }
      }

      // ---- Product list mode (all products / category / sub / series) ----
      let baseList = null, baseTitle = null, baseBlurb = null;
      if (seriesObj) {
        baseList = XTRAL_PRODUCTS.filter(p => p.seriesId === seriesObj.id);
        baseTitle = seriesObj.name;
        baseBlurb = seriesObj.blurb || "All products in this series.";
      } else if (subObj) {
        baseList = XTRAL_PRODUCTS.filter(p => p.subId === subObj.id);
        baseTitle = subObj.name;
        baseBlurb = subObj.blurb || "All products in this range.";
      }
      // Chips only make sense on the flat all/category listing
      const showChips = baseList === null;

      let currentPage = 1;
      const itemsPerPage = 9;
      let searchVal = (params.get("search") || "").toLowerCase().trim();

      const searchInput = document.getElementById("catalog-search");
      if (searchInput) {
        if (searchVal) searchInput.value = params.get("search");
        searchInput.addEventListener("input", (e) => {
          searchVal = e.target.value.toLowerCase().trim();
          currentPage = 1; // reset page on search
          render();
        });
      }

      // Category Populating
      const categoryOptionsContainer = document.querySelector("#select-category .custom-options");
      const catTriggerLabel = document.querySelector("#select-category .custom-select-trigger [data-selected-label]");
      if (categoryOptionsContainer) {
        categoryOptionsContainer.innerHTML = '<div class="custom-option selected" data-value="all">All Categories</div>' +
          XTRAL_CATEGORIES.map(c => `<div class="custom-option" data-value="${c.id}">${c.name}</div>`).join("");
        if (current !== "all") {
          const matchingCat = XTRAL_CATEGORIES.find(c => c.id === current);
          if (catTriggerLabel && matchingCat) {
            catTriggerLabel.textContent = matchingCat.name;
          }
          // Highlight active option
          categoryOptionsContainer.querySelectorAll(".custom-option").forEach(opt => {
            if (opt.dataset.value === current) opt.classList.add("selected");
            else opt.classList.remove("selected");
          });
        }
      }

      // Collapsible Filters Panel Toggle
      const toggleFilterBtn = document.getElementById("toggle-filter-btn");
      const filterDrawer = document.getElementById("filter-drawer");
      if (toggleFilterBtn && filterDrawer) {
        toggleFilterBtn.addEventListener("click", () => {
          const isHidden = filterDrawer.style.display === "none";
          filterDrawer.style.display = isHidden ? "block" : "none";
        });
      }

      // Filter state variables
      let selectedCategory = current;
      let minPrice = null;
      let maxPrice = null;
      let currentSort = "default";

      // Toggle Custom Dropdown Open/Close
      document.querySelectorAll(".custom-select").forEach(select => {
        const trigger = select.querySelector(".custom-select-trigger");
        if (trigger) {
          trigger.addEventListener("click", (e) => {
            e.stopPropagation();
            document.querySelectorAll(".custom-select").forEach(s => {
              if (s !== select) s.classList.remove("open");
            });
            select.classList.toggle("open");
          });
        }
      });

      // Click outside to close custom select dropdowns
      document.addEventListener("click", () => {
        document.querySelectorAll(".custom-select").forEach(select => {
          select.classList.remove("open");
        });
      });

      // Bind instant reactive listeners for Category
      if (categoryOptionsContainer) {
        categoryOptionsContainer.addEventListener("click", (e) => {
          const opt = e.target.closest(".custom-option");
          if (!opt) return;

          selectedCategory = opt.dataset.value;

          // Update highlights
          categoryOptionsContainer.querySelectorAll(".custom-option").forEach(o => o.classList.remove("selected"));
          opt.classList.add("selected");

          // Update label text
          if (catTriggerLabel) catTriggerLabel.textContent = opt.textContent;

          // Close dropdown
          opt.closest(".custom-select").classList.remove("open");

          // Sync URL search params
          const url = new URL(location);
          if (selectedCategory !== "all") {
            url.searchParams.set("cat", selectedCategory);
          } else {
            url.searchParams.delete("cat");
          }

          if (selectedCategory !== current) {
            url.searchParams.delete("sub");
            url.searchParams.delete("series");
            current = selectedCategory;
            baseList = null;
          }
          history.replaceState(null, "", url);

          // Hide subcategories/series cards grid to show products directly
          const subTilesHost = document.querySelector("[data-sub-tiles-grid]");
          if (subTilesHost) {
            subTilesHost.innerHTML = "";
            subTilesHost.style.display = "none";
          }

          currentPage = 1;
          render();

          // Collapse the drawer
          if (filterDrawer) filterDrawer.style.display = "none";
        });
      }

      const minPriceInput = document.getElementById("filter-price-min");
      if (minPriceInput) {
        minPriceInput.addEventListener("input", (e) => {
          const val = e.target.value.trim();
          minPrice = val !== "" ? Number(val) : null;
          currentPage = 1;
          render();
        });
      }

      const maxPriceInput = document.getElementById("filter-price-max");
      if (maxPriceInput) {
        maxPriceInput.addEventListener("input", (e) => {
          const val = e.target.value.trim();
          maxPrice = val !== "" ? Number(val) : null;
          currentPage = 1;
          render();
        });
      }

      // Bind instant reactive listeners for Sort By
      const sortOptionsContainer = document.querySelector("#select-sort .custom-options");
      const sortTriggerLabel = document.querySelector("#select-sort .custom-select-trigger [data-selected-label]");
      if (sortOptionsContainer) {
        sortOptionsContainer.addEventListener("click", (e) => {
          const opt = e.target.closest(".custom-option");
          if (!opt) return;

          currentSort = opt.dataset.value;

          // Update highlights
          sortOptionsContainer.querySelectorAll(".custom-option").forEach(o => o.classList.remove("selected"));
          opt.classList.add("selected");

          // Update label text
          if (sortTriggerLabel) sortTriggerLabel.textContent = opt.textContent;

          // Close dropdown
          opt.closest(".custom-select").classList.remove("open");

          currentPage = 1;
          render();

          // Collapse the drawer
          if (filterDrawer) filterDrawer.style.display = "none";
        });
      }

      // Apply button (Done) - just closes the drawer
      const applyBtn = document.getElementById("filter-apply");
      if (applyBtn) {
        applyBtn.addEventListener("click", () => {
          if (filterDrawer) filterDrawer.style.display = "none";
        });
      }

      // Reset button
      const resetBtn = document.getElementById("filter-reset");
      if (resetBtn) {
        resetBtn.addEventListener("click", () => {
          // Reset visual fields for Category
          if (catTriggerLabel) catTriggerLabel.textContent = "All Categories";
          if (categoryOptionsContainer) {
            categoryOptionsContainer.querySelectorAll(".custom-option").forEach(o => {
              if (o.dataset.value === "all") o.classList.add("selected");
              else o.classList.remove("selected");
            });
          }

          // Reset visual fields for Sort
          if (sortTriggerLabel) sortTriggerLabel.textContent = "Default / Featured";
          if (sortOptionsContainer) {
            sortOptionsContainer.querySelectorAll(".custom-option").forEach(o => {
              if (o.dataset.value === "default") o.classList.add("selected");
              else o.classList.remove("selected");
            });
          }

          if (minPriceInput) minPriceInput.value = "";
          if (maxPriceInput) maxPriceInput.value = "";

          // Reset filter states
          selectedCategory = "all";
          minPrice = null;
          maxPrice = null;
          currentSort = "default";

          // Clear instant search
          if (searchInput) searchInput.value = "";
          searchVal = "";

          // Clear URL parameters
          const url = new URL(location);
          url.searchParams.delete("cat");
          url.searchParams.delete("sub");
          url.searchParams.delete("series");
          url.searchParams.delete("search");
          history.replaceState(null, "", url);

          // Reset category variables
          current = "all";
          baseList = null;

          // Update tiles
          updateSubTiles("all");

          currentPage = 1;
          render();

          // Collapse the drawer
          if (filterDrawer) filterDrawer.style.display = "none";
        });
      }

      // Dynamic sub-category/series tiles updates
      function updateSubTiles(catId) {
        const subTilesHost = document.querySelector("[data-sub-tiles-grid]");
        if (!subTilesHost) return;

        let tilesToShow = null;
        let tilesTitle = "";
        let tilesBlurb = "";

        if (catId !== "all") {
          const subs = XTRAL_SUBCATS.filter(s => s.cat === catId);
          const directSeries = XTRAL_SERIES.filter(s => s.cat === catId && !s.sub);
          const cat = XTRAL_CATEGORIES.find(c => c.id === catId);
          const catBlurb = (cat && cat.blurb) ? cat.blurb : "Choose a range to explore.";
          if (subs.length || directSeries.length) {
            tilesToShow = subs.map(s => tileCard(`products.php?cat=${catId}&sub=${s.id}`, s.img, s.name, "Explore"))
              .concat(directSeries.map(s => tileCard(`products.php?series=${s.id}`, s.img, s.name, "Explore")));
            tilesTitle = xtralCatName(catId);
            tilesBlurb = catBlurb;
          }
        }

        if (tilesToShow && tilesToShow.length) {
          subTilesHost.innerHTML = tilesToShow.join("");
          subTilesHost.style.display = "grid";
          if (titleEl) titleEl.textContent = tilesTitle;
          if (subEl) subEl.textContent = tilesBlurb;
        } else {
          subTilesHost.innerHTML = "";
          subTilesHost.style.display = "none";
        }
      }

      function render() {
        const hasTiles = subTilesHost && subTilesHost.innerHTML.trim() !== "";
        if (toolbarEl) toolbarEl.style.display = hasTiles ? "none" : "flex";
        if (pagHost) pagHost.style.display = hasTiles ? "none" : "flex";
        if (grid) grid.style.display = hasTiles ? "none" : "grid";

        let list = XTRAL_PRODUCTS.slice();
        if (baseList !== null && selectedCategory === current) {
          list = baseList.slice();
        } else if (selectedCategory !== "all") {
          list = XTRAL_PRODUCTS.filter(p => p.cat === selectedCategory);
        }

        if (searchVal) {
          list = list.filter(p => {
            const name = p.name ? p.name.toLowerCase() : "";
            const code = p.code ? p.code.toLowerCase() : "";
            const catName = p.cat ? xtralCatName(p.cat).toLowerCase() : "";
            const series = p.series ? p.series.toLowerCase() : "";

            return name.includes(searchVal) ||
              code.includes(searchVal) ||
              catName.includes(searchVal) ||
              series.includes(searchVal);
          });
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

        if (baseList !== null && selectedCategory === current) {
          if (titleEl) titleEl.textContent = baseTitle;
          if (subEl) subEl.textContent = baseBlurb;
        } else {
          if (selectedCategory !== "all") {
            if (titleEl) titleEl.textContent = xtralCatName(selectedCategory);
            if (subEl) {
              const c = XTRAL_CATEGORIES.find(c => c.id === selectedCategory);
              subEl.textContent = (c && c.blurb) ? c.blurb : "Browse the complete range.";
            }
          } else {
            if (titleEl) titleEl.textContent = "All Products";
            if (subEl) subEl.textContent = "Browse the complete X-Tral range across every category.";
          }
        }

        const totalPages = Math.ceil(list.length / itemsPerPage);
        if (currentPage > totalPages) currentPage = totalPages || 1;

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

            // Prev button
            html += `<button class="pagination-btn${currentPage === 1 ? ' disabled' : ''}" data-page="${currentPage - 1}">&lt;</button>`;

            // Pages range
            const range = [];
            const delta = 1; // number of pages to show around current page
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

            // Next button
            html += `<button class="pagination-btn${currentPage === totalPages ? ' disabled' : ''}" data-page="${currentPage + 1}">&gt;</button>`;

            pagHost.innerHTML = html;
          }
        }
      }

      if (pagHost) {
        pagHost.addEventListener("click", (e) => {
          const btn = e.target.closest(".pagination-btn");
          if (!btn || btn.classList.contains("disabled")) return;
          currentPage = parseInt(btn.dataset.page, 10);
          render();

          // Smooth scroll to top of product grid
          const scrollTarget = document.querySelector(".toolbar") || grid;
          if (scrollTarget) {
            scrollTarget.scrollIntoView({ behavior: "smooth", block: "start" });
          }
        });
      }

      render();
    }

    /* ---- Product detail page ---- */
    const pdHost = document.querySelector("[data-product-detail]");
    if (pdHost && XTRAL_PRODUCTS.length) {
      // URLs use the dash form of the code (e.g. "TDS-60"); normalize both
      // sides the same way so old space-form links (bookmarked/QR-printed
      // before this change) still resolve correctly.
      const id = dashCode((new URLSearchParams(location.search).get("id") || "").trim()).toLowerCase();
      const p = XTRAL_PRODUCTS.find(x => {
        const prodId = String(x.id).trim().toLowerCase();
        const prodCode = x.code && x.code !== '—' ? dashCode(String(x.code).trim()).toLowerCase() : "";
        if (prodId === id || (prodCode && prodCode === id)) return true;
        if (x.variants && x.variants.items && x.variants.items.length > 0) {
          return x.variants.items.some(v => v.code && dashCode(String(v.code).trim()).toLowerCase() === id);
        }
        return false;
      }) || XTRAL_PRODUCTS[0];

      document.title = p.name + " — X-Tral";
      // Fill EVERY matching element (name appears in both breadcrumb and title)
      const set = (sel, val) => { document.querySelectorAll(sel).forEach(el => { el.innerHTML = val; }); };
      // Rewrite the address bar to the dash form so old %20-encoded links
      // (bookmarked/QR-printed before this change) self-correct on load,
      // not just when a variant is switched.
      const syncUrlId = (rawId) => {
        if (!rawId) return;
        const url = new URL(location);
        url.searchParams.set("id", dashCode(String(rawId).trim()));
        history.replaceState(null, "", url);
      };

      set("[data-pd-cat]", xtralCatName(p.cat));
      set("[data-pd-name]", p.name);
      set("[data-pd-code]", "Model " + dashCode(p.code));
      if (!p.variants || !p.variants.items || p.variants.items.length === 0) {
        syncUrlId(p.code && p.code !== '—' ? p.code : p.id);
      }
      set("[data-pd-price]", p.price ? `${money(p.price)} <small>M.R.P. (incl. of all taxes)</small>` : "On request");

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
        const len = p.specs.find(s => s[0] && s[0].toLowerCase().includes("length"))?.[1];
        const wid = p.specs.find(s => s[0] && s[0].toLowerCase().includes("width"))?.[1];
        const hei = p.specs.find(s => s[0] && s[0].toLowerCase().includes("height"))?.[1];
        if (len && wid && hei) {
          productDimensions = `${len} x ${wid} x ${hei}`;
        } else if (len && wid) {
          productDimensions = `${len} x ${wid}`;
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
          const toggleDesc = () => {
            const expanded = descEl.classList.toggle("expanded");
            if (descToggle) descToggle.textContent = expanded ? "Read less" : "Read more";
          };
          if (descToggle) descToggle.addEventListener("click", toggleDesc);
          // Clicking the clamped text (the "…") also opens the full description
          descEl.addEventListener("click", () => {
            if (!descEl.classList.contains("expanded")) toggleDesc();
          });
        } else {
          descBlock.hidden = true;
        }
      }
      set("[data-pd-dimensions-val]", p.dimensions || "—");
      set("[data-pd-hsn-val]", p.hsn || "—");
      const img = document.querySelector("[data-pd-img]");
      if (img) { img.src = p.img; img.alt = p.name; img.style.visibility = "visible"; }

      /* ---- Product variants ---- */
      const varHost = document.querySelector("[data-pd-variants]");
      if (varHost) {
        if (p.variants && p.variants.items && p.variants.items.length > 0) {
          const v = p.variants;
          let initialIdx = 0;
          if (id) {
            const matchIdx = v.items.findIndex(item => item.code && dashCode(String(item.code).trim()).toLowerCase() === id);
            if (matchIdx >= 0) initialIdx = matchIdx;
          }

          let optionsHTML = "";

          if (v.type === "color") {
            optionsHTML = v.items.map((item, idx) => `
              <button class="color-swatch${idx === initialIdx ? ' active' : ''}"
                      title="${item.name}"
                      data-index="${idx}"
                      style="background-color: ${item.val};"
                      aria-label="${item.name}">
              </button>
            `).join("");
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

          // Initialize with matching variant
          const applyVariant = (item) => {
            set("[data-pd-code]", "Model " + dashCode(item.code));
            set("[data-pd-price]", item.price ? `${money(item.price)} <small>M.R.P. (incl. of all taxes)</small>` : "On request");
            if (item.img && img) { img.src = item.img; img.style.visibility = "visible"; }
            
            // If it's a size variant, update dimensions display with variant's size
            if (v.type === "size" && item.name) {
              updateDimensions(item.name);
            }
          };
          applyVariant(v.items[initialIdx]);
          syncUrlId(v.items[initialIdx].code);

          // Handle variant selection click
          varHost.addEventListener("click", (e) => {
            const btn = e.target.closest(".color-swatch, .size-pill");
            if (!btn) return;
            varHost.querySelectorAll(".color-swatch, .size-pill").forEach(el => el.classList.remove("active"));
            btn.classList.add("active");
            const idx = parseInt(btn.dataset.index, 10);
            if (v.items[idx]) {
              applyVariant(v.items[idx]);
              syncUrlId(v.items[idx].code);
            }
          });
        } else {
          varHost.innerHTML = ""; // Clear if no variants
        }
      }

      const crumbCat = document.querySelector("[data-pd-crumb]");
      if (crumbCat) { crumbCat.textContent = xtralCatName(p.cat); crumbCat.href = "products.php?cat=" + p.cat; }

      set("[data-pd-specs]", p.specs.map(s => `<tr><th>${s[0]}</th><td>${s[1]}</td></tr>`).join(""));

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

      /* ---- Thumbnails & Lightbox Popup (like Amazon) ---- */
      const thumbsHost = document.querySelector("[data-pd-thumbs]");
      const mainImgEl = document.querySelector("[data-pd-img]");
      const videoContainer = document.querySelector("[data-pd-video-container]");
      const mediaContainer = document.querySelector("[data-pd-media-container]");

      const mediaList = [];
      const productImages = p.imgs && p.imgs.length > 0 ? p.imgs : [p.img];

      // 1. Push primary image (first image)
      if (productImages.length > 0 && productImages[0]) {
        mediaList.push({ type: 'image', url: productImages[0], index: mediaList.length });
      }

      // 2. Push video (if present)
      if (p.video) {
        mediaList.push({ type: 'video', url: p.video, index: mediaList.length });
      }

      // 3. Push remaining gallery images
      for (let i = 1; i < productImages.length; i++) {
        if (productImages[i]) {
          mediaList.push({ type: 'image', url: productImages[i], index: mediaList.length });
        }
      }

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
          const embedUrl = isLightbox
            ? ytUrl.replace('&mute=1', '').replace('?autoplay=1', '?autoplay=1')
            : ytUrl;
          return `<iframe src="${embedUrl}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="width:100%; height:100%; display:block;"></iframe>`;
        }

        const vimeoUrl = getVimeoEmbedUrl(url);
        if (vimeoUrl) {
          const embedUrl = isLightbox
            ? vimeoUrl.replace('&muted=1', '')
            : vimeoUrl;
          return `<iframe src="${embedUrl}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="width:100%; height:100%; display:block;"></iframe>`;
        }

        if (isLightbox) {
          return `<video src="${url}" controls autoplay class="rounded bg-black"></video>`;
        } else {
          return `<video src="${url}" autoplay muted loop playsinline></video>`;
        }
      };

      // Helper to show main media (image or video preview)
      let currentMainIdx = 0;
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
                    <svg width="24" height="24" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                  </div>
                ` : ''}
                <div class="pd-media-video-click-overlay" style="position: absolute; top:0; left:0; width:100%; height:100%; cursor:pointer; background:transparent; z-index:2;"></div>
              </div>
            `;

            videoContainer.querySelector(".pd-media-video-click-overlay").addEventListener("click", (e) => {
              e.stopPropagation();
              openLightbox(mediaItem.index);
            });
          }
          if (mediaContainer) mediaContainer.style.cursor = "pointer";
        } else {
          if (videoContainer) videoContainer.style.display = "none";
          if (mainImgEl) {
            mainImgEl.style.display = "block";
            mainImgEl.src = mediaItem.url;
          }
          if (mediaContainer) mediaContainer.style.cursor = "zoom-in";
        }
      };

      // 1. Render thumbnails if there are multiple items
      if (thumbsHost) {
        if (mediaList.length > 1) {
          const initialIdx = mediaList.findIndex(item => item.type === 'video') !== -1
            ? mediaList.findIndex(item => item.type === 'video')
            : 0;

          thumbsHost.innerHTML = mediaList.map((item, idx) => {
            const isActive = idx === initialIdx ? ' active' : '';
            if (item.type === 'video') {
              const videoThumbImg = p.img || (productImages && productImages[0]) || '';
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

          thumbsHost.addEventListener("click", (e) => {
            const thumb = e.target.closest(".pd-thumb");
            if (!thumb) return;
            const idx = parseInt(thumb.dataset.index, 10);
            showMedia(mediaList[idx]);
          });
        } else {
          thumbsHost.innerHTML = ""; // Hide/clear if single item
        }
      }

      // Initialize with video first if present, otherwise first item
      if (mediaList.length > 0) {
        const videoItem = mediaList.find(item => item.type === 'video');
        if (videoItem) {
          showMedia(videoItem);
        } else {
          showMedia(mediaList[0]);
        }
      }

      // Main product media next/previous arrow navigation & swipe gestures
      const prevMediaBtn = mediaContainer ? mediaContainer.querySelector(".pd-media-arrow--prev") : null;
      const nextMediaBtn = mediaContainer ? mediaContainer.querySelector(".pd-media-arrow--next") : null;

      const navigateMainMedia = (direction) => {
        if (mediaList.length <= 1) return;
        currentMainIdx = (currentMainIdx + direction + mediaList.length) % mediaList.length;
        showMedia(mediaList[currentMainIdx]);
      };

      if (prevMediaBtn && nextMediaBtn) {
        if (mediaList.length <= 1) {
          prevMediaBtn.style.display = "none";
          nextMediaBtn.style.display = "none";
        } else {
          prevMediaBtn.style.display = "flex";
          nextMediaBtn.style.display = "flex";

          prevMediaBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            navigateMainMedia(-1);
          });
          nextMediaBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            navigateMainMedia(1);
          });
        }
      }

      // Mobile touch gestures for swiping images
      if (mediaContainer) {
        let touchStartX = 0;
        let touchEndX = 0;

        mediaContainer.addEventListener("touchstart", (e) => {
          touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        mediaContainer.addEventListener("touchend", (e) => {
          touchEndX = e.changedTouches[0].screenX;
          handleSwipe();
        }, { passive: true });

        const handleSwipe = () => {
          if (mediaList.length <= 1) return;
          const swipeThreshold = 50; // swipe offset minimum
          const diffX = touchEndX - touchStartX;

          if (diffX < -swipeThreshold) {
            navigateMainMedia(1); // Swiped Left -> Next media
          } else if (diffX > swipeThreshold) {
            navigateMainMedia(-1); // Swiped Right -> Prev media
          }
        };
      }

      // 2. Lightbox modal popup implementation
      const openLightbox = (startIndex) => {
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

          // Close actions
          lightbox.querySelector(".pd-lightbox-close").addEventListener("click", () => {
            closeLightbox();
          });
          lightbox.addEventListener("click", (e) => {
            if (e.target === lightbox) closeLightbox();
          });

          // Nav actions
          lightbox.querySelector(".pd-lightbox-nav--prev").addEventListener("click", (e) => {
            e.stopPropagation();
            navigateLightbox(-1);
          });
          lightbox.querySelector(".pd-lightbox-nav--next").addEventListener("click", (e) => {
            e.stopPropagation();
            navigateLightbox(1);
          });
        }

        let activeIdx = startIndex;

        const closeLightbox = () => {
          const activeVideo = lightbox.querySelector("video");
          if (activeVideo) activeVideo.pause();
          const activeIframe = lightbox.querySelector("iframe");
          if (activeIframe) activeIframe.src = ""; // Kill audio and playback in iframe
          lightbox.classList.remove("open");
        };

        const navigateLightbox = (direction) => {
          activeIdx = (activeIdx + direction + mediaList.length) % mediaList.length;
          renderLightboxContent();
        };

        const updateNavVisibility = () => {
          const navs = lightbox.querySelectorAll(".pd-lightbox-nav");
          navs.forEach(n => n.style.display = mediaList.length > 1 ? "grid" : "none");
        };

        const renderLightboxContent = () => {
          const currentItem = mediaList[activeIdx];
          const contentEl = lightbox.querySelector(".pd-lightbox-content");

          // Remove previous elements
          const oldImg = contentEl.querySelector("img");
          const oldVid = contentEl.querySelector(".pd-lightbox-video-container");
          if (oldImg) oldImg.remove();
          if (oldVid) oldVid.remove();

          if (currentItem.type === 'video') {
            const videoDiv = document.createElement("div");
            videoDiv.className = "pd-lightbox-video-container";
            videoDiv.innerHTML = getVideoHtml(currentItem.url, true);
            contentEl.insertBefore(videoDiv, contentEl.querySelector(".pd-lightbox-nav--next"));
          } else {
            const newImg = document.createElement("img");
            newImg.src = currentItem.url;
            newImg.alt = "Zoomed Product";
            contentEl.insertBefore(newImg, contentEl.querySelector(".pd-lightbox-nav--next"));
          }
          updateNavVisibility();
        };

        renderLightboxContent();
        lightbox.classList.add("open");
      };

      if (mainImgEl) {
        mainImgEl.addEventListener("click", () => {
          const currentUrl = mainImgEl.src;
          const activeIdx = mediaList.findIndex(item => item.type === 'image' && item.url === currentUrl);
          openLightbox(activeIdx >= 0 ? activeIdx : 0);
        });
      }

      // Thumbnail scrolling with arrows
      const thumbsWrapper = document.querySelector(".pd-thumbs-wrapper");
      if (thumbsWrapper) {
        const prevArrow = thumbsWrapper.querySelector(".pd-thumbs-arrow--prev");
        const nextArrow = thumbsWrapper.querySelector(".pd-thumbs-arrow--next");
        const thumbsContainer = thumbsWrapper.querySelector("[data-pd-thumbs]");

        const updateArrowVisibility = () => {
          if (!thumbsContainer) return;
          const scrollLeft = thumbsContainer.scrollLeft;
          const maxScroll = thumbsContainer.scrollWidth - thumbsContainer.clientWidth;

          if (prevArrow) {
            prevArrow.style.display = scrollLeft > 5 ? "flex" : "none";
          }
          if (nextArrow) {
            nextArrow.style.display = scrollLeft < maxScroll - 5 ? "flex" : "none";
          }
        };

        if (prevArrow && nextArrow && thumbsContainer) {
          prevArrow.addEventListener("click", () => {
            thumbsContainer.scrollBy({ left: -120, behavior: "smooth" });
          });
          nextArrow.addEventListener("click", () => {
            thumbsContainer.scrollBy({ left: 120, behavior: "smooth" });
          });
          thumbsContainer.addEventListener("scroll", updateArrowVisibility);

          // Trigger initially and on resize
          setTimeout(updateArrowVisibility, 300);
          window.addEventListener("resize", updateArrowVisibility);

          // Monitor child elements (like dynamically loaded thumbnails)
          const observer = new MutationObserver(updateArrowVisibility);
          observer.observe(thumbsContainer, { childList: true });
        }
      }



      /* ---- Related products (Series first, then Category) ---- */
      const rel = document.querySelector("[data-pd-related]");
      if (rel) {
        const sameSeries = XTRAL_PRODUCTS.filter(x => x.cat === p.cat && x.series === p.series && x.id !== p.id);
        const sameCategory = XTRAL_PRODUCTS.filter(x => x.cat === p.cat && x.series !== p.series && x.id !== p.id);

        const others = sameSeries
          .concat(sameCategory)
          .slice(0, 4);

        rel.innerHTML = others.map(productCard).join("");
      }


    }
  };
})();
