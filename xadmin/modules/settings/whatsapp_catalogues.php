<?php
/**
 * Website Catalogues — manage the PDF brochure list shown on the public
 * website's Catalogue page (xfront/catalogue.php), which reads from this
 * `whatsapp_catalogues` table via xadmin/api/webapi/catalogues.php.
 *
 * Files live at uploads/catalogue/pdfs/<pdf_filename> and
 * uploads/catalogue/pdfs/Thumb/<thumb_filename>.
 *
 * The active-row/name-length caps below are relaxed from their original
 * WhatsApp-bot-list values (this table isn't wired to a bot in Xtral) but
 * kept as sane sanity limits rather than removed outright.
 */

$pageTitle  = "Website Catalogues";
$activePage = "whatsapp_catalogues";
require_once '../../config/db.php';
requireRole(['super_admin']);

// ---------------------------------------------------------------------------
// Constants
// ---------------------------------------------------------------------------
const WC_MAX_PDF_MB        = 100;
const WC_MAX_THUMB_MB      = 5;
const WC_MAX_ACTIVE_ROWS   = 50;                    // sanity cap, not a hard platform limit
const WC_MAX_NAME_CHARS    = 60;                    // fits comfortably as a website card title
const WC_PDF_MIMES         = ['application/pdf'];
const WC_THUMB_MIMES       = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];

$pdfDir   = dirname(dirname(__DIR__)) . '/uploads/catalogue/pdfs/';
$thumbDir = $pdfDir . 'Thumb/';

if (!is_dir($pdfDir))   mkdir($pdfDir,   0755, true);
if (!is_dir($thumbDir)) mkdir($thumbDir, 0755, true);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Convert a category name into a safe filename base (no extension).
 * Preserves spaces and dashes for readability; strips characters that break
 * filesystems or URLs. Falls back to 'catalogue' if name reduces to empty.
 *
 * Example: "X-Tral Products / 2026" → "X-Tral Products 2026"
 */
function wc_filename_from_name(string $name): string {
    $clean = trim($name);
    // Strip filesystem-dangerous + control chars (cross-platform safe).
    $clean = preg_replace('/[\/\\\\:*?"<>|\x00-\x1F]/u', '', $clean);
    // Collapse internal whitespace.
    $clean = preg_replace('/\s+/', ' ', $clean);
    // Trim spaces + trailing dots (Windows hates trailing dots).
    $clean = trim($clean, " .");
    if ($clean === '') $clean = 'catalogue';
    // Cap at 80 chars so suffix `_<id>.pdf` always fits within filesystem
    // limits even on legacy systems.
    return mb_substr($clean, 0, 80);
}

/**
 * Resolve a disk-collision-safe filename. If another row in the table already
 * owns `<base>.<ext>` (in the specified column), append `_<id>` to make this
 * one unique. We check the DB rather than the filesystem so legacy/orphan
 * files in the folder don't accidentally trigger suffixing.
 */
function wc_unique_filename(string $base, string $ext, int $rowId, string $column): string {
    $candidate = $base . '.' . $ext;
    if (!in_array($column, ['pdf_filename', 'thumb_filename'], true)) {
        // Defensive: column name is interpolated, so whitelist guard.
        $column = 'pdf_filename';
    }
    $clash = fetchOne(
        "SELECT id FROM whatsapp_catalogues WHERE {$column} = ? AND id <> ? LIMIT 1",
        [$candidate, $rowId]
    );
    return $clash ? ($base . '_' . $rowId . '.' . $ext) : $candidate;
}

