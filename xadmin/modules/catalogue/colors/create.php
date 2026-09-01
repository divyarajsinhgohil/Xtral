<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

$pageTitle = "Add New Color";
$activePage = "catalogue_colors";

include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="list.php" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Library
            </a>
            <h2><i class="fas fa-plus-circle me-2"></i><?= $pageTitle ?></h2>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Color Details</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="create_process.php" enctype="multipart/form-data">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Color Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required placeholder="e.g., Matte Black">
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-block">Type <span class="text-danger">*</span></label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="type_solid" value="solid" checked onchange="toggleType()">
                                <label class="form-check-label" for="type_solid">Solid Color</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="type_texture" value="texture" onchange="toggleType()">
                                <label class="form-check-label" for="type_texture">Texture / Image</label>
                            </div>
                        </div>

                        <!-- Solid Color Section -->
                        <div class="mb-3" id="solid_section">
                            <label for="hex_code" class="form-label">Hex Code <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="color" class="form-control form-control-color" id="hex_picker" value="#000000" title="Choose your color">
                                <input type="text" class="form-control" id="hex_code" name="hex_code" value="#000000" pattern="^#+([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$" placeholder="#000000">
                            </div>
                        </div>

                        <!-- Texture Section -->
                        <div class="mb-3" id="texture_section" style="display:none;">
                            <label for="texture_image" class="form-label">Texture Image <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="texture_image" name="texture_image" accept="image/*">
                            <small class="text-muted">Recommended: Square image (e.g., 200x200px)</small>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Save Color</button>
                            <a href="list.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>

<script>
    // Sync Hex Picker and Text Input
    const hexPicker = document.getElementById('hex_picker');
    const hexInput = document.getElementById('hex_code');

    hexPicker.addEventListener('input', function() {
        hexInput.value = this.value;
    });

    hexInput.addEventListener('input', function() {
        if (/^#[0-9A-F]{6}$/i.test(this.value)) {
            hexPicker.value = this.value;
        }
    });

    function toggleType() {
        const isSolid = document.getElementById('type_solid').checked;
        const solidSection = document.getElementById('solid_section');
        const textureSection = document.getElementById('texture_section');
        const hexInput = document.getElementById('hex_code');
        const textureInput = document.getElementById('texture_image');

        if (isSolid) {
            solidSection.style.display = 'block';
            textureSection.style.display = 'none';
            hexInput.required = true;
            textureInput.required = false;
        } else {
            solidSection.style.display = 'none';
            textureSection.style.display = 'block';
            hexInput.required = false;
            textureInput.required = true;
        }
    }

    // Initialize
    toggleType();
</script>
