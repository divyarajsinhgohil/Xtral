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
        if ($display_order <= 0) {
            $maxOrder = fetchOne("SELECT MAX(display_order) as max_order FROM catalogue_products");
            $display_order = ($maxOrder['max_order'] ?? 0) + 1;
        }
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

        // 2. Validate Common & Resolve Classification
        if (empty($name)) {
            throw new Exception("Product Name is required.");
        }

        $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $sub_category_id = !empty($_POST['sub_category_id']) ? (int)$_POST['sub_category_id'] : null;

        if ($series_id > 0) {
            $seriesInfo = fetchOne("SELECT category_id, sub_category_id FROM catalogue_series WHERE id = ?", [$series_id]);
            if ($seriesInfo) {
                $category_id = (int)$seriesInfo['category_id'];
                $sub_category_id = !empty($seriesInfo['sub_category_id']) ? (int)$seriesInfo['sub_category_id'] : $sub_category_id;
            }
        } else {
            $series_id = null;
            if (empty($category_id)) {
                throw new Exception("Please select at least a Main Category for this product.");
            }
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
            if ($series_id !== null) {
                $stmtCheck = $pdo->prepare("SELECT id FROM catalogue_products WHERE code = ? AND series_id = ? LIMIT 1");
                $stmtCheck->execute([$mainCode, $series_id]);
            } else {
                $stmtCheck = $pdo->prepare("SELECT id FROM catalogue_products WHERE code = ? AND category_id = ? AND series_id IS NULL LIMIT 1");
                $stmtCheck->execute([$mainCode, $category_id]);
            }
            if ($stmtCheck->fetchColumn()) {
                throw new Exception("Product Code '$mainCode' already exists in this category/series.");
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

        $price_label_1 = trim($_POST['price_label_1'] ?? '') ?: null;
        $price_label_2 = trim($_POST['price_label_2'] ?? '') ?: null;

        $valL1 = $price_label_1 !== '' ? $price_label_1 : null;
        $valL2 = $price_label_2 !== '' ? $price_label_2 : null;

        $sql = "INSERT INTO catalogue_products (category_id, sub_category_id, series_id, name, code, hsn_code, price, price_zone2, price_label_1, price_label_2, dimensions, specifications, display_order, is_new_arrival, is_active, variant_type, colour_label, video_url)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$category_id, $sub_category_id, $series_id, $name, $mainCode, $hsn_code, $mainPrice, $price_zone2, $valL1, $valL2, $mainDimensions, $specifications, $display_order, $is_new_arrival, $is_active, $variant_type, $colour_label, $videoUrl]);
        $product_id = $pdo->lastInsertId();

        if (!empty($price_label_1)) updateSetting('prod_price_label_1_' . $product_id, $price_label_1);
        if (!empty($price_label_2)) updateSetting('prod_price_label_2_' . $product_id, $price_label_2);

        // 5. Handle Main Gallery Images
        if (isset($_FILES['images'])) {
            $files = $_FILES['images'];
            $fileCount = min(count($files['name']), 5); // Hard limit: maximum 5 total images

            $primary_image = $_POST['primary_image'] ?? null;
            $newPrimaryIndex = 0;
            if (is_string($primary_image) && strpos($primary_image, 'new_') === 0) {
                $newPrimaryIndex = (int)substr($primary_image, 4);
            }
            
            $insertedCount = 0;
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
                        $is_primary = ($i === $newPrimaryIndex) ? 1 : 0;
                        $imgSql = "INSERT INTO catalogue_product_images (product_id, image_url, is_primary, display_order) VALUES (?, ?, ?, ?)";
                        $imgStmt = $pdo->prepare($imgSql);
                        $imgStmt->execute([$product_id, $uploadResult['filename'], $is_primary, $i]);
                        $insertedCount++;
                    }
                }
            }

            // Ensure at least one image is marked as primary
            if ($insertedCount > 0) {
                $hasPrimary = fetchOne("SELECT id FROM catalogue_product_images WHERE product_id = ? AND is_primary = 1 LIMIT 1", [$product_id]);
                if (!$hasPrimary) {
                    $firstImg = fetchOne("SELECT id FROM catalogue_product_images WHERE product_id = ? ORDER BY id ASC LIMIT 1", [$product_id]);
                    if ($firstImg) execute("UPDATE catalogue_product_images SET is_primary = 1 WHERE id = ?", [$firstImg['id']]);
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