/** Validate uploaded file. Returns ['ok'=>bool, 'error'=>?string]. */
function wc_validate_upload(array $f, array $allowedMimes, int $maxBytes, string $label): array {
    if ($f['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'error' => "$label is required."];
    }
    if ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE) {
        $serverMax = (int)(ini_get('upload_max_filesize'));
        return ['ok' => false, 'error' => "$label too large for server (PHP upload_max_filesize is currently {$serverMax}M; need at least " . round($maxBytes / 1024 / 1024) . "M)."];
    }
    if ($f['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => "$label upload failed (error code {$f['error']})."];
    }
    if ($f['size'] > $maxBytes) {
        return ['ok' => false, 'error' => "$label exceeds " . round($maxBytes / 1024 / 1024) . " MB limit (file was " . round($f['size'] / 1024 / 1024, 1) . " MB)."];
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $f['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mime, $allowedMimes, true)) {
        return ['ok' => false, 'error' => "$label has invalid type ($mime). Allowed: " . implode(', ', $allowedMimes) . '.'];
    }
    return ['ok' => true, 'error' => null];
}

// ---------------------------------------------------------------------------
// POST handlers
// ---------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // -- AJAX endpoints ----------------------------------------------------
    if (isset($_POST['ajax_action'])) {
        header('Content-Type: application/json');

        if ($_POST['ajax_action'] === 'toggle_status') {
            $id  = (int)($_POST['id'] ?? 0);
            $row = fetchOne("SELECT is_active FROM whatsapp_catalogues WHERE id = ?", [$id]);
            if (!$row) {
                echo json_encode(['success' => false, 'message' => 'Not found']); exit;
            }
            $new = $row['is_active'] ? 0 : 1;

            if ($new === 1) {
                $activeCount = fetchOne("SELECT COUNT(*) AS c FROM whatsapp_catalogues WHERE is_active = 1")['c'] ?? 0;
                if ($activeCount >= WC_MAX_ACTIVE_ROWS) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Maximum ' . WC_MAX_ACTIVE_ROWS . ' active catalogues allowed. Deactivate one first.'
                    ]); exit;
                }
            }

            execute("UPDATE whatsapp_catalogues SET is_active = ? WHERE id = ?", [$new, $id]);
            echo json_encode(['success' => true, 'is_active' => $new]); exit;
        }

        if ($_POST['ajax_action'] === 'reorder') {
            $order = json_decode($_POST['order'] ?? '[]', true);
            if (is_array($order)) {
                foreach ($order as $pos => $id) {
                    execute("UPDATE whatsapp_catalogues SET display_order = ? WHERE id = ?", [$pos + 1, (int)$id]);
                }
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid order data']);
            }
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Unknown action']);
        exit;
    }

    // -- Save / Update -----------------------------------------------------
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id   = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $name = trim($_POST['name'] ?? '');

        if ($name === '') {
            $_SESSION['error'] = 'Name is required.';
            header('Location: whatsapp_catalogues.php'); exit;
        }
        if (mb_strlen($name) > WC_MAX_NAME_CHARS) {
            $_SESSION['error'] = 'Name must be ' . WC_MAX_NAME_CHARS . ' characters or fewer. Yours is ' . mb_strlen($name) . '.';
            header('Location: whatsapp_catalogues.php'); exit;
        }

        $existing = $id ? fetchOne("SELECT * FROM whatsapp_catalogues WHERE id = ?", [$id]) : null;
        if ($id && !$existing) {
            $_SESSION['error'] = 'Catalogue not found.';
            header('Location: whatsapp_catalogues.php'); exit;
        }

        // Pre-validate uploads BEFORE touching the DB, so a bad file doesn't
        // leave a half-created row behind on INSERT.
        $hasPdfUpload   = !empty($_FILES['pdf']['name']);
        $hasThumbUpload = !empty($_FILES['thumb']['name']);

        if (!$id && !$hasPdfUpload) {
            $_SESSION['error'] = 'PDF is required for a new catalogue.';
            header('Location: whatsapp_catalogues.php'); exit;
        }
        if ($hasPdfUpload) {
            $check = wc_validate_upload($_FILES['pdf'], WC_PDF_MIMES, WC_MAX_PDF_MB * 1024 * 1024, 'PDF');
            if (!$check['ok']) { $_SESSION['error'] = $check['error']; header('Location: whatsapp_catalogues.php'); exit; }
        }
        if ($hasThumbUpload) {
            $check = wc_validate_upload($_FILES['thumb'], WC_THUMB_MIMES, WC_MAX_THUMB_MB * 1024 * 1024, 'Thumbnail');
            if (!$check['ok']) { $_SESSION['error'] = $check['error']; header('Location: whatsapp_catalogues.php'); exit; }
        }

        // Filename strategy: disk filename = category name (e.g. "X-Tral
        // Products.pdf"). Same name flows through to the WhatsApp document
        // label AND the website's download attribute — so user sees one
        // consistent filename everywhere. Collisions (two catalogues with
        // the same name) get an `_<id>` suffix to disambiguate; rare in
        // practice and resolves on rename.

        $pdfBase = wc_filename_from_name($name);

        // ---- INSERT path ----
        // Insert placeholder row to get an ID (needed for collision suffix),
        // then move files into final names, then UPDATE with real filenames.
        // If any file move fails after INSERT, we roll back the row so
        // there's no orphan DB entry.
        if (!$id) {
            $activeCount = fetchOne("SELECT COUNT(*) AS c FROM whatsapp_catalogues WHERE is_active = 1")['c'] ?? 0;
            $isActive    = $activeCount < WC_MAX_ACTIVE_ROWS ? 1 : 0;
            $maxOrder    = fetchOne("SELECT MAX(display_order) AS m FROM whatsapp_catalogues")['m'] ?? 0;
            $newId = insert(
                "INSERT INTO whatsapp_catalogues (name, pdf_filename, thumb_filename, is_active, display_order)
                 VALUES (?, '__pending__', NULL, ?, ?)",
                [$name, $isActive, $maxOrder + 1]
            );

            $pdfTarget   = wc_unique_filename($pdfBase, 'pdf', (int)$newId, 'pdf_filename');
            $thumbTarget = null;

            if (!move_uploaded_file($_FILES['pdf']['tmp_name'], $pdfDir . $pdfTarget)) {
                execute("DELETE FROM whatsapp_catalogues WHERE id = ?", [$newId]);
                $_SESSION['error'] = 'Failed to save PDF.';
                header('Location: whatsapp_catalogues.php'); exit;
            }
            if ($hasThumbUpload) {
                $ext         = strtolower(pathinfo($_FILES['thumb']['name'], PATHINFO_EXTENSION));
                $thumbTarget = wc_unique_filename($pdfBase, $ext, (int)$newId, 'thumb_filename');
                if (!move_uploaded_file($_FILES['thumb']['tmp_name'], $thumbDir . $thumbTarget)) {
                    @unlink($pdfDir . $pdfTarget);
                    execute("DELETE FROM whatsapp_catalogues WHERE id = ?", [$newId]);
                    $_SESSION['error'] = 'Failed to save thumbnail.';
                    header('Location: whatsapp_catalogues.php'); exit;
                }
            }
            execute(
                "UPDATE whatsapp_catalogues SET pdf_filename = ?, thumb_filename = ? WHERE id = ?",
                [$pdfTarget, $thumbTarget, $newId]
            );
            $_SESSION['success'] = $isActive
                ? 'Catalogue added.'
                : 'Catalogue added but saved as Inactive (already at ' . WC_MAX_ACTIVE_ROWS . ' active). Deactivate another to enable.';
            logInfo("Catalogue '{$name}' created by " . getAdminUsername() . " (id={$newId})");
            header('Location: whatsapp_catalogues.php'); exit;
        }

        // ---- UPDATE path ----
        // Three independent things can change here:
        //   (a) name      → rename existing file(s) on disk to match
        //   (b) PDF       → overwrite at the (possibly new) name, delete old
        //                   file at old name if name also changed
        //   (c) thumb     → same as PDF, plus ext-change handling
        // The order of operations matters — we want to do as little disk I/O
        // as possible and keep the row+disk consistent on failure.
        $pdfFilename   = $existing['pdf_filename'];
        $thumbFilename = $existing['thumb_filename'];

        // --- PDF ---
        $desiredPdf = wc_unique_filename($pdfBase, 'pdf', $id, 'pdf_filename');
        if ($hasPdfUpload) {
            if (!move_uploaded_file($_FILES['pdf']['tmp_name'], $pdfDir . $desiredPdf)) {
                $_SESSION['error'] = 'Failed to save PDF.';
                header('Location: whatsapp_catalogues.php'); exit;
            }
            // Old file at a different name → delete (orphan otherwise).
            if ($pdfFilename && $pdfFilename !== $desiredPdf && file_exists($pdfDir . $pdfFilename)) {
                @unlink($pdfDir . $pdfFilename);
            }
            $pdfFilename = $desiredPdf;
        } elseif ($pdfFilename && $pdfFilename !== $desiredPdf) {
            // No new PDF uploaded, but the on-disk name doesn't match the
            // expected convention (either the category was renamed, OR the
            // row was created under an older naming scheme like cat_<id>.pdf).
            // Rename in place so disk + download names stay in sync. Failures
            // (missing file, permission denied) leave the DB pointer alone —
            // better a working old link than a 404.
            $oldPath = $pdfDir . $pdfFilename;
            $newPath = $pdfDir . $desiredPdf;
            if (file_exists($oldPath) && @rename($oldPath, $newPath)) {
                $pdfFilename = $desiredPdf;
            }
        }

        // --- Thumbnail ---
        if ($hasThumbUpload) {
            $ext         = strtolower(pathinfo($_FILES['thumb']['name'], PATHINFO_EXTENSION));
            $desiredThumb = wc_unique_filename($pdfBase, $ext, $id, 'thumb_filename');
            if ($thumbFilename && $thumbFilename !== $desiredThumb && file_exists($thumbDir . $thumbFilename)) {
                @unlink($thumbDir . $thumbFilename);
            }
            if (!move_uploaded_file($_FILES['thumb']['tmp_name'], $thumbDir . $desiredThumb)) {
                $_SESSION['error'] = 'Failed to save thumbnail.';
                header('Location: whatsapp_catalogues.php'); exit;
            }
            $thumbFilename = $desiredThumb;
        } elseif ($thumbFilename) {
            // Same rename-to-match-convention logic as PDF, preserving the
            // existing thumb's extension (jpg/png/webp).
            $oldExt       = strtolower(pathinfo($thumbFilename, PATHINFO_EXTENSION));
            $desiredThumb = wc_unique_filename($pdfBase, $oldExt, $id, 'thumb_filename');
            if ($thumbFilename !== $desiredThumb) {
                $oldPath = $thumbDir . $thumbFilename;
                $newPath = $thumbDir . $desiredThumb;
                if (file_exists($oldPath) && @rename($oldPath, $newPath)) {
                    $thumbFilename = $desiredThumb;
                }
            }
        }

        execute(
            "UPDATE whatsapp_catalogues
                SET name = ?, pdf_filename = ?, thumb_filename = ?
              WHERE id = ?",
            [$name, $pdfFilename, $thumbFilename, $id]
        );
        $_SESSION['success'] = 'Catalogue updated.';
        logInfo("Catalogue #{$id} updated by " . getAdminUsername());
        header('Location: whatsapp_catalogues.php'); exit;
    }

    if ($action === 'delete') {
        $id  = (int)($_POST['id'] ?? 0);
        $row = fetchOne("SELECT pdf_filename, thumb_filename FROM whatsapp_catalogues WHERE id = ?", [$id]);
        if ($row) {
            execute("DELETE FROM whatsapp_catalogues WHERE id = ?", [$id]);
            // Delete files from disk (admin chose hard-delete; soft-delete is
            // the toggle-Inactive flow which keeps files alive).
            if ($row['pdf_filename'] && file_exists($pdfDir . $row['pdf_filename'])) {
                @unlink($pdfDir . $row['pdf_filename']);
            }
            if ($row['thumb_filename'] && file_exists($thumbDir . $row['thumb_filename'])) {
                @unlink($thumbDir . $row['thumb_filename']);
            }
            $_SESSION['success'] = 'Catalogue deleted.';
            logInfo("Catalogue #{$id} deleted by " . getAdminUsername());
        } else {
            $_SESSION['error'] = 'Catalogue not found.';
        }
        header('Location: whatsapp_catalogues.php'); exit;
    }
}

