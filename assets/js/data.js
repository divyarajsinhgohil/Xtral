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
        val: v.color_hex || '#d7dde0',
        img: v.image || null
      }))
    };
  }

  // If the product itself has no price but its variants do, show the
  // first variant's price instead of "On request" — the base product
  // record is often left blank when pricing only varies by variant.
  const firstVariantPrice = variants && variants.items.length
    ? variants.items.find(v => v.price)?.price
    : null;

  return {
    id: String(p.id),
    cat: String(p.category_id),
    subId: p.sub_category_id != null ? String(p.sub_category_id) : null,
    seriesId: p.series_id != null ? String(p.series_id) : null,
    name: p.name,
    code: p.code || '—',
    price: p.price || firstVariantPrice || null,
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
    dimensions: p.dimensions || ''
  };
}

/* ---- Company branding: swap the text brand for the uploaded logo ----
   Logo is uploaded in Admin → Configuration → Company Info and served
   by webapi/site.php. Header uses `.brand img`; footer gets the extra
   `.footer-logo` class so the CSS inverts it to white. */
function xtralApplyBranding(site) {
  if (!site || !site.logo) return;
  document.querySelectorAll('.site-header .brand, footer .brand').forEach(brand => {
    const img = document.createElement('img');
    img.src = site.logo;
    img.alt = site.name || 'X-Tral';
    if (brand.closest('footer')) img.classList.add('footer-logo');
    brand.replaceChildren(img);
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

(async function xtralLoadSite() {
  try {
    const site = await xtralApiGet('site.php');
    window.XTRAL_SITE = site;
    xtralApplyBranding(site);
    xtralApplySiteInfo(site);
  } catch (err) {
    // No logo configured or API unreachable — keep the placeholder text.
  }
})();

/* ---- Load everything, then render the page ---- */
(async function xtralLoadData() {
  try {
    const [cats, subs, series, prods, banners] = await Promise.all([
      xtralApiGet('categories.php'),
      xtralApiGet('sub_categories.php'),
      xtralApiGet('series.php'),
      xtralApiGet('products.php'),
      xtralApiGet('banners.php')
    ]);

    XTRAL_BANNERS = banners.map(b => ({
      id: b.id,
      title: b.title || '',
      img: b.image,
      video: b.video || null,
      type: b.banner_type || 'image',
      link: b.link || null
    }));

    XTRAL_CATEGORIES = cats.map(c => ({
      id: String(c.id),
      name: c.name,
      img: c.image || '',
      blurb: c.description || ''
    }));

    XTRAL_SUBCATS = subs.map(s => ({
      id: String(s.id),
      cat: String(s.category_id),
      name: s.name,
      img: s.image || '',
      blurb: s.description || ''
    }));

    XTRAL_SERIES = series.map(s => ({
      id: String(s.id),
      cat: String(s.category_id),
      sub: s.sub_category_id != null ? String(s.sub_category_id) : null,
      name: s.name,
      img: s.image || '',
      blurb: s.description || '',
      is_new_arrival: !!s.is_new_arrival
    }));

    XTRAL_PRODUCTS = prods.map(xtralMapProduct);
  } catch (err) {
    console.error('X-tral: could not load data from API —', err);
  }

  // main.js defines this — renders categories, products, details, etc.
  // Fallback: if main.js hasn't loaded yet, try again when the DOM is ready.
  const notify = () => { if (typeof window.XTRAL_RENDER === 'function') window.XTRAL_RENDER(); };
  if (typeof window.XTRAL_RENDER === 'function') notify();
  else if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', notify);
  else setTimeout(notify, 200);
})();
