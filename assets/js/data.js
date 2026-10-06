/* =====================================================================
   X-TRAL — Live catalogue data
   Loads categories & products from the xadmin Web API and fills the
   same XTRAL_CATEGORIES / XTRAL_PRODUCTS globals the site renders from.
   The API address comes from config.js (edit that file for deploy).
   ===================================================================== */

let XTRAL_CATEGORIES = [];
let XTRAL_SUBCATS = [];
let XTRAL_SERIES = [];
let XTRAL_PRODUCTS = [];
let XTRAL_BANNERS = [];

/* Helpers used by main.js */
function xtralCount(catId) { return XTRAL_PRODUCTS.filter(p => p.cat === catId).length; }
function xtralCatName(catId) { const c = XTRAL_CATEGORIES.find(c => c.id === catId); return c ? c.name : catId; }

/* ---- API fetch helper ---- */
async function xtralApiGet(endpoint) {
  const res = await fetch(`${API_BASE}/${endpoint}`);
  if (!res.ok) throw new Error(`API error ${res.status} on ${endpoint}`);
  const json = await res.json();
  if (!json.success) throw new Error(json.message || `API request failed: ${endpoint}`);
  return json.data;
}

/* ---- Convert specification HTML from admin into text lines ---- */
function xtralSpecLines(html) {
  if (!html) return [];
  const text = html
    .replace(/<br\s*\/?>/gi, '\n')
    .replace(/<\/(p|div|li|h[1-6])>/gi, '\n')
    .replace(/<[^>]+>/g, '')
    .replace(/&nbsp;/g, ' ')
    .replace(/&amp;/g, '&');
  return text.split('\n').map(s => s.trim()).filter(Boolean);
}

/* ---- Map an API product to the shape the site renders ---- */
function xtralMapProduct(p) {
  const lines = xtralSpecLines(p.specifications);

  // Lines like "Material : Acrylic" become spec table rows; the rest are features
  const specs = [];
  const extras = [];
  lines.forEach(line => {
    const trimmed = line.trim();
    const lower = trimmed.toLowerCase();
    
    // Skip section headers in specifications content to prevent parsing them as features
    if (lower === "technical specifications" || 
        lower === "dimensions & details" || 
        lower === "description" || 
        lower === "overview" || 
        lower.includes("specification") || 
        lower.includes("dimension")) {
      return;
    }

    const i = trimmed.indexOf(':');
    if (i > 0 && i < 40) {
      specs.push([trimmed.slice(0, i).trim(), trimmed.slice(i + 1).trim()]);
    } else {
      extras.push(trimmed);
    }
  });
  if (p.dimensions) specs.unshift(['Dimensions', p.dimensions]);
  if (p.series) specs.push(['Series', p.series]);

  // Variants from admin → colour swatches / size pills
  let variants = null;
  if (p.variants && p.variants.length > 0 && p.variant_type !== 'none') {
    const isColor = p.variant_type === 'color';
    variants = {
      type: p.variant_type,
      label: isColor
        ? (p.colour_label === 'finish' ? 'Finish' : 'Colour')
        : 'Size',
      items: p.variants.map(v => ({
        name: v.name,
        code: v.code,
        price: v.price,
        price_zone2: v.price_zone2 || null,
        price_label_1: v.price_label_1 || p.price_label_1 || null,
        price_label_2: v.price_label_2 || p.price_label_2 || null,
        val: v.color_hex || '#d7dde0',
        textureImg: v.texture_image || null,
        img: v.image || null,
        sizes: Array.isArray(v.sizes) ? v.sizes : []
      }))
    };
  }

  // If the product itself has no price but its variants do, show the
  // first variant's price instead of "On request" — the base product
  // record is often left blank when pricing only varies by variant.
  const firstVariantPrice = variants && variants.items.length
    ? variants.items.find(v => v.price)?.price
    : null;
  const firstVariantPrice2 = variants && variants.items.length
    ? variants.items.find(v => v.price_zone2)?.price_zone2
    : null;

  return {
    id: String(p.id),
    cat: String(p.category_id),
    subId: p.sub_category_id != null ? String(p.sub_category_id) : null,
    seriesId: p.series_id != null ? String(p.series_id) : null,
    series: p.series || '',
    name: p.name,
    code: p.code || '—',
    price: p.price || firstVariantPrice || null,
    price_zone2: p.price_zone2 || firstVariantPrice2 || null,
    price_label_1: p.price_label_1 || null,
    price_label_2: p.price_label_2 || null,
    tag: p.is_new_arrival ? 'New' : null,
    img: p.image || '',
    imgs: p.images || (p.image ? [p.image] : []),
    short: extras[0] || lines[0] || '',
    // Raw admin-entered HTML (Quill) — keeps bold/italic/lists for the
    // product-detail description; the plain-text `short` stays for cards.
    descHtml: p.specifications || '',
    variants: variants,
    specs: specs,
    features: (p.features && p.features.length > 0)
      ? p.features.map(f => [f.icon, f.name, f.name])
      : [],
    hsn: p.hsn_code || '—',
    video: p.video || null,
    dimensions: p.dimensions || '',
    displayOrder: p.display_order != null ? Number(p.display_order) : 0
  };
}

