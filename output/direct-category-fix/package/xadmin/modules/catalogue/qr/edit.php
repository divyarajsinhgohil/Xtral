<?php
/**
 * Edit QR Code
 * Permanent Parent Link / Target Destination editor
 */
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();
require_once dirname(__DIR__, 3) . '/includes/catalogue_product_hierarchy.php';
$productHierarchy = catalogueProductHierarchySql(getDBConnection());
$productCategorySql = $productHierarchy['category'];
$productSubCategorySql = $productHierarchy['sub_category'];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: list.php');
    exit;
}

$qr = fetchOne("SELECT q.*, p.name AS prod_name, p.code AS prod_code 
                FROM catalogue_qr_codes q 
                LEFT JOIN catalogue_products p ON q.product_id = p.id 
                WHERE q.id = ?", [$id]);
if (!$qr) {
    $_SESSION['error'] = "QR Code not found.";
    header('Location: list.php');
    exit;
}

$activePage = 'catalogue_qr';
$pageTitle = 'Edit QR Code: ' . htmlspecialchars($qr['code']);

$allProducts = fetchAll("SELECT p.id, p.name, p.code, s.name as series_name, c.name as category_name 
                        FROM catalogue_products p 
                        LEFT JOIN catalogue_series s ON p.series_id = s.id 
                        JOIN catalogue_categories c ON {$productCategorySql} = c.id 
                        WHERE p.is_active = 1 
                        ORDER BY c.name ASC, s.name ASC, p.name ASC");

$frontBase = getFrontBaseUrl();
$permUrl = $frontBase . '/qr/' . rawurlencode($qr['code']);

$additionalCSS = '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />';

$additionalJS = '<script src="' . BASE_URL . '/assets/js/qrcode.min.js"></script>
<script src="' . BASE_URL . '/assets/js/qrcode-generator.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>';

include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="list.php" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-2"></i>Back to QR Codes
            </a>
            <h2><i class="fas fa-edit text-primary me-2"></i>Edit QR Code: <?= htmlspecialchars($qr['code']) ?></h2>
            <p class="text-muted small">
                You can change the target product anytime. The permanent QR code and URL printed in the physical catalogue will stay exactly the same.
            </p>
        </div>
    </div>

    <div class="row">
        <!-- Edit Form -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light fw-bold py-3">
                    <i class="fas fa-link me-2"></i>QR Code Configuration
                </div>
                <div class="card-body p-4">
                    <form action="save_process.php" method="POST">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" value="<?= $qr['id'] ?>">

                        <!-- Permanent Code -->
                        <div class="mb-3">
                            <label for="code" class="form-label fw-bold">Permanent QR Code Identifier <span class="text-danger">*</span></label>
                            <input type="text" class="form-control font-monospace fw-bold" id="code" name="code" 
                                   value="<?= htmlspecialchars($qr['code']) ?>" required>
                            <div class="form-text text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i> If this QR code is already printed in physical catalogues, do not change this code. Changing it will break already printed QR codes!
                            </div>
                        </div>

                        <!-- Target Product (Child link) -->
                        <div class="mb-3">
                            <label for="product_id" class="form-label fw-bold">Target Product Destination (Child Link)</label>
                            <select name="product_id" id="product_id" class="form-select select2-init" style="width: 100%;">
                                <option value="">-- No Product Linked --</option>
                                <?php foreach ($allProducts as $p): ?>
                                    <option value="<?= $p['id'] ?>" <?= $qr['product_id'] == $p['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['name']) ?> 
                                        <?= (!empty($p['code']) && $p['code'] !== '—' ? '[' . htmlspecialchars($p['code']) . ']' : '') ?>
                                        (<?= htmlspecialchars($p['category_name']) ?> - <?= htmlspecialchars($p['series_name']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-muted">
                                When customers scan the QR code in the catalogue, they will be redirected to this product's detail page.
                            </div>
                        </div>

                        <!-- Custom URL Override (Optional) -->
                        <div class="mb-3">
                            <label for="target_url" class="form-label fw-bold">Custom URL Override <small class="text-muted">(Optional)</small></label>
                            <input type="url" class="form-control" id="target_url" name="target_url" 
                                   value="<?= htmlspecialchars($qr['target_url'] ?? '') ?>" 
                                   placeholder="e.g. https://x-tral.com/custom-page.php">
                            <div class="form-text text-muted">
                                If filled, scans will redirect directly to this URL instead of the linked product.
                            </div>
                        </div>

                        <!-- Label / Catalogue Section -->
                        <div class="mb-3">
                            <label for="label" class="form-label fw-bold">Label / Catalogue Note</label>
                            <input type="text" class="form-control" id="label" name="label" 
                                   value="<?= htmlspecialchars($qr['label'] ?? '') ?>" 
                                   placeholder="e.g. Page 12 - MASS Commode">
                        </div>

                        <!-- Status Active -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                       <?= $qr['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold" for="is_active">Active (QR code responds to scans)</label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                            <a href="list.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- QR Code Preview & Download Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                <div class="card-header bg-light fw-bold py-3 text-center">
                    <i class="fas fa-qrcode me-2"></i>Catalogue QR Code
                </div>
                <div class="card-body text-center p-4">
                    <div class="p-3 bg-white border rounded shadow-sm d-inline-block mb-3" style="max-width: 250px;">
                        <div id="page_qr_canvas"></div>
                        <div class="mt-2 fw-bold text-dark badge bg-dark fs-6"><?= htmlspecialchars($qr['code']) ?></div>
                    </div>

                    <div class="mb-3 text-start">
                        <label class="small text-muted fw-bold">Permanent URL on Catalogue:</label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control font-monospace" id="perm_url_input" value="<?= htmlspecialchars($permUrl) ?>" readonly>
                            <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($permUrl) ?>'); alert('Permanent URL copied to clipboard!');">
                                <i class="far fa-copy"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Text Option Toggle for Single Download -->
                    <div class="d-flex align-items-center justify-content-between p-2 mb-3 bg-light border rounded">
                        <div class="text-start">
                            <span class="d-block fw-bold small text-dark"><i class="fas fa-font text-primary me-1"></i> Text below QR:</span>
                            <small class="text-success fw-semibold" id="editModalTextHelper">Only QR Code (Without text)</small>
                        </div>
                        <div class="form-check form-switch m-0 fs-5">
                            <input class="form-check-input" type="checkbox" id="editIncludeTextSwitch" onchange="toggleEditTextSwitch(this)">
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-primary btn-sm w-100 text-truncate" onclick="downloadQr(1200, 'png')">
                                <i class="far fa-file-image me-1"></i> <strong>PNG</strong> Transparent (1200px)
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-danger btn-sm w-100 text-truncate" onclick="downloadQr(1200, 'jpg')">
                                <i class="fas fa-file-image me-1"></i> <strong>JPG</strong> White BG (1200px)
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-secondary btn-sm w-100 text-truncate" onclick="downloadQr(300, 'png')">
                                PNG Transparent (300px)
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-secondary btn-sm w-100 text-truncate" onclick="downloadQr(300, 'jpg')">
                                JPG White BG (300px)
                            </button>
                        </div>
                        <div class="col-12 mt-1">
                            <button type="button" class="btn btn-outline-success btn-sm w-100" onclick="downloadQr(0, 'svg')">
                                <i class="fas fa-bezier-curve me-1"></i> <strong>SVG Vector</strong> (Transparent Cutout for CorelDraw / InDesign)
                            </button>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-2" style="font-size: 0.75rem;">
                        PNG &amp; SVG are exported without background (transparent) for direct placement onto coloured or wooden catalogue pages.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    $('.select2-init').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Choose Product --'
    });

    const qrUrl = "<?= htmlspecialchars($permUrl) ?>";
    const qrCode = "<?= htmlspecialchars($qr['code']) ?>";
    const prodName = "<?= addslashes($qr['prod_name'] ?? $qr['label'] ?? '') ?>";
    const prodCode = "<?= addslashes($qr['prod_code'] ?? '') ?>";

    const container = document.getElementById("page_qr_canvas");
    new QRCode(container, {
        text: qrUrl,
        width: 200,
        height: 200,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });

    window.toggleEditTextSwitch = function(el) {
        const helper = document.getElementById('editModalTextHelper');
        if (helper) {
            helper.textContent = el.checked ? 'With Product Name & Code text' : 'Only QR Code (Without text)';
            helper.className = el.checked ? 'text-primary fw-semibold small' : 'text-success fw-semibold small';
        }
    };

    function sanitizeZipPathPart(str) {
        if (!str) return '';
        let clean = str
            .replace(/["'\\/?:*|<>]/g, '')
            .replace(/[\x00-\x1F\x7F]/g, '')
            .trim()
            .replace(/[. ]+$/, '');

        clean = clean.replace(/^CON\.\s*/i, 'CONCEALED ');
        if (/^(CON|PRN|AUX|NUL|COM[1-9]|LPT[1-9])(\.|$|\s)/i.test(clean)) {
            clean = '_' + clean;
        }
        return clean;
    }

    function getQrFileName(prodName, code, ext = 'png') {
        const cleanName = sanitizeZipPathPart(prodName);
        const cleanCode = sanitizeZipPathPart(code) || 'QR';
        return `${cleanName ? cleanName + ' (' + cleanCode + ')' : cleanCode}.${ext}`;
    }

    function renderCustomCanvas(url, code, prodName, prodCode, size, format, includeText, callback) {
        if (typeof includeText === 'function') {
            callback = includeText;
            includeText = false;
        }
        if (typeof qrcode !== 'function') {
            callback(null);
            return;
        }

        const qr = qrcode(0, 'H');
        qr.addData(url);
        qr.make();
        const count = qr.getModuleCount();
        const margin = 2;
        const totalModules = count + margin * 2;
        const cellSize = Math.floor(size / totalModules);
        const qrActualSize = cellSize * totalModules;

        const padding = Math.round(size * 0.04);
        const captionHeight = includeText ? Math.round(size * 0.16) : 0;

        const finalCanvas = document.createElement("canvas");
        finalCanvas.width = qrActualSize + (padding * 2);
        finalCanvas.height = qrActualSize + (padding * 2) + captionHeight;
        const ctx = finalCanvas.getContext("2d");

        // 1. Background
        if (format === 'jpg') {
            ctx.fillStyle = "#ffffff";
            ctx.fillRect(0, 0, finalCanvas.width, finalCanvas.height);
        } else {
            ctx.clearRect(0, 0, finalCanvas.width, finalCanvas.height);
        }

        // 2. Draw QR code modules
        ctx.fillStyle = "#000000";
        for (let r = 0; r < count; r++) {
            for (let c = 0; c < count; c++) {
                if (qr.isDark(r, c)) {
                    ctx.fillRect(
                        padding + (c + margin) * cellSize,
                        padding + (r + margin) * cellSize,
                        cellSize,
                        cellSize
                    );
                }
            }
        }

        // 3. Draw text ONLY IF includeText is true
        if (includeText) {
            const displayName = (prodName && prodName.trim()) ? prodName.trim() : code;
            ctx.fillStyle = "#111827";
            ctx.font = `bold ${Math.round(size * 0.046)}px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif`;
            ctx.textAlign = "center";
            ctx.fillText(displayName.slice(0, 36), finalCanvas.width / 2, padding + qrActualSize + Math.round(size * 0.065));

            let subText = code;
            if (prodCode && prodCode !== '—') subText += ` • ${prodCode}`;
            ctx.fillStyle = "#4b5563";
            ctx.font = `500 ${Math.round(size * 0.034)}px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif`;
            ctx.fillText(subText, finalCanvas.width / 2, padding + qrActualSize + Math.round(size * 0.12));
        }

        callback(finalCanvas);
    }

    function generateSvgString(url, code, prodName, prodCode, isTransparent = true, includeText = false) {
        if (typeof qrcode !== 'function') return null;
        const qr = qrcode(0, 'H');
        qr.addData(url);
        qr.make();
        const count = qr.getModuleCount();
        const margin = 2;
        const cellSize = 10;
        const size = (count + margin * 2) * cellSize;
        const extraBottom = includeText ? 70 : 0;
        const totalHeight = size + extraBottom;

        let svg = `<?xml version="1.0" encoding="UTF-8"?>\n<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${size} ${totalHeight}" width="${size}" height="${totalHeight}">`;
        if (!isTransparent) {
            svg += `\n<rect width="100%" height="100%" fill="#ffffff"/>`;
        }

        for (let r = 0; r < count; r++) {
            for (let c = 0; c < count; c++) {
                if (qr.isDark(r, c)) {
                    const x = (c + margin) * cellSize;
                    const y = (r + margin) * cellSize;
                    svg += `\n<rect x="${x}" y="${y}" width="${cellSize}" height="${cellSize}" fill="#000000"/>`;
                }
            }
        }

        if (includeText) {
            const displayName = (prodName && prodName.trim()) ? prodName.trim() : code;
            const safeName = displayName.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').slice(0, 36);
            svg += `\n<text x="${size / 2}" y="${size + 30}" font-family="sans-serif" font-size="20" font-weight="bold" fill="#111827" text-anchor="middle">${safeName}</text>`;

            let subText = code;
            if (prodCode && prodCode !== '—') subText += ` • ${prodCode}`;
            const safeSub = subText.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            svg += `\n<text x="${size / 2}" y="${size + 54}" font-family="sans-serif" font-size="14" font-weight="500" fill="#4b5563" text-anchor="middle">${safeSub}</text>`;
        }

        svg += `\n</svg>`;
        return svg;
    }

    window.downloadQr = function (size, format = 'png') {
        const includeText = document.getElementById('editIncludeTextSwitch') ? document.getElementById('editIncludeTextSwitch').checked : false;
        const fileName = getQrFileName(prodName, qrCode, format);

        if (format === 'svg') {
            const svgContent = generateSvgString(qrUrl, qrCode, prodName, prodCode, true, includeText);
            if (!svgContent) return;
            const blob = new Blob([svgContent], { type: "image/svg+xml;charset=utf-8" });
            const a = document.createElement("a");
            a.download = fileName;
            a.href = URL.createObjectURL(blob);
            a.click();
            setTimeout(() => URL.revokeObjectURL(a.href), 3000);
            return;
        }

        renderCustomCanvas(qrUrl, qrCode, prodName, prodCode, size, format, includeText, function (finalCanvas) {
            if (!finalCanvas) return;
            const mimeType = format === 'jpg' ? 'image/jpeg' : 'image/png';
            const a = document.createElement("a");
            a.download = fileName;
            a.href = finalCanvas.toDataURL(mimeType, 0.95);
            a.click();
        });
    };
});
</script>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
