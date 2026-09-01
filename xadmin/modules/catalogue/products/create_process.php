<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    try {
        $pdo = getDBConnection();
        $pdo->beginTransaction();

        // 1. Common Inputs
        $series_id = (int)($_POST['series_id'] ?? 0);
        $name = sanitize($_POST['name'] ?? '');
        $specifications = $_POST['specifications'] ?? ''; // Allow HTML for rich text
        $display_order = (int)($_POST['display_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $is_new_arrival = isset($_POST['is_new_arrival']) ? 1 : 0;
        $variant_type = $_POST['variant_type'] ?? 'none'; // none, size, color
        $colour_label_raw = $_POST['colour_label'] ?? 'colour';
        $colour_label = in_array($colour_label_raw, ['colour', 'finish']) ? $colour_label_raw : 'colour';

        // Strict Separation: Unset irrelevant data to prevent mixed-bag submissions
        if ($variant_type === 'none') {
            unset($_POST['variants']);
        } elseif ($variant_type === 'size') {
            $_POST['code'] = null;
            $_POST['price'] = 0;
            $_POST['price_zone2'] = 0;
            if (isset($_POST['variants']['color'])) unset($_POST['variants']['color']);
        } elseif ($variant_type === 'color') {
            $_POST['code'] = null;
            $_POST['price'] = 0;
            $_POST['price_zone2'] = 0;
            if (isset($_POST['variants']['size'])) unset($_POST['variants']['size']);
        }

        // 2. Validate Common
        if (empty($series_id) || empty($name)) {
            throw new Exception("Series and Product Name are required.");
        }

        // 3. Prepare Main Product Data
        $mainCode = null;
        $mainPrice = 0.00;
        $mainDimensions = ($variant_type === 'size') ? null : sanitize($_POST['dimensions'] ?? '');

        if ($variant_type === 'none') {
            $mainCode = dashCode(sanitize($_POST['code'] ?? ''));
            $mainPrice = !empty($_POST['price']) ? (int)$_POST['price'] : 0;

            if (empty($mainCode)) {
                throw new Exception("Product Code is required for Simple Products.");
            }
            
            // Check Main Code Uniqueness
            $stmtCheck = $pdo->prepare("SELECT id FROM catalogue_products WHERE code = ? AND series_id = ? LIMIT 1");
            $stmtCheck->execute([$mainCode, $series_id]);
            if ($stmtCheck->fetchColumn()) {
                throw new Exception("Product Code '$mainCode' already exists in this series.");
            }
        }

        // 4. Insert Main Product
        $price_zone2 = !empty($_POST['price_zone2']) ? (int)$_POST['price_zone2'] : null;
        $hsn_code    = trim($_POST['hsn_code'] ?? '') ?: null;

        // Handle Video Upload
        $videoUrl = null;
        if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
            $videoUploadResult = handleFileUpload($_FILES['video'], 'catalogue/products', ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime']);
            if ($videoUploadResult['success']) {
                $videoUrl = $videoUploadResult['filename'];
            } else {
                throw new Exception("Video upload failed: " . $videoUploadResult['error']);
            }
        } elseif (!empty($_POST['video_url_link'])) {
            $videoUrl = sanitize($_POST['video_url_link']);
        }

        $sql = "INSERT INTO catalogue_products (series_id, name, code, hsn_code, price, price_zone2, dimensions, specifications, display_order, is_new_arrival, is_active, variant_type, colour_label, video_url)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$series_id, $name, $mainCode, $hsn_code, $mainPrice, $price_zone2, $mainDimensions, $specifications, $display_order, $is_new_arrival, $is_active, $variant_type, $colour_label, $videoUrl]);
        $product_id = $pdo->lastInsertId();

        // 5. Handle Main Gallery Images
        if (isset($_FILES['images'])) {
            $files = $_FILES['images'];
            $fileCount = min(count($files['name']), 5); // Hard limit: maximum 5 total images
            
            for ($i = 0; $i < $fileCount; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error' => $files['error'][$i],
                        'size' => $files['size'][$i]
                    ];
                    
                    $uploadResult = handleFileUpload($file, 'catalogue/products', ['image/jpeg', 'image/png', 'image/webp']);

                    if ($uploadResult['success']) {
                        $is_primary = ($i === 0) ? 1 : 0;
                        $imgSql = "INSERT INTO catalogue_product_images (product_id, image_url, is_primary, display_order) VALUES (?, ?, ?, ?)";
                        $imgStmt = $pdo->prepare($imgSql);
                        $imgStmt->execute([$product_id, $uploadResult['filename'], $is_primary, $i]);
                    }
                }
            }
        }

        // 6. Handle Variants
        if ($variant_type !== 'none') {
            // Get variants array based on type
            $submittedVariants = $_POST['variants'][$variant_type] ?? [];
            
            if (empty($submittedVariants)) {
                throw new Exception("At least one variant is required for this product type.");
            }

            foreach ($submittedVariants as $index => $variantData) {
                // Determine Attribute Values
                $attrValue = ''; // Size or Color Name
                $colorId = null;

                if ($variant_type === 'size') {
                    $attrValue = sanitize($variantData['value'] ?? ''); // Size
                } elseif ($variant_type === 'color') {
                    $attrValue = sanitize($variantData['value'] ?? ''); // Color Name
                    $colorId = !empty($variantData['color_id']) ? (int)$variantData['color_id'] : null;
                }

                $vCode = dashCode(sanitize($variantData['code'] ?? ''));
                $vPrice = !empty($variantData['price']) ? (int)$variantData['price'] : 0;
                $vName = sanitize($variantData['name'] ?? '');

                // Validation
                if (empty($attrValue) || empty($vCode)) {
                     throw new Exception("Size/Color, Code and Price are required for all variants.");
                }
                if ($variant_type === 'color' && empty($colorId)) {
                     throw new Exception("Color selection is required for color variants.");
                }

                // Variant Image
                $vImageUrl = null;
                $fileKey = "variant_images_{$variant_type}_{$index}";
                if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = handleFileUpload($_FILES[$fileKey], 'catalogue/products', ['image/jpeg', 'image/png', 'image/webp']);
                    if ($uploadResult['success']) {
                        $vImageUrl = $uploadResult['filename'];
                    }
                }

                $vPriceZ2 = !empty($variantData['price_zone2']) ? (int)$variantData['price_zone2'] : null;

                // Insert Variant
                $vSql = "INSERT INTO catalogue_product_variants (product_id, name, code, price, price_zone2, attribute_value, color_id, image_url, display_order, is_active) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
                $vStmt = $pdo->prepare($vSql);
                $vStmt->execute([$product_id, $vName, $vCode, $vPrice, $vPriceZ2, $attrValue, $colorId, $vImageUrl, $index]);
            }
        }

        // 7. Handle Features
        if (!empty($_POST['feature_ids']) && is_array($_POST['feature_ids'])) {
            $fSql = "INSERT INTO catalogue_product_features (product_id, feature_id) VALUES (?, ?)";
            $fStmt = $pdo->prepare($fSql);
            foreach ($_POST['feature_ids'] as $f_id) {
                if (!empty($f_id)) {
                    $fStmt->execute([$product_id, (int)$f_id]);
                }
            }
        }

        $pdo->commit();
        $_SESSION['success'] = "Product '$name' created successfully!";
        header("Location: list.php");
        exit;

    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: create.php");
        exit;
    }
} else {
    header("Location: list.php");
    exit;
}