/* ---- Company branding: swap the text brand for the uploaded logo ----
   Logo is uploaded in Admin → Configuration → Company Info and served
   by webapi/site.php. Header uses `.brand img`; footer gets the extra
   `.footer-logo` class so the CSS inverts it to white. */
function xtralApplyBranding(site) {
  document.querySelectorAll('.site-header .brand, footer .brand').forEach(brand => {
    if (site && site.logo) {
      // Admin has uploaded a logo — replace the brand with it
      const img = document.createElement('img');
      img.src = site.logo;
      img.alt = (site && site.name) || 'X-Tral';
      if (brand.closest('footer')) img.classList.add('footer-logo');
      brand.replaceChildren(img);
    } else {
      // No logo configured — reveal the CSS-drawn fallback brand
      brand.classList.add('brand--fallback');
    }
  });
}

/* ---- Company contact info: fill [data-site-*] placeholders ----
   Used in the footer "Get in touch" column and the Contact page.
   Values come from Admin → Configuration → Company Info. */
function xtralApplySiteInfo(site) {
  if (!site) return;
  if (site.email) {
    document.querySelectorAll('[data-site-email]').forEach(el => {
      el.textContent = site.email;
      if (el.tagName === 'A') el.href = 'mailto:' + site.email;
    });
  }
  if (site.phone) {
    document.querySelectorAll('[data-site-phone]').forEach(el => {
      el.textContent = site.phone;
      if (el.tagName === 'A') el.href = 'tel:' + site.phone.replace(/[^+\d]/g, '');
    });
  }
  if (site.address) {
    // Address is trusted HTML from the admin's rich-text editor
    const addressHost = document.createElement('div');
    addressHost.innerHTML = site.address;
    const addressHtml = /india/i.test(addressHost.textContent || '')
      ? site.address
      : `${site.address}<p>India.</p>`;

    document.querySelectorAll('[data-site-address]').forEach(el => {
      el.innerHTML = addressHtml;
    });
  }

  // Handle social media links in the footer
  const socialsHost = document.querySelector('[data-site-socials]');
  if (socialsHost) {
    socialsHost.innerHTML = '';
    const socials = [
      { key: 'social_facebook', icon: 'fa-facebook-f', label: 'Facebook' },
      { key: 'social_instagram', icon: 'fa-instagram', label: 'Instagram' },
      { key: 'social_youtube', icon: 'fa-youtube', label: 'YouTube' },
      { key: 'social_twitter', icon: 'fa-x-twitter', label: 'X (Twitter)' }
    ];
    socials.forEach(s => {
      const url = site[s.key];
      if (url && url.trim() !== '') {
        const a = document.createElement('a');
        a.href = url.trim();
        a.target = '_blank';
        a.rel = 'noopener noreferrer';
        a.setAttribute('aria-label', s.label);
        a.innerHTML = `<i class="fa-brands ${s.icon}"></i>`;
        socialsHost.appendChild(a);
      }
    });
  }
}

const XTRAL_CACHE_TTL = 5 * 60 * 1000; // 5 minutes cache

function xtralGetStorage(key) {
  try {
    const raw = sessionStorage.getItem(key);
    if (!raw) return null;
    return JSON.parse(raw);
  } catch (e) {
    return null;
  }
}

function xtralSetStorage(key, val) {
  try {
    sessionStorage.setItem(key, JSON.stringify({ time: Date.now(), data: val }));
  } catch (e) {
    // Quota exceeded or disabled, silently ignore
  }
}

// Clear any stale catalogue cache that is missing required price_label fields
try {
  const c = sessionStorage.getItem('xtral_catalogue');
  if (c && !c.includes('price_label_1')) {
    sessionStorage.removeItem('xtral_catalogue');
  }
} catch (e) {}

(async function xtralLoadSite() {
  if (window.XTRAL_PRICE_LABEL_1) {
    if (!window.XTRAL_SITE) window.XTRAL_SITE = {};
    window.XTRAL_SITE.price_label_1 = window.XTRAL_PRICE_LABEL_1;
    window.XTRAL_SITE.price_label_2 = window.XTRAL_PRICE_LABEL_2;
  }

  const cached = xtralGetStorage('xtral_site');
  if (cached && cached.data) {
    window.XTRAL_SITE = Object.assign({}, cached.data, window.XTRAL_SITE || {});
    if (window.XTRAL_PRICE_LABEL_1) window.XTRAL_SITE.price_label_1 = window.XTRAL_PRICE_LABEL_1;
    if (window.XTRAL_PRICE_LABEL_2) window.XTRAL_SITE.price_label_2 = window.XTRAL_PRICE_LABEL_2;
    xtralApplyBranding(window.XTRAL_SITE);
    xtralApplySiteInfo(window.XTRAL_SITE);
  }

  try {
    const site = await xtralApiGet('site.php');
    if (site) {
      if (site.price_label_1 && ['fabio', 'fabio:', 'ped', 'ped:'].includes(String(site.price_label_1).toLowerCase().trim())) {
        site.price_label_1 = 'Zone 1';
      }
      if (site.price_label_2 && ['fabio', 'fabio:', 'ped', 'ped:'].includes(String(site.price_label_2).toLowerCase().trim())) {
        site.price_label_2 = 'Zone 2';
      }
      window.XTRAL_SITE = Object.assign({}, window.XTRAL_SITE || {}, site);
      if (window.XTRAL_PRICE_LABEL_1) window.XTRAL_SITE.price_label_1 = window.XTRAL_PRICE_LABEL_1;
      if (window.XTRAL_PRICE_LABEL_2) window.XTRAL_SITE.price_label_2 = window.XTRAL_PRICE_LABEL_2;
      xtralSetStorage('xtral_site', window.XTRAL_SITE);
      xtralApplyBranding(window.XTRAL_SITE);
      xtralApplySiteInfo(window.XTRAL_SITE);
    }
  } catch (err) {
    if (!window.XTRAL_SITE) {
      xtralApplyBranding(null);
    }
  }
})();