// ---------------------------------------------------------------------------
// Load list
// ---------------------------------------------------------------------------
$catalogues = fetchAll("SELECT * FROM whatsapp_catalogues ORDER BY display_order ASC, id ASC");

// Detect server's effective upload limit so we can warn the user up-front if
// it's lower than our 100 MB target.
$phpUploadMax = (int)(ini_get('upload_max_filesize'));
$phpPostMax   = (int)(ini_get('post_max_size'));
$effectiveMax = min($phpUploadMax, $phpPostMax);

require_once '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 text-gray-800"><i class="fas fa-file-pdf text-danger me-2"></i>Website Catalogues</h1>
                <p class="text-muted mb-0">
                    PDF brochures shown on the public website's <a href="/Xtral/xfront/catalogue.php" target="_blank">Catalogue page</a>.
                </p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#catalogueModal" onclick="wcResetForm()">
                <i class="fas fa-plus me-1"></i> Add New Catalogue
            </button>
        </div>
    </div>

    <?php if ($effectiveMax < WC_MAX_PDF_MB): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Server upload limit is <?= $effectiveMax ?> MB</strong> but PDFs can be up to <?= WC_MAX_PDF_MB ?> MB.
            PDFs larger than <?= $effectiveMax ?> MB will be rejected by PHP before they reach this page.
            Ask your hosting admin to raise <code>upload_max_filesize</code> and <code>post_max_size</code> in <code>.user.ini</code> or php.ini to <code><?= WC_MAX_PDF_MB ?>M</code>.
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-2 small text-muted">
                <span>
                    <i class="fas fa-info-circle me-1"></i>
                    Drag the <i class="fas fa-grip-vertical mx-1"></i> handle to reorder.
                    Toggle Active to show/hide on the website.
                </span>
                <span>
                    Active: <strong><?= count(array_filter($catalogues, fn($c) => $c['is_active'])) ?></strong> / <?= WC_MAX_ACTIVE_ROWS ?>
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="catalogueTable">
                    <thead>
                        <tr>
                            <th style="width:40px;"></th>
                            <th style="width:80px;">Thumb</th>
                            <th>Name</th>
                            <th>PDF Filename</th>
                            <th style="width:120px;">Active</th>
                            <th class="text-nowrap" style="width:160px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="catalogueList">
                        <?php foreach ($catalogues as $row):
                            $pdfUrl   = BASE_URL . '/uploads/catalogue/pdfs/' . rawurlencode($row['pdf_filename']);
                            $thumbUrl = $row['thumb_filename']
                                ? BASE_URL . '/uploads/catalogue/pdfs/Thumb/' . rawurlencode($row['thumb_filename'])
                                : '';
                        ?>
                        <tr data-id="<?= $row['id'] ?>">
                            <td class="text-center text-muted" style="cursor:grab;">
                                <i class="fas fa-grip-vertical"></i>
                            </td>
                            <td>
                                <?php if ($thumbUrl): ?>
                                    <img src="<?= htmlspecialchars($thumbUrl) ?>" alt="thumb"
                                         style="width:60px;height:60px;object-fit:cover;border:1px solid #eee;border-radius:4px;">
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($row['name']) ?></strong>
                                <?php if (mb_strlen($row['name']) > WC_MAX_NAME_CHARS): ?>
                                    <span class="badge bg-danger ms-1" title="Long name — may wrap on the website card">!</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= htmlspecialchars($pdfUrl) ?>" target="_blank" class="small">
                                    <i class="fas fa-file-pdf text-danger me-1"></i><?= htmlspecialchars($row['pdf_filename']) ?>
                                </a>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-<?= $row['is_active'] ? 'success' : 'secondary' ?> toggle-active"
                                        data-id="<?= $row['id'] ?>">
                                    <?= $row['is_active'] ? 'Active' : 'Inactive' ?>
                                </button>
                            </td>
                            <td class="text-nowrap">
                                <button class="btn btn-sm btn-info"
                                        onclick='wcEdit(<?= json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger"
                                        onclick="wcConfirmDelete(<?= $row['id'] ?>, <?= htmlspecialchars(json_encode($row['name']), ENT_QUOTES) ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($catalogues)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No catalogues yet. Add one to get started.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit / Create Modal -->
