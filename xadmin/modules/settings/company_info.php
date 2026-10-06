<?php
/**
 * Company Info Settings
 * Company name, logo, contact details and address shown on the website.
 * Logo is served to the front site (header + footer) via api/webapi/site.php.
 */
require_once dirname(dirname(__DIR__)) . '/config/db.php';
requireRole(['super_admin']);

$pageTitle = 'Company Info';
$activePage = 'company_info';

$error = '';
$success = '';

// ============================================================
// HANDLE FORM SUBMISSION
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        updateSetting('company_name', trim($_POST['company_name'] ?? ''));
        updateSetting('company_toll_free', trim($_POST['company_toll_free'] ?? ''));
        updateSetting('company_whatsapp', trim($_POST['company_whatsapp'] ?? ''));
        updateSetting('company_email', trim($_POST['company_email'] ?? ''));
        updateSetting('company_website', trim($_POST['company_website'] ?? ''));
        updateSetting('company_address', $_POST['company_address'] ?? '');
        updateSetting('social_facebook', trim($_POST['social_facebook'] ?? ''));
        updateSetting('social_instagram', trim($_POST['social_instagram'] ?? ''));
        updateSetting('social_youtube', trim($_POST['social_youtube'] ?? ''));
        updateSetting('social_twitter', trim($_POST['social_twitter'] ?? ''));
        updateSetting('price_label_1', trim($_POST['price_label_1'] ?? 'Zone 1') ?: 'Zone 1');
        updateSetting('price_label_2', trim($_POST['price_label_2'] ?? 'Zone 2') ?: 'Zone 2');

        // Handle logo upload — one image, replaces the previous file
        if (!empty($_FILES['company_logo']['name'])) {
            if ($_FILES['company_logo']['error'] !== UPLOAD_ERR_OK) {
                $error = 'Logo upload failed. Please try again.';
            } else {
                $allowedTypes = [
                    'image/jpeg'    => 'jpg',
                    'image/png'     => 'png',
                    'image/webp'    => 'webp',
                    'image/svg+xml' => 'svg',
                ];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $_FILES['company_logo']['tmp_name']);
                finfo_close($finfo);

                if (!isset($allowedTypes[$mimeType])) {
                    $error = 'Invalid logo file type. Only JPG, PNG, WebP or SVG allowed.';
                } elseif ($_FILES['company_logo']['size'] > 5 * 1024 * 1024) {
                    $error = 'Logo exceeds the 5MB limit.';
                } else {
                    $uploadDir = dirname(dirname(__DIR__)) . '/uploads/settings/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                    $filename = 'logo_' . time() . '.' . $allowedTypes[$mimeType];
                    if (move_uploaded_file($_FILES['company_logo']['tmp_name'], $uploadDir . $filename)) {
                        // Remove the previous logo file so uploads/ doesn't pile up
                        $oldLogo = getSetting('company_logo');
                        if ($oldLogo && file_exists($uploadDir . $oldLogo)) {
                            unlink($uploadDir . $oldLogo);
                        }
                        updateSetting('company_logo', $filename);
                    } else {
                        $error = 'Failed to save the uploaded logo.';
                    }
                }
            }
        }

        if (empty($error)) {
            logInfo("Company info updated by " . getAdminUsername());
            $success = 'Company info saved successfully!';
        }
    } catch (Exception $e) {
        logError("Failed to save company info: " . $e->getMessage());
        $error = 'Failed to save company info.';
    }
}

// ============================================================
// LOAD CURRENT DATA
// ============================================================
$companyName    = getSetting('company_name') ?? '';
$companyLogo    = getSetting('company_logo') ?? '';
$tollFree       = getSetting('company_toll_free') ?? '';
$whatsapp       = getSetting('company_whatsapp') ?? '';
$companyEmail   = getSetting('company_email') ?? '';
$companyWebsite = getSetting('company_website') ?? '';
$companyAddress = getSetting('company_address') ?? '';
$socialFacebook  = getSetting('social_facebook') ?? '';
$socialInstagram = getSetting('social_instagram') ?? '';
$socialYoutube   = getSetting('social_youtube') ?? '';
$socialTwitter   = getSetting('social_twitter') ?? '';
$priceLabel1     = getPriceLabel1();
$priceLabel2     = getPriceLabel2();