function xtralPopulateData(payload) {
  if (payload.banners) {
    XTRAL_BANNERS = payload.banners.map(b => ({
      id: b.id,
      title: b.title || '',
      img: b.image,
      video: b.video || null,
      type: b.banner_type || 'image',
      link: b.link || null
    }));
  }

  if (payload.cats) {
    XTRAL_CATEGORIES = payload.cats.map(c => ({
      id: String(c.id),
      name: c.name,
      img: c.image || '',
      blurb: c.description || ''
    }));
  }

  if (payload.subs) {
    XTRAL_SUBCATS = payload.subs.map(s => ({
      id: String(s.id),
      cat: String(s.category_id),
      name: s.name,
      img: s.image || '',
      blurb: s.description || ''
    }));
  }

  if (payload.series) {
    XTRAL_SERIES = payload.series.map(s => ({
      id: String(s.id),
      cat: String(s.category_id),
      sub: s.sub_category_id != null ? String(s.sub_category_id) : null,
      name: s.name,
      img: s.image || '',
      blurb: s.description || '',
      is_new_arrival: !!s.is_new_arrival
    }));
  }

  if (payload.prods) {
    XTRAL_PRODUCTS = payload.prods.map(xtralMapProduct);
    if (window.XTRAL_CURRENT_PROD) {
      const curId = String(window.XTRAL_CURRENT_PROD.id || '');
      const curCode = String(window.XTRAL_CURRENT_PROD.code || '').replace(/[^a-zA-Z0-9]/g, '').toLowerCase();
      const match = XTRAL_PRODUCTS.find(x => 
        (curId && String(x.id) === curId) || 
        (curCode && x.code && String(x.code).replace(/[^a-zA-Z0-9]/g, '').toLowerCase() === curCode)
      );
      if (match) {
        if (window.XTRAL_CURRENT_PROD.price_label_1 && (!match.price_label_1 || window.XTRAL_CURRENT_PROD.price_label_1 !== 'Zone 1')) {
          match.price_label_1 = window.XTRAL_CURRENT_PROD.price_label_1;
        }
        if (window.XTRAL_CURRENT_PROD.price_label_2 && (!match.price_label_2 || window.XTRAL_CURRENT_PROD.price_label_2 !== 'Zone 2')) {
          match.price_label_2 = window.XTRAL_CURRENT_PROD.price_label_2;
        }
      }
    }
  }
}

/* ---- Load everything, then render the page ---- */
(async function xtralLoadData() {
  const notify = () => { if (typeof window.XTRAL_RENDER === 'function') window.XTRAL_RENDER(); };

  const cached = xtralGetStorage('xtral_catalogue');
  let hasRendered = false;

  if (cached && cached.data) {
    xtralPopulateData(cached.data);
    hasRendered = true;
    if (typeof window.XTRAL_RENDER === 'function') notify();
    else if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', notify);
    else setTimeout(notify, 50);
  }

  /* Fetch each endpoint independently so one failure does not blank the
     whole front page — banners, products, categories etc. each survive
     on their own even if a sibling call throws. */
  const safe = (promise, name) =>
    promise.catch(err => {
      console.warn(`X-tral: ${name} failed —`, err.message || err);
      return null; // null = "no data, keep going"
    });

  const [cats, subs, series, prods, banners] = await Promise.all([
    safe(xtralApiGet('categories.php'),    'categories'),
    safe(xtralApiGet('sub_categories.php'),'sub_categories'),
    safe(xtralApiGet('series.php'),        'series'),
    safe(xtralApiGet('products.php'),      'products'),
    safe(xtralApiGet('banners.php'),       'banners'),
  ]);

  const payload = { cats, subs, series, prods, banners };
  xtralPopulateData(payload);
  xtralSetStorage('xtral_catalogue', payload);

  // main.js defines this — renders categories, products, details, etc.
  if (typeof window.XTRAL_RENDER === 'function') notify();
  else if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', notify);
  else setTimeout(notify, 50);
})();
