<?php
require_once __DIR__ . '/xadmin/config/db.php';

$idParam = trim($_GET['id'] ?? '');
$prod = null;
$prodVariants = [];
$primaryImg = '';
$matchedVariant = null;

if ($idParam !== '') {
    try {
        // 1. Try matching product ID directly or from {id}-{slug} URL
        $numericId = null;
        if (ctype_digit($idParam)) {
            $numericId = (int)$idParam;
        } elseif (preg_match('/^(\d+)(?:-.*)?$/', $idParam, $matches)) {
            $numericId = (int)$matches[1];
        }

        if ($numericId !== null) {
            $prod = fetchOne("SELECT p.*, s.name as series_name, c.name as category_name, c.id as category_id 
                             FROM catalogue_products p 
                             LEFT JOIN catalogue_series s ON p.series_id = s.id 
                             LEFT JOIN catalogue_categories c ON COALESCE(p.category_id, s.category_id) = c.id 
                             WHERE p.id = ? AND p.is_active = 1", [$numericId]);
        }
        
        // 2. Try matching product code
        if (!$prod) {
            $cleanParam = preg_replace('/[^a-zA-Z0-9]/', '', $idParam);
            $dashParam = dashCode($idParam);
            
            $whereCodes = ["p.code = ?"];
            $params = [$idParam];
            if ($dashParam !== '' && $dashParam !== $idParam) {
                $whereCodes[] = "p.code = ?";
                $params[] = $dashParam;
            }
            if ($cleanParam !== '' && $cleanParam !== $idParam && $cleanParam !== $dashParam) {
                $whereCodes[] = "p.code = ?";
                $params[] = $cleanParam;
            }
            if ($cleanParam !== '') {
                $whereCodes[] = "REPLACE(p.code, '-', '') = ?";
                $params[] = $cleanParam;
            }
            
            $sql = "SELECT p.*, s.name as series_name, c.name as category_name, c.id as category_id 
                    FROM catalogue_products p 
                    LEFT JOIN catalogue_series s ON p.series_id = s.id 
                    LEFT JOIN catalogue_categories c ON COALESCE(p.category_id, s.category_id) = c.id 
                    WHERE (" . implode(' OR ', $whereCodes) . ") AND p.is_active = 1 
                    ORDER BY (p.price_label_1 IS NOT NULL AND p.price_label_1 != '') DESC, p.id ASC LIMIT 1";
            $prod = fetchOne($sql, $params);
        }

        // 3. Try matching variant code
        if (!$prod) {
            $cleanParam = preg_replace('/[^a-zA-Z0-9]/', '', $idParam);
            $dashParam = dashCode($idParam);
            
            $vWhere = ["code = ?"];
            $vParams = [$idParam];
            if ($dashParam !== '' && $dashParam !== $idParam) {
                $vWhere[] = "code = ?";
                $vParams[] = $dashParam;
            }
            if ($cleanParam !== '' && $cleanParam !== $idParam && $cleanParam !== $dashParam) {
                $vWhere[] = "code = ?";
                $vParams[] = $cleanParam;
            }
            if ($cleanParam !== '') {
                $vWhere[] = "REPLACE(code, '-', '') = ?";
                $vParams[] = $cleanParam;
            }

            $vSql = "SELECT * FROM catalogue_product_variants WHERE (" . implode(' OR ', $vWhere) . ") AND is_active = 1 LIMIT 1";
            $matchedVariant = fetchOne($vSql, $vParams);
            
            if ($matchedVariant) {
                $prod = fetchOne("SELECT p.*, s.name as series_name, c.name as category_name, c.id as category_id 
                                 FROM catalogue_products p 
                                 LEFT JOIN catalogue_series s ON p.series_id = s.id 
                                 LEFT JOIN catalogue_categories c ON COALESCE(p.category_id, s.category_id) = c.id 
                                 WHERE p.id = ? AND p.is_active = 1", [(int)$matchedVariant['product_id']]);
            }
        }
    } catch (\Throwable $e) {
        // Fallback gracefully without 500 error
    }
}

// Canonical 301 redirect: Always serve the authoritative {id}-{code} URL.
// e.g. /products/WB123016  →  /products/45-WB123016
// This prevents duplicate-code ambiguity (two products with the same code).
if ($prod) {
    $mergedCode = '';
    if (!empty($prod['code']) && $prod['code'] !== '—') {
        $mergedCode = preg_replace('/[^a-zA-Z0-9]/', '', $prod['code']);
    }
    $canonicalSlug = $mergedCode ? $prod['id'] . '-' . $mergedCode : $prod['id'];

    // Redirect only if the current URL doesn't already start with this product's numeric ID
    if ($idParam !== '' && !preg_match('/^' . preg_quote($prod['id'], '/') . '(?:-|$)/', $idParam)) {
        $docRoot = !empty($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])) : '';
        $frontDir = str_replace('\\', '/', realpath(__DIR__));
        $basePath = '';
        if ($docRoot !== '' && stripos($frontDir, $docRoot) === 0) {
            $basePath = rtrim(substr($frontDir, strlen($docRoot)), '/');
        }
        $targetUrl = ($basePath !== '' ? $basePath : '') . '/products/' . rawurlencode($canonicalSlug);
        header('Location: ' . $targetUrl, true, 301);
        exit;
    }
}