$additionalCSS = '<link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">';

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-building me-2"></i>Company Info</h2>
                    <p class="text-muted mb-0">Company name, logo and contact details shown on the website</p>
                </div>
            </div>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" id="companyInfoForm">
        <div class="row">
            <div class="col-lg-8">
                <!-- Section 1: Company Name -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-building me-2"></i>Company Name</h5>
                    </div>
                    <div class="card-body">
                        <input type="text" class="form-control" id="company_name" name="company_name"
                               value="<?= htmlspecialchars($companyName) ?>"
                               placeholder="e.g. X-Tral">
                    </div>
                </div>

                <!-- Section 2: Company Logo -->
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-image me-2"></i>Company Logo</h5>
                        <small class="text-muted">Shown in website header &amp; footer • PNG, JPG, WebP, SVG</small>
                    </div>
                    <div class="card-body">
                        <?php if ($companyLogo): ?>
                            <div class="mb-3 d-flex align-items-center gap-4">
                                <div class="border rounded p-3 bg-white">
                                    <img src="<?= BASE_URL ?>/uploads/settings/<?= htmlspecialchars($companyLogo) ?>"
                                         alt="Company Logo" style="max-height: 60px; max-width: 240px;">
                                </div>
                                <div class="border rounded p-3 bg-dark">
                                    <img src="<?= BASE_URL ?>/uploads/settings/<?= htmlspecialchars($companyLogo) ?>"
                                         alt="Company Logo (dark preview)" style="max-height: 60px; max-width: 240px; filter: brightness(0) invert(1);">
                                </div>
                            </div>
                            <small class="text-muted d-block mb-3">Current logo — light preview (header) and dark preview (footer, shown in white).</small>
                        <?php else: ?>
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-image fs-3 d-block mb-2"></i>
                                <p class="mb-0">No logo uploaded yet</p>
                            </div>
                        <?php endif; ?>

                        <div class="border rounded p-3 bg-light">
                            <label for="company_logo" class="form-label mb-1 fw-bold">
                                <i class="fas fa-upload me-1"></i><?= $companyLogo ? 'Replace Logo' : 'Upload Logo' ?>
                            </label>
                            <input type="file" class="form-control" id="company_logo" name="company_logo"
                                   accept="image/jpeg,image/jpg,image/png,image/webp,image/svg+xml">
                            <small class="form-text text-muted">Transparent PNG or SVG recommended. Max 5MB. Uploading replaces the current logo.</small>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Contact Information -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-phone-alt me-2"></i>Contact Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="company_toll_free" class="form-label">Toll Free Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="text" class="form-control" id="company_toll_free" name="company_toll_free"
                                           value="<?= htmlspecialchars($tollFree) ?>"
                                           placeholder="1800-XXX-XXXX">
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="company_whatsapp" class="form-label">WhatsApp Number</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fab fa-whatsapp"></i></span>
                                    <input type="text" class="form-control" id="company_whatsapp" name="company_whatsapp"
                                           value="<?= htmlspecialchars($whatsapp) ?>"
                                           placeholder="90999 10204">
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="company_email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="company_email" name="company_email"
                                           value="<?= htmlspecialchars($companyEmail) ?>"
                                           placeholder="info@company.com">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="company_website" class="form-label">Website</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                    <input type="url" class="form-control" id="company_website" name="company_website"
                                           value="<?= htmlspecialchars($companyWebsite) ?>"
                                           placeholder="https://www.company.com">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3.5: Social Media Links -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-share-alt me-2"></i>Social Media Links</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="social_facebook" class="form-label">Facebook Link</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-primary"><i class="fab fa-facebook-f"></i></span>
                                    <input type="url" class="form-control" id="social_facebook" name="social_facebook"
                                           value="<?= htmlspecialchars($socialFacebook) ?>"
                                           placeholder="https://facebook.com/yourpage">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="social_instagram" class="form-label">Instagram Link</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-danger"><i class="fab fa-instagram"></i></span>
                                    <input type="url" class="form-control" id="social_instagram" name="social_instagram"
                                           value="<?= htmlspecialchars($socialInstagram) ?>"
                                           placeholder="https://instagram.com/yourprofile">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="social_youtube" class="form-label">YouTube Channel Link</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-danger"><i class="fab fa-youtube"></i></span>
                                    <input type="url" class="form-control" id="social_youtube" name="social_youtube"
                                           value="<?= htmlspecialchars($socialYoutube) ?>"
                                           placeholder="https://youtube.com/c/yourchannel">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="social_twitter" class="form-label">X (Twitter) Link</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-dark"><i class="fab fa-x-twitter"></i></span>
                                    <input type="url" class="form-control" id="social_twitter" name="social_twitter"
                                           value="<?= htmlspecialchars($socialTwitter) ?>"
                                           placeholder="https://x.com/yourprofile">
                                </div>
                            </div>
                        </div>
                        <div class="form-text text-muted">
                            <i class="fas fa-info-circle me-1"></i>Links left blank will automatically be hidden from the website footer.
                        </div>
                    </div>
                </div>

                <!-- Section: Dual Price Titles -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-tags me-2 text-primary"></i>Product Dual Pricing Titles</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Set custom names/titles for the two product prices (e.g., <strong>Zone 1</strong> &amp; <strong>Zone 2</strong>, <strong>Price 1</strong> &amp; <strong>Price 2</strong>, <strong>MRP</strong> &amp; <strong>Offer Price</strong>, <strong>White</strong> &amp; <strong>Colour</strong>, etc.). These names will display on product edit forms and the website.</p>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price_label_1" class="form-label">Price 1 Title / Name</label>
                                <input type="text" class="form-control" id="price_label_1" name="price_label_1"
                                       value="<?= htmlspecialchars($priceLabel1) ?>"
                                       placeholder="e.g., Zone 1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="price_label_2" class="form-label">Price 2 Title / Name</label>
                                <input type="text" class="form-control" id="price_label_2" name="price_label_2"
                                       value="<?= htmlspecialchars($priceLabel2) ?>"
                                       placeholder="e.g., Zone 2">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Address -->
                <div class="card mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Address</h5>
                    </div>
                    <div class="card-body">
                        <div id="address-editor" style="height: 200px;"><?= $companyAddress ?></div>
                        <input type="hidden" id="company_address" name="company_address">
                    </div>
                </div>

                <!-- Save Button -->
                <div class="d-flex justify-content-end mb-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Save Company Info
                    </button>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-globe me-2"></i>Website Usage</h6>
                    </div>
                    <div class="card-body">
                        <ul class="small text-muted mb-0">
                            <li><strong>Logo</strong> — shown in the website header and footer (white version in footer)</li>
                            <li><strong>Contact details</strong> — used on the Contact page</li>
                            <li><strong>Address</strong> — shown with formatted text</li>
                        </ul>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Logo Tips</h6>
                    </div>
                    <div class="card-body">
                        <ul class="small text-muted mb-0">
                            <li>Transparent <strong>PNG</strong> or <strong>SVG</strong> works best</li>
                            <li>Wide/horizontal logos fit the header nicely</li>
                            <li>Displayed ~48px tall — upload at least 2× that for sharpness</li>
                            <li>Max size: 5MB</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php
$additionalJS = <<<'JS'
<!-- Quill JS -->
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>

<script>
$(document).ready(function() {
    var addressQuill = new Quill('#address-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['clean']
            ]
        },
        placeholder: 'Enter address...'
    });

    // Sync Quill content to the hidden input on form submit
    $('#companyInfoForm').on('submit', function() {
        var addressHtml = addressQuill.root.innerHTML;
        if (addressHtml === '<p><br></p>') addressHtml = '';
        $('#company_address').val(addressHtml);
    });
});
</script>
JS;

include dirname(dirname(__DIR__)) . '/includes/footer.php';
?>
