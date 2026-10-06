<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();
require_once dirname(__DIR__, 3) . '/includes/catalogue_product_hierarchy.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    try {
        $pdo = getDBConnection();
        ensureCatalogueProductHierarchySchema($pdo);
        $pdo->beginTransaction();

        $id = $_POST['id'] ?? null;
        if (!$id) {
            throw new Exception("Invalid ID.");
        }

        // 1. Fetch Current Product State (to verify type)
        $currentProduct = fetchOne("SELECT * FROM catalogue_products WHERE id = ?", [$id]);
        if (!$currentProduct) {
            throw new Exception("Product not found.");
        }
        $oldVariantType = $currentProduct['variant_type'] ?? 'none';

        // Variant type from form
        $variant_type = sanitize($_POST['variant_type'] ?? $oldVariantType);
        if (!in_array($variant_type, ['none', 'size', 'color'])) {
            $variant_type = 'none';
        }

        // 2. Common Inputs
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
        $colour_label_raw = $_POST['colour_label'] ?? 'colour';
        $colour_label = in_array($colour_label_raw, ['colour', 'finish']) ? $colour_label_raw : 'colour';
        
        $delete_images = $_POST['delete_images'] ?? []; // Main gallery del
        $primary_image_id = $_POST['primary_image'] ?? null;
        $delete_variants = $_POST['delete_variants'] ?? [];

        // 2b. Validate Common & Resolve Classification
        if (empty($name)) {
            throw new Exception("Product Name is required.");
        }

        [$category_id, $sub_category_id, $series_id] = resolveCatalogueProductClassification(
            $pdo, $_POST['category_id'] ?? null, $_POST['sub_category_id'] ?? null, $series_id
        );

        // 3. Prepare Main Product Data
        $code = null;
        $price = 0;
        $priceZ2 = null;
        $dimensions = ($variant_type === 'size') ? null : sanitize($_POST['dimensions'] ?? '');

        $submittedCode = trim($_POST['code'] ?? '');

        if ($variant_type === 'none') {
            if ($submittedCode === '') {
                 throw new Exception("Product Code is required for Simple Products.");
            }
            $code = dashCode(sanitize($submittedCode));
            $price = !empty($_POST['price']) ? (int)$_POST['price'] : 0;
            $priceZ2 = !empty($_POST['price_zone2']) ? (int)$_POST['price_zone2'] : null;

            // Check Code Unique
            if ($series_id !== null) {
                $stmtCheck = $pdo->prepare("SELECT id FROM catalogue_products WHERE code = ? AND series_id = ? AND id != ? LIMIT 1");
                $stmtCheck->execute([$code, $series_id, $id]);
            } else {
                $stmtCheck = $pdo->prepare("SELECT id FROM catalogue_products WHERE code = ? AND category_id = ? AND series_id IS NULL AND id != ? LIMIT 1");
                $stmtCheck->execute([$code, $category_id, $id]);
            }
            if ($stmtCheck->fetchColumn()) {
                throw new Exception("Product Code '$code' already exists in this category/series.");
            }
        } else {
            // For variant products, keep existing code or update if user entered/changed it
            if ($submittedCode !== '') {
                $code = dashCode(sanitize($submittedCode));
                // Check Code Unique
                if ($series_id !== null) {
                    $stmtCheck = $pdo->prepare("SELECT id FROM catalogue_products WHERE code = ? AND series_id = ? AND id != ? LIMIT 1");
                    $stmtCheck->execute([$code, $series_id, $id]);
                } else {
                    $stmtCheck = $pdo->prepare("SELECT id FROM catalogue_products WHERE code = ? AND category_id = ? AND series_id IS NULL AND id != ? LIMIT 1");
                    $stmtCheck->execute([$code, $category_id, $id]);
                }
                if ($stmtCheck->fetchColumn()) {
                    throw new Exception("Product Code '$code' already exists in this category/series.");
                }
            } else {
                // Keep old code from DB if exists
                $code = !empty($currentProduct['code']) ? $currentProduct['code'] : null;
            }
            // Preserve existing price if any
            $price = (int)($currentProduct['price'] ?? 0);
            $priceZ2 = $currentProduct['price_zone2'] ?? null;
        }

        // 4. Update Main Product
        // HSN code is settable on every product (simple + size-parent + colour-parent).
        // Variants inherit from parent unless their own hsn_code is explicitly set.
        $hsn_code = trim($_POST['hsn_code'] ?? '') ?: null;

        // Fetch current video state
        $oldVideoUrl = $currentProduct['video_url'] ?? null;
        $isOldVideoLocal = $oldVideoUrl && !preg_match('#^https?://#i', $oldVideoUrl);

        $delete_video = isset($_POST['delete_video']) && $_POST['delete_video'] == '1';
        $newVideoVal = $oldVideoUrl;

        // Handle Video Upload or Link update
        if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
            $videoUploadResult = handleFileUpload($_FILES['video'], 'catalogue/products', ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime']);
            if ($videoUploadResult['success']) {
                $newVideoVal = $videoUploadResult['filename'];
                // Delete old local video since we are replacing it
                if ($isOldVideoLocal) {
                    deleteUploadedFile('catalogue/products/' . $oldVideoUrl);
                }
            } else {
                throw new Exception("Video upload failed: " . $videoUploadResult['error']);
            }
        } elseif (isset($_POST['video_url_link'])) {
            $videoLink = trim($_POST['video_url_link']);
            if ($videoLink !== '') {
                $newVideoVal = sanitize($videoLink);
                // If it changed, and old was local, delete old file
                if ($newVideoVal !== $oldVideoUrl && $isOldVideoLocal) {
                    deleteUploadedFile('catalogue/products/' . $oldVideoUrl);
                }
            } else {
                // If it was an external link, and now it is cleared, remove the video
                if ($oldVideoUrl && preg_match('#^https?://#i', $oldVideoUrl)) {
                    $newVideoVal = null;
                }
            }
        }

        // If explicitly deleted
        if ($delete_video) {
            $newVideoVal = null;
            if ($isOldVideoLocal) {
                deleteUploadedFile('catalogue/products/' . $oldVideoUrl);
            }
        }

        $price_label_1 = trim($_POST['price_label_1'] ?? '');
        $price_label_2 = trim($_POST['price_label_2'] ?? '');

        // Guaranteed storage via settings
        updateSetting('prod_price_label_1_' . $id, $price_label_1);
        updateSetting('prod_price_label_2_' . $id, $price_label_2);

        $valL1 = $price_label_1 !== '' ? $price_label_1 : null;
        $valL2 = $price_label_2 !== '' ? $price_label_2 : null;

        try {
            $sql = "UPDATE catalogue_products
                    SET category_id=?, sub_category_id=?, series_id=?, name=?, code=?, hsn_code=?, price=?, price_zone2=?, price_label_1=?, price_label_2=?, dimensions=?, specifications=?, display_order=?, is_new_arrival=?, is_active=?, variant_type=?, colour_label=?, video_url=?
                    WHERE id=?";
            $params = [$category_id, $sub_category_id, $series_id, $name, $code, $hsn_code, $price, $priceZ2, $valL1, $valL2, $dimensions, $specifications, $display_order, $is_new_arrival, $is_active, $variant_type, $colour_label, $newVideoVal, $id];
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        } catch (\Throwable $t) {
            // Fallback in case price_label columns don't exist yet
            $sql = "UPDATE catalogue_products
                    SET category_id=?, sub_category_id=?, series_id=?, name=?, code=?, hsn_code=?, price=?, price_zone2=?, dimensions=?, specifications=?, display_order=?, is_new_arrival=?, is_active=?, variant_type=?, colour_label=?, video_url=?
                    WHERE id=?";
            $params = [$category_id, $sub_category_id, $series_id, $name, $code, $hsn_code, $price, $priceZ2, $dimensions, $specifications, $display_order, $is_new_arrival, $is_active, $variant_type, $colour_label, $newVideoVal, $id];
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }

        // 5. Handle Gallery Images (Delete & Add)
        
        // 5a. Delete
        if (!empty($delete_images)) {
            foreach ($delete_images as $imgId) {
                $img = fetchOne("SELECT image_url FROM catalogue_product_images WHERE id = ? AND product_id = ?", [$imgId, $id]);
                if ($img) {
                    deleteUploadedFile('catalogue/products/' . $img['image_url']);
                    execute("DELETE FROM catalogue_product_images WHERE id = ?", [$imgId]);
                }
            }
        }

        // Calculate remaining quota to enforce a total of 5 images limit
        $existingCountRow = fetchOne("SELECT COUNT(*) as cnt FROM catalogue_product_images WHERE product_id = ?", [$id]);
        $existingCount = (int)($existingCountRow['cnt'] ?? 0);
        $remainingQuota = max(0, 5 - $existingCount);

        // Determine if primary image is chosen from newly uploaded files (e.g. "new_0", "new_1", ...)
        $newPrimaryIndex = null;
        if (is_string($primary_image_id) && strpos($primary_image_id, 'new_') === 0) {
            $newPrimaryIndex = (int)substr($primary_image_id, 4);
        }

        // Reset existing primary flags if a primary selection was made
        if ($newPrimaryIndex !== null || ($primary_image_id && is_numeric($primary_image_id))) {
            execute("UPDATE catalogue_product_images SET is_primary = 0 WHERE product_id = ?", [$id]);
        }

        $newPrimarySet = false;
        // 5b. Add
        if (isset($_FILES['images']) && $remainingQuota > 0) {
             $files = $_FILES['images'];
             $fileCount = min(count($files['name']), $remainingQuota);
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
                          $is_primary = ($newPrimaryIndex !== null && $i === $newPrimaryIndex) ? 1 : 0;
                          if ($is_primary) {
                              $newPrimarySet = true;
                          }
                          execute("INSERT INTO catalogue_product_images (product_id, image_url, is_primary, display_order) VALUES (?, ?, ?, 99)", [$id, $uploadResult['filename'], $is_primary]);
                      }
                  }
             }
        }

        // 5c. Set Primary
        if (!$newPrimarySet) {
            if ($primary_image_id && is_numeric($primary_image_id) && !in_array($primary_image_id, $delete_images)) {
                execute("UPDATE catalogue_product_images SET is_primary = 1 WHERE id = ? AND product_id = ?", [$primary_image_id, $id]);
            } else {
                $hasPrimary = fetchOne("SELECT id FROM catalogue_product_images WHERE product_id = ? AND is_primary = 1 LIMIT 1", [$id]);
                if (!$hasPrimary) {
                    $firstImg = fetchOne("SELECT id FROM catalogue_product_images WHERE product_id = ? ORDER BY id ASC LIMIT 1", [$id]);
                    if ($firstImg) execute("UPDATE catalogue_product_images SET is_primary = 1 WHERE id = ?", [$firstImg['id']]);
                }
            }
        }

        // 5d. Handle Image Display Reordering
        if (!empty($_POST['image_order']) && is_array($_POST['image_order'])) {
            foreach ($_POST['image_order'] as $displayOrder => $imgId) {
                if (is_numeric($imgId) && !in_array($imgId, $delete_images)) {
                    execute("UPDATE catalogue_product_images SET display_order = ? WHERE id = ? AND product_id = ?", [(int)$displayOrder + 1, (int)$imgId, $id]);
                }
            }
        }

        // 6. Handle Variants
        // If variant_type changed away from previous variant type (e.g. size -> color, color -> size, variant -> none):
        if ($oldVariantType !== $variant_type && $oldVariantType !== 'none') {
            // Delete all previous variants from old type and clean their uploaded images
            $oldVariants = fetchAll("SELECT id, image_url FROM catalogue_product_variants WHERE product_id = ?", [$id]);
            foreach ($oldVariants as $ov) {
                if (!empty($ov['image_url'])) {
                    deleteUploadedFile('catalogue/products/' . $ov['image_url']);
                }
            }
            execute("DELETE FROM catalogue_product_variants WHERE product_id = ?", [$id]);
        }

        if ($variant_type !== 'none') {
            // 6a. Delete Variants explicitly marked for deletion (only applicable if variant_type didn't change)
            if ($oldVariantType === $variant_type && !empty($delete_variants)) {
                foreach ($delete_variants as $vid) {
                    // Check ownership
                    $v = fetchOne("SELECT image_url FROM catalogue_product_variants WHERE id = ? AND product_id = ?", [$vid, $id]);
                    if ($v) {
                        if ($v['image_url']) deleteUploadedFile('catalogue/products/' . $v['image_url']);
                        execute("DELETE FROM catalogue_product_variants WHERE id = ?", [$vid]);
                    }
                }
            }

            // 6b. Insert/Update Variants
            $submittedVariants = $_POST['variants'][$variant_type] ?? [];
            if (empty($submittedVariants)) {
                throw new Exception("Please add at least one variant.");
            }
            
            foreach ($submittedVariants as $index => $variantData) {
                // If variant_type changed, all old variants were wiped, so everything is an INSERT
                $vid = ($oldVariantType === $variant_type && !empty($variantData['id'])) ? (int)$variantData['id'] : null;
                
                $attrValue = ''; 
                $colorId = null;

                if ($variant_type === 'size') {
                    $attrValue = sanitize($variantData['value'] ?? '');
                } elseif ($variant_type === 'color') {
                    $attrValue = sanitize($variantData['value'] ?? '');
                    $colorId = !empty($variantData['color_id']) ? (int)$variantData['color_id'] : null;
                }

                $vCode = dashCode(sanitize($variantData['code'] ?? ''));
                $vPrice = !empty($variantData['price']) ? (int)$variantData['price'] : 0;
                $vPriceZ2 = !empty($variantData['price_zone2']) ? (int)$variantData['price_zone2'] : null;
                $vName = sanitize($variantData['name'] ?? '');

                if (empty($attrValue) || empty($vCode)) {
                    throw new Exception("All variants must have a value (Size/Color) and Code.");
                }
                if ($variant_type === 'color' && empty($colorId)) {
                    throw new Exception("Color selection is required for color variants.");
                }

                // Image Upload
                $vImageUrl = null;
                $fileKey = "variant_images_{$variant_type}_{$index}"; 
                
                if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                     $uploadResult = handleFileUpload($_FILES[$fileKey], 'catalogue/products', ['image/jpeg', 'image/png', 'image/webp']);
                     if ($uploadResult['success']) {
                         $vImageUrl = $uploadResult['filename'];
                         
                         // If updating and new image, delete old
                         if ($vid) {
                             $oldV = fetchOne("SELECT image_url FROM catalogue_product_variants WHERE id = ?", [$vid]);
                             if ($oldV && $oldV['image_url']) {
                                 deleteUploadedFile('catalogue/products/' . $oldV['image_url']);
                             }
                         }
                     }
                }

                if ($vid) {
                    // UPDATE
                    $sqlV = "UPDATE catalogue_product_variants SET name=?, code=?, price=?, price_zone2=?, attribute_value=?, color_id=?, display_order=?";
                    $paramsV = [$vName, $vCode, $vPrice, $vPriceZ2, $attrValue, $colorId, $index];
                    
                    if ($vImageUrl) {
                        $sqlV .= ", image_url=?";
                        $paramsV[] = $vImageUrl;
                    } elseif (isset($variantData['delete_image']) && $variantData['delete_image'] == '1') {
                         // Explicit Removal
                         $oldV = fetchOne("SELECT image_url FROM catalogue_product_variants WHERE id = ?", [$vid]);
                         if ($oldV && $oldV['image_url']) {
                             deleteUploadedFile('catalogue/products/' . $oldV['image_url']);
                         }
                         $sqlV .= ", image_url=NULL";
                    }

                    $sqlV .= " WHERE id=? AND product_id=?";
                    $paramsV[] = $vid;
                    $paramsV[] = $id;
                    
                    $stmtV = $pdo->prepare($sqlV);
                    $stmtV->execute($paramsV);

                } else {
                    // INSERT
                    $vSql = "INSERT INTO catalogue_product_variants (product_id, name, code, price, price_zone2, attribute_value, color_id, image_url, display_order, is_active) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";
                    $vStmt = $pdo->prepare($vSql);
                    $vStmt->execute([$id, $vName, $vCode, $vPrice, $vPriceZ2, $attrValue, $colorId, $vImageUrl, $index]);
                }
            }
        }

        // 9. Update Features
        $stmtDelFeatures = $pdo->prepare("DELETE FROM catalogue_product_features WHERE product_id = ?");
        $stmtDelFeatures->execute([$id]);

        if (!empty($_POST['feature_ids']) && is_array($_POST['feature_ids'])) {
            $fSql = "INSERT INTO catalogue_product_features (product_id, feature_id) VALUES (?, ?)";
            $fStmt = $pdo->prepare($fSql);
            foreach ($_POST['feature_ids'] as $f_id) {
                if (!empty($f_id)) {
                    $fStmt->execute([$id, (int)$f_id]);
                }
            }
        }

        $pdo->commit();
        $_SESSION['success'] = "Product updated successfully!";
        header("Location: list.php");
        exit;

    } catch (Exception $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error'] = "Error: " . $e->getMessage();
        // Redirect back to edit with ID
        $redirectId = $_POST['id'] ?? '';
        if ($redirectId) {
            header("Location: edit.php?id=$redirectId");
        } else {
            header("Location: list.php");
        }
        exit;
    }
} else {
    header("Location: list.php");
    exit;
}
