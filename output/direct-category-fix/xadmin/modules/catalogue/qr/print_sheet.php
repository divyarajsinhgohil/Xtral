<?php
/**
 * Printable QR Code Sheets for Graphic Designers & Catalogue Production
 */
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();
require_once dirname(__DIR__, 3) . '/includes/catalogue_product_hierarchy.php';
$productHierarchy = catalogueProductHierarchySql(getDBConnection());
$productCategorySql = $productHierarchy['category'];
$productSubCategorySql = $productHierarchy['sub_category'];

$categoryId = !empty($_GET['category_id']) ? (int)$_GET['category_id'] : null;

$where = ["q.is_active = 1"];
$params = [];

if ($categoryId) {
    $where[] = "{$productCategorySql} = ?";
    $params[] = $categoryId;
}

$whereSql = implode(" AND ", $where);

$sql = "SELECT q.*, 
               p.name as prod_name, p.code as prod_code,
               s.name as series_name, c.name as category_name
        FROM catalogue_qr_codes q
        LEFT JOIN catalogue_products p ON q.product_id = p.id
        LEFT JOIN catalogue_series s ON p.series_id = s.id
        LEFT JOIN catalogue_categories c ON {$productCategorySql} = c.id
        WHERE {$whereSql}
        ORDER BY c.name ASC, s.name ASC, q.code ASC";

$items = fetchAll($sql, $params);
$categories = fetchAll("SELECT id, name FROM catalogue_categories WHERE is_active = 1 ORDER BY name ASC");
$frontBase = getFrontBaseUrl();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>X-Tral — Catalogue QR Codes Print Sheet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="<?= BASE_URL ?>/assets/js/qrcode.min.js"></script>
    <style>
        body {
            background: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #1a1a1a;
        }
        .no-print {
            background: #ffffff;
            border-bottom: 1px solid #dee2e6;
            padding: 12px 24px;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .qr-card {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            page-break-inside: avoid;
        }
        .qr-canvas-holder {
            width: 140px;
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }
        .qr-canvas-holder canvas, .qr-canvas-holder img {
            max-width: 100%;
            max-height: 100%;
        }
        .qr-code-label {
            font-family: monospace;
            font-size: 0.95rem;
            font-weight: 700;
            background: #212529;
            color: #ffffff;
            padding: 2px 8px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 4px;
        }
        .prod-name {
            font-size: 0.85rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 2px;
        }
        .prod-meta {
            font-size: 0.75rem;
            color: #6c757d;
        }
        .perm-url {
            font-size: 0.65rem;
            color: #888;
            word-break: break-all;
            margin-top: 4px;
        }

        @media print {
            body {
                background: #ffffff !important;
            }
            .no-print {
                display: none !important;
            }
            .container-fluid {
                padding: 0 !important;
            }
            .qr-card {
                border: 1px dashed #ccc !important;
                box-shadow: none !important;
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>

    <!-- Controls (Hidden on print) -->
    <div class="no-print d-flex flex-wrap justify-content-between align-items-center gap-3 shadow-sm">
        <div>
            <h5 class="mb-0 fw-bold"><i class="fas fa-qrcode text-primary me-2"></i>Catalogue QR Code Sheets</h5>
            <small class="text-muted">Total: <?= count($items) ?> QR codes ready for catalogue placement</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <form method="GET" class="d-flex align-items-center gap-2">
                <select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="fas fa-print me-1"></i> Print / Save as PDF
            </button>
            <a href="list.php" class="btn btn-outline-secondary btn-sm">Close</a>
        </div>
    </div>

    <!-- Printable Grid -->
    <div class="container-fluid p-4">
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-3">
            <?php foreach ($items as $item): ?>
                <?php 
                    $url = $frontBase . '/qr/' . rawurlencode($item['code']);
                ?>
                <div class="col">
                    <div class="qr-card">
                        <div>
                            <span class="qr-code-label"><?= htmlspecialchars($item['code']) ?></span>
                            <div class="qr-canvas-holder mx-auto" data-url="<?= htmlspecialchars($url) ?>">
                                <div id="qr_<?= $item['id'] ?>"></div>
                            </div>
                        </div>
                        <div>
                            <div class="prod-name">
                                <?= htmlspecialchars($item['prod_name'] ?: $item['label'] ?: 'Catalogue Link') ?>
                            </div>
                            <?php if (!empty($item['prod_code']) && $item['prod_code'] !== '—'): ?>
                                <div class="prod-meta fw-bold text-dark"><?= htmlspecialchars($item['prod_code']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($item['category_name']) || !empty($item['series_name'])): ?>
                                <div class="prod-meta">
                                    <?= htmlspecialchars($item['category_name'] ?? '') ?> 
                                    <?= !empty($item['series_name']) ? '· ' . htmlspecialchars($item['series_name']) : '' ?>
                                </div>
                            <?php endif; ?>
                            <div class="perm-url"><?= htmlspecialchars($url) ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".qr-canvas-holder").forEach(el => {
                const url = el.getAttribute("data-url");
                const target = el.firstElementChild;
                if (url && target) {
                    new QRCode(target, {
                        text: url,
                        width: 140,
                        height: 140,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.M
                    });
                }
            });
        });
    </script>
</body>
</html>