try {
    // Fallback to first active product if param was empty
    if (!$prod) {
        $prod = fetchOne("SELECT p.*, s.name as series_name, c.name as category_name, c.id as category_id 
                         FROM catalogue_products p 
                         LEFT JOIN catalogue_series s ON p.series_id = s.id 
                         LEFT JOIN catalogue_categories c ON COALESCE(p.category_id, s.category_id) = c.id 
                         WHERE p.is_active = 1 ORDER BY p.id ASC LIMIT 1");
    }

    if ($prod) {
        $prodVariants = fetchAll("SELECT v.*, c.name as color_name, c.hex_code 
                                  FROM catalogue_product_variants v 
                                  LEFT JOIN catalogue_colors c ON v.color_id = c.id 
                                  WHERE v.product_id = ? AND v.is_active = 1 
                                  ORDER BY v.display_order ASC", [$prod['id']]);

        // Primary image — files are stored under xadmin/uploads/
        if (!empty($matchedVariant['image_url'])) {
            $primaryImg = 'xadmin/uploads/catalogue/products/' . $matchedVariant['image_url'];
        } elseif (!empty($prod['primary_image'])) {
            $primaryImg = 'xadmin/uploads/catalogue/products/' . $prod['primary_image'];
        } elseif (!empty($prodVariants)) {
            foreach ($prodVariants as $pv) {
                if (!empty($pv['image_url'])) {
                    $primaryImg = 'xadmin/uploads/catalogue/products/' . $pv['image_url'];
                    break;
                }
            }
        }
        // Also query the product_images table if still empty
        if (empty($primaryImg)) {
            $imgRow = fetchOne(
                "SELECT image_url FROM catalogue_product_images WHERE product_id = ? ORDER BY is_primary DESC, display_order, id LIMIT 1",
                [$prod['id']]
            );
            if ($imgRow && !empty($imgRow['image_url'])) {
                $primaryImg = 'xadmin/uploads/catalogue/products/' . $imgRow['image_url'];
            }
        }
    }
} catch (\Throwable $t) {
    // Ensure page continues rendering even if fallback query fails
}

$priceLabel1 = getProductPriceLabel1($prod['id'] ?? null, $prod['price_label_1'] ?? null);
$priceLabel2 = getProductPriceLabel2($prod['id'] ?? null, $prod['price_label_2'] ?? null);
$priceVal1 = !empty($matchedVariant['price']) ? (float)$matchedVariant['price'] : (!empty($prod['price']) ? (float)$prod['price'] : 0);
$priceVal2 = !empty($matchedVariant['price_zone2']) ? (float)$matchedVariant['price_zone2'] : (!empty($prod['price_zone2']) ? (float)$prod['price_zone2'] : 0);

// header.php escapes these — pass them raw (escaping here too double-encodes the " in sizes)
$pageTitle = ($prod ? $prod['name'] . ' — ' : '') . 'X-Tral';
$pageDescription = ($prod && !empty($prod['short_description']))
    ? trim(strip_tags($prod['short_description']))
    : 'X-Tral product details, specifications and model information.';

// Link preview when the page is shared: product photo + model / size / price
if ($prod) {
    if ($primaryImg !== '') $ogImage = $primaryImg;
    $ogParts = [];
    $ogCode = $matchedVariant['code'] ?? $prod['code'] ?? '';
    if ($ogCode !== '') $ogParts[] = 'Model ' . dashCode($ogCode);
    if (!empty($prod['dimensions'])) $ogParts[] = 'Size ' . $prod['dimensions'];
    $ogPrice = $priceVal1 > 0 ? $priceVal1 : $priceVal2;
    if ($ogPrice > 0) $ogParts[] = '₹' . number_format($ogPrice, floor($ogPrice) == $ogPrice ? 0 : 2) . ' M.R.P.';
    $ogDescription = implode(' · ', $ogParts) ?: $pageDescription;
    // WhatsApp shows <meta name="description">, so don't leave it as the generic text
    if (empty($prod['short_description'])) $pageDescription = $ogDescription;
}

include __DIR__ . '/includes/header.php';
?>
<?php if (!empty($prod)): ?>
<script>
  window.XTRAL_CURRENT_PROD = <?= json_encode([
      'id' => (string)$prod['id'],
      'name' => $prod['name'],
      'code' => $prod['code'],
      'price_label_1' => $priceLabel1,
      'price_label_2' => $priceLabel2,
      'fallback_label_1' => $priceLabel1,
      'fallback_label_2' => $priceLabel2,
  ]) ?>;
</script>
<?php endif; ?>

  <div class="container pd-topbar">
    <div class="crumbs">
      <a href="index">Home</a><span class="sep">/</span>
      <a href="products">Products</a><span class="sep">/</span>
      <?php if ($prod && !empty($prod['category_name'])): ?>
        <a data-pd-crumb href="products?cat=<?= $prod['category_id'] ?>"><?= htmlspecialchars($prod['category_name']) ?></a><span class="sep">/</span>
      <?php else: ?>
        <a data-pd-crumb href="products">Category</a><span class="sep">/</span>
      <?php endif; ?>
      <span data-pd-name><?= htmlspecialchars($prod['name'] ?? 'Product') ?></span>
    </div>
    <div class="pd-share" data-pd-share-wrap>
      <button type="button" class="pd-share-btn" data-pd-share aria-label="Share product" aria-haspopup="true" aria-expanded="false" title="Share">
        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4"/></svg>
      </button>
      <div class="pd-share-menu" data-pd-share-menu role="menu" hidden>
        <button type="button" role="menuitem" data-share-action="whatsapp">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.7-1.4.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.8 11.9 11.9 0 0 0 4.6 4c1.7.7 2.3.8 3.2.6.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2-.1-.1-.2-.2-.4-.3z"/></svg>
          WhatsApp
        </button>
        <button type="button" role="menuitem" data-share-action="copy">
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"/></svg>
          <span data-share-copy-label>Copy details &amp; link</span>
        </button>
        <button type="button" role="menuitem" data-share-action="native" hidden>
          <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="5" cy="12" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/></svg>
          More options
        </button>
      </div>
    </div>
  </div>

  <section class="section" style="padding-top:20px;" data-product-detail>
    <div class="container">
      <div class="pd">
        <div class="pd-media-gallery">
          <div class="pd-media" data-pd-media-container>
            <button type="button" class="pd-media-arrow pd-media-arrow--prev" aria-label="Previous media" style="display: none;">
              <svg width="24" height="24" viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
            </button>
            <img data-pd-img src="<?= htmlspecialchars($primaryImg) ?>" alt="<?= htmlspecialchars($prod['name'] ?? 'Product') ?>" <?= empty($primaryImg) ? 'style="display:none"' : '' ?>>
            <div data-pd-video-container style="display:none; width: 100%; height: 100%;"></div>
            <button type="button" class="pd-media-arrow pd-media-arrow--next" aria-label="Next media" style="display: none;">
              <svg width="24" height="24" viewBox="0 0 24 24"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
            </button>
          </div>
          <div class="pd-thumbs-wrapper">
            <button type="button" class="pd-thumbs-arrow pd-thumbs-arrow--prev" aria-label="Previous thumbnails" style="display: none;">
              <svg width="18" height="18" viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
            </button>
            <div class="pd-thumbs" data-pd-thumbs></div>
            <button type="button" class="pd-thumbs-arrow pd-thumbs-arrow--next" aria-label="Next thumbnails" style="display: none;">
              <svg width="18" height="18" viewBox="0 0 24 24"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
            </button>
          </div>
        </div>
        <div>
          <span class="pd-cat" data-pd-cat><?= htmlspecialchars($prod['category_name'] ?? 'Category') ?></span>
          <h1 data-pd-name><?= htmlspecialchars($prod['name'] ?? 'Product Name') ?></h1>
          <p class="code" data-pd-code>Model <?= htmlspecialchars(dashCode($matchedVariant['code'] ?? $prod['code'] ?? '')) ?></p>
          <div class="pd-dimensions" data-pd-dimensions style="display: none; margin-top: -12px; margin-bottom: 24px; color: var(--muted); font-size: 0.9rem; font-weight: 500;"></div>
          <div class="price" data-pd-price>
            <?php if ($priceVal1 > 0 && $priceVal2 > 0): ?>
              <?php
                $dispLbl1 = rtrim(trim($priceLabel1 ?? ''), ':');
                $dispLbl2 = rtrim(trim($priceLabel2 ?? ''), ':');
                $dispVal1 = (floor($priceVal1) == $priceVal1) ? number_format($priceVal1, 0) : number_format($priceVal1, 2);
                $dispVal2 = (floor($priceVal2) == $priceVal2) ? number_format($priceVal2, 0) : number_format($priceVal2, 2);
              ?>
              <div class="pd-dual-prices">
                <div class="pd-dual-prices-inline">
                  <div class="pd-price-row"><span class="pd-price-label"><?= htmlspecialchars($dispLbl1) ?>:</span> <span class="pd-price-val">₹<?= $dispVal1 ?></span></div>
                  <span class="pd-price-divider" aria-hidden="true">|</span>
                  <div class="pd-price-row"><span class="pd-price-label"><?= htmlspecialchars($dispLbl2) ?>:</span> <span class="pd-price-val">₹<?= $dispVal2 ?></span></div>
                </div>
                <small class="pd-tax-note">M.R.P. (incl. of all taxes)</small>
              </div>
            <?php elseif ($priceVal1 > 0): ?>
              <?php $dispVal1 = (floor($priceVal1) == $priceVal1) ? number_format($priceVal1, 0) : number_format($priceVal1, 2); ?>
              <?= '₹' . $dispVal1 . ' <small>M.R.P. (incl. of all taxes)</small>' ?>
            <?php elseif ($priceVal2 > 0): ?>
              <?php $dispVal2 = (floor($priceVal2) == $priceVal2) ? number_format($priceVal2, 0) : number_format($priceVal2, 2); ?>
              <?= '₹' . $dispVal2 . ' <small>M.R.P. (incl. of all taxes)</small>' ?>
            <?php else: ?>
              On request
            <?php endif; ?>
          </div>

          <div class="pd-variants" data-pd-variants></div>

          <div class="pd-desc-block" data-pd-desc-block hidden>
            <span class="pd-desc-label">Description</span>
            <div class="pd-short-desc" data-pd-short-desc title="Click to read the full description"></div>
            <button type="button" class="pd-desc-toggle" data-pd-desc-toggle hidden>Read more</button>
          </div>

          <?php
            $noticeRange = ($prod['series_name'] ?? '') . ' ' . ($prod['category_name'] ?? '');
            $showProductNotice = preg_match('/\b(?:vanitys|vanities|vanity|(?:quardz|quartz)\s+sinks?)\b/i', $noticeRange);
          ?>
          <p data-pd-representation-notice <?= $showProductNotice ? '' : 'hidden' ?> style="margin:16px 0; font-size:0.875rem; line-height:1.6; color:var(--muted);">
            <strong>Notice:</strong> The images shown are for representation purposes only. Actual product images &amp; size may vary slightly.
          </p>

          <div class="features-wrap" data-pd-features-wrap style="display:none">
            <p class="features-heading">Features</p>
            <div class="features" data-pd-features></div>
          </div>

          <div class="pd-actions" style="margin-top: 30px;">
            <a href="contact" class="btn btn--dark">Enquire Now <span class="ar">→</span></a>
            <a href="catalogue" class="btn btn--outline">Download Catalogue</a>
          </div>

        </div>
      </div>
    </div>
  </section>

  <section class="section section--tight" style="background:var(--paper-2);">
    <div class="container">
      <div class="sec-head" data-reveal>
        <span class="eyebrow">You may also like</span>
        <h2>Related products</h2>
      </div>
      <div class="prod-grid" data-pd-related></div>
    </div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