<div class="modal fade" id="catalogueModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data" id="catalogueForm">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" id="wc_id">

                <div class="modal-header">
                    <h5 class="modal-title" id="wcModalTitle">Add New Catalogue</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">
                                Name <span class="text-danger">*</span>
                                <small class="text-muted">(max <?= WC_MAX_NAME_CHARS ?> chars)</small>
                            </label>
                            <input type="text" name="name" id="wc_name" class="form-control"
                                   required maxlength="<?= WC_MAX_NAME_CHARS ?>"
                                   oninput="wcCountName()">
                            <small class="text-muted"><span id="wcNameCount">0</span> / <?= WC_MAX_NAME_CHARS ?></small>
                        </div>

                        <div class="col-md-7">
                            <label class="form-label">
                                PDF <span class="text-danger wc-pdf-required">*</span>
                                <small class="text-muted">(max <?= WC_MAX_PDF_MB ?> MB)</small>
                            </label>
                            <input type="file" name="pdf" id="wc_pdf" class="form-control" accept="application/pdf">
                            <small class="text-muted" id="wc_pdf_existing"></small>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label">
                                Thumbnail <small class="text-muted">(optional, max <?= WC_MAX_THUMB_MB ?> MB)</small>
                            </label>
                            <input type="file" name="thumb" id="wc_thumb" class="form-control"
                                   accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted" id="wc_thumb_existing"></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Catalogue</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteCatalogueModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Delete Catalogue</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to permanently delete <strong id="deleteCatalogueName"></strong>?</p>
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    The PDF and thumbnail files will also be removed from disk. If you only want to hide it temporarily, use the Active toggle instead.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" class="d-inline" id="deleteCatalogueForm">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteCatalogueId">
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt me-1"></i>Delete Catalogue</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Sortable.js for drag-reorder (already used by company_info banners) -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
function wcResetForm() {
    document.getElementById('wcModalTitle').innerText = 'Add New Catalogue';
    document.getElementById('wc_id').value = '';
    document.getElementById('wc_name').value = '';
    document.getElementById('wc_pdf').value = '';
    document.getElementById('wc_thumb').value = '';
    document.getElementById('wc_pdf_existing').innerHTML = '';
    document.getElementById('wc_thumb_existing').innerHTML = '';
    document.querySelector('.wc-pdf-required').style.display = '';
    wcCountName();
}

