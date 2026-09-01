<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    try {
        $pdo = getDBConnection();
        $pdo->beginTransaction();

        $id = $_POST['id'] ?? null;
        if (!$id) {
            throw new Exception("Invalid ID.");
        }

        // 1. Fetch Current Product State (to verify type)
        $currentProduct = fetchOne("SELECT variant_type FROM catalogue_products WHERE id = ?", [$id]);
        if (!$currentProduct) {
            throw new Exception("Product not found.");
        }
        $variant_type = $currentProduct['variant_type'];

        // 2. Common Inputs
        $series_id = (int)($_POST['series_id'] ?? 0);
        $name = sanitize($_POST['name'] ?? '');
        $specifications = $_POST['specifications'] ?? ''; // Allow HTML for rich text
        $display_order = (int)($_POST['display_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $is_new_arrival = isset($_POST['is_new_arrival']) ? 1 : 0;
        $colour_label_raw = $_POST['colour_label'] ?? 'colour';
        $colour_label = in_array($colour_label_raw, ['colour', 'finish']) ? $colour_label_raw : 'colour';
        
        $delete_images = $_POST['delete_images'] ?? []; // Main gallery del
        $primary_image_id = $_POST['primary_image'] ?? null;
        $delete_variants = $_POST['delete_variants'] ?? [];

        if (empty($series_id) || empty($name)) {
            throw new Exception("Series and Product Name are required.");
        }

        // 3. Prepare Main Product Data
        $code = null;
        $price = 0;
        $dimensions = sanitize($_POST['dimensions'] ?? '');

        if ($variant_type === 'none') {
            $code = dashCode(sanitize($_POST['code'] ?? ''));
            $price = !empty($_POST['price']) ? (int)$_POST['price'] : 0;
            $priceZ2 = !empty($_POST['price_zone2']) ? (int)$_POST['price_zone2'] : null;

            if (empty($code)) {
                 throw new Exception("Product Code is required for Simple Products.");
            }

            // Check Code Unique
            $stmtCheck = $pdo->prepare("SELECT id FROM catalogue_products WHERE code = ? AND series_id = ? AND id != ? LIMIT 1");
            $stmtCheck->execute([$code, $series_id, $id]);
            if ($stmtCheck->fetchColumn()) {
                throw new Exception("Product Code '$code' already exists in this series.");
            }
        } else {
             // For variant products, we can keep the existing code/price or set null.
             // Since form fields are hidden, let's just not update them (or set to NULL if schema allows).
             // We'll just update the common fields.
        }

        // 4. Update Main Product
        // HSN code is settable on every product (simple + size-parent + colour-parent).
        // Variants inherit from parent unless their own hsn_code is explicitly set.
        $hsn_code = trim($_POST['hsn_code'] ?? '') ?: null;

        // Fetch current video state
        $oldProduct = fetchOne("SELECT video_url FROM catalogue_products WHERE id = ?", [$id]);
        $oldVideoUrl = $oldProduct['video_url'] ?? null;
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

        if ($variant_type === 'none') {
             $sql = "UPDATE catalogue_products
                     SET series_id=?, name=?, code=?, hsn_code=?, price=?, price_zone2=?, dimensions=?, specifications=?, display_order=?, is_new_arrival=?, is_active=?, video_url=?
                     WHERE id=?";
             $params = [$series_id, $name, $code, $hsn_code, $price, $priceZ2, $dimensions, $specifications, $display_order, $is_new_arrival, $is_active, $newVideoVal, $id];
        } elseif ($variant_type === 'size') {
             $sql = "UPDATE catalogue_products
                     SET series_id=?, name=?, hsn_code=?, specifications=?, display_order=?, is_new_arrival=?, is_active=?, video_url=?
                     WHERE id=?";
             $params = [$series_id, $name, $hsn_code, $specifications, $display_order, $is_new_arrival, $is_active, $newVideoVal, $id];
        } else {
             // color variants — keep dimensions + colour_label
             $sql = "UPDATE catalogue_products
                     SET series_id=?, name=?, hsn_code=?, dimensions=?, specifications=?, display_order=?, is_new_arrival=?, is_active=?, colour_label=?, video_url=?
                     WHERE id=?";
             $params = [$series_id, $name, $hsn_code, $dimensions, $specifications, $display_order, $is_new_arrival, $is_active, $colour_label, $newVideoVal, $id];
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

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
                          execute("INSERT INTO catalogue_product_images (product_id, image_url, is_primary, display_order) VALUES (?, ?, 0, 99)", [$id, $uploadResult['filename']]);
                      }
                  }
             }
        }

        // 5c. Set Primary
        execute("UPDATE catalogue_product_images SET is_primary = 0 WHERE product_id = ?", [$id]);
        if ($primary_image_id && !in_array($primary_image_id, $delete_images)) {
            execute("UPDATE catalogue_product_images SET is_primary = 1 WHERE id = ? AND product_id = ?", [$primary_image_id, $id]);
        } else {
            $firstImg = fetchOne("SELECT id FROM catalogue_product_images WHERE product_id = ? ORDER BY id ASC LIMIT 1", [$id]);
            if ($firstImg) execute("UPDATE catalogue_product_images SET is_primary = 1 WHERE id = ?", [$firstImg['id']]);
        }

        // 6. Handle Variants
        if ($variant_type !== 'none') {
            // 6a. Delete Variants
            if (!empty($delete_variants)) {
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
            
            // If ALL variants are deleted and none added, that's invalid if we enforce checks.
            // But let's just process.
            
            foreach ($submittedVariants as $index => $variantData) {
                $vid = $variantData['id'] ?? null;
                
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