function wcEdit(row) {
    document.getElementById('wcModalTitle').innerText = 'Edit Catalogue';
    document.getElementById('wc_id').value = row.id;
    document.getElementById('wc_name').value = row.name;
    document.getElementById('wc_pdf').value = '';
    document.getElementById('wc_thumb').value = '';
    document.getElementById('wc_pdf_existing').innerHTML =
        'Current: <code>' + (row.pdf_filename || '') + '</code> — leave empty to keep it.';
    document.getElementById('wc_thumb_existing').innerHTML = row.thumb_filename
        ? 'Current: <code>' + row.thumb_filename + '</code> — leave empty to keep it.'
        : '<em>No thumbnail set.</em>';
    document.querySelector('.wc-pdf-required').style.display = 'none'; // not required on edit
    wcCountName();
    new bootstrap.Modal(document.getElementById('catalogueModal')).show();
}

function wcConfirmDelete(id, name) {
    document.getElementById('deleteCatalogueId').value = id;
    document.getElementById('deleteCatalogueName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteCatalogueModal')).show();
}

function wcCountName() {
    const el = document.getElementById('wc_name');
    document.getElementById('wcNameCount').textContent = el ? el.value.length : 0;
}

// Drag-reorder
const listEl = document.getElementById('catalogueList');
if (listEl && typeof Sortable !== 'undefined') {
    Sortable.create(listEl, {
        handle: '.fa-grip-vertical',
        animation: 150,
        onEnd: function() {
            const order = [];
            document.querySelectorAll('#catalogueList tr[data-id]').forEach(tr => order.push(tr.dataset.id));
            const fd = new FormData();
            fd.append('ajax_action', 'reorder');
            fd.append('order', JSON.stringify(order));
            fetch('whatsapp_catalogues.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(res => { if (!res.success) alert(res.message || 'Reorder failed'); });
        }
    });
}

// Toggle active (AJAX so the page doesn't jump)
document.querySelectorAll('.toggle-active').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const fd = new FormData();
        fd.append('ajax_action', 'toggle_status');
        fd.append('id', id);
        fetch('whatsapp_catalogues.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    location.reload();
                } else {
                    alert(res.message || 'Toggle failed');
                }
            });
    });
});

// Initial char count
wcCountName();
</script>

<?php require_once '../../includes/footer.php'; ?>
