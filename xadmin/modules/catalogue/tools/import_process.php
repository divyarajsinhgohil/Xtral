<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

// --- CONFIG ---
ini_set('max_execution_time', 600);
ini_set('memory_limit', '512M');
ini_set('upload_max_filesize', '256M');
ini_set('post_max_size', '260M');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: import.php");
    exit;
}

// --- ZIP IMAGE EXTRACTION ---
$GLOBALS['zip_file_map'] = [];
$GLOBALS['zip_extract_dir'] = '';

$zipFile = $_FILES['image_zip'] ?? null;
if ($zipFile && $zipFile['error'] === UPLOAD_ERR_OK && !empty($zipFile['tmp_name'])) {
    $extractDir = dirname(__DIR__, 3) . '/uploads/catalogue/import_staging_' . time() . '_' . uniqid() . '/';
    $GLOBALS['zip_extract_dir'] = $extractDir;

    $zip = new ZipArchive();
    if ($zip->open($zipFile['tmp_name']) === TRUE) {
        $zip->extractTo($extractDir);
        $zip->close();

        // Build lookup map: scan all extracted files and index by path suffixes
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($extractDir, RecursiveDirectoryIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $fullPath = str_replace('\\', '/', $file->getPathname());
                $relativePath = str_replace('\\', '/', str_replace($extractDir, '', $file->getPathname()));

                // Store by basename
                $basename = basename($fullPath);
                if (!isset($GLOBALS['zip_file_map']['name:' . strtolower($basename)])) {
                    $GLOBALS['zip_file_map']['name:' . strtolower($basename)] = [];
                }
                $GLOBALS['zip_file_map']['name:' . strtolower($basename)][] = $fullPath;

                // Build path suffix keys (e.g., "PTMT/10.ABS SHOWER WITH ABS ARM/04.jpg")
                $parts = explode('/', $relativePath);
                for ($si = 0; $si < count($parts); $si++) {
                    $suffix = strtolower(implode('/', array_slice($parts, $si)));
                    $GLOBALS['zip_file_map']['path:' . $suffix] = $fullPath;
                }
            }
        }
    }
    else {
        $_SESSION['import_errors'] = ["Could not open ZIP file. Please ensure it's a valid .zip archive."];
        header("Location: import.php");
        exit;
    }
}

$simulate = isset($_POST['simulate']) ? true : false;
$productType = $_POST['product_type'] ?? 'simple';
$csvFile = $_FILES['csv_file'] ?? null;

if (!$csvFile || $csvFile['error'] !== UPLOAD_ERR_OK) {
    $_SESSION['import_errors'] = ["Please upload a valid CSV file."];
    header("Location: import.php");
    exit;
}

$handle = fopen($csvFile['tmp_name'], "r");
if ($handle === false) {
    $_SESSION['import_errors'] = ["Could not open CSV file."];
    header("Location: import.php");
    exit;
}

// 1. Get Headers & Map
$headers = fgetcsv($handle);
$map = [];
foreach ($headers as $i => $h) {
    $clean = trim(strtolower(preg_replace('/\(.*?\)/', '', $h)));
    $clean = preg_replace('/[^a-z0-9]/', '', $clean);
    $map[$clean] = $i;
}

// ============================================================
// SIMPLE PRODUCTS IMPORT
// ============================================================
if ($productType === 'simple') {

    // Validate Required Headers
    $requiredKeys = ['category', 'series', 'productname', 'productcode', 'hsncode', 'price', 'image1'];
    $missingKeys = [];
    foreach ($requiredKeys as $k) {
        if (!isset($map[$k]))
            $missingKeys[] = $k;
    }
    if (!empty($missingKeys)) {
        $_SESSION['import_errors'] = ["Missing required columns in CSV: " . implode(', ', $missingKeys) . ". Please use the template."];
        fclose($handle);
        header("Location: import.php");
        exit;
    }

    // Check optional columns
    $hasProductId = isset($map['productid']);
    $hasPriceZone2 = isset($map['pricezone2']);
    $hasSubCategory = isset($map['subcategory']);
    $hasDimensions = isset($map['dimensions']);
    $hasSpecifications = isset($map['specifications']);
    $hasDisplayOrder = isset($map['displayorder']);

    $rowNum = 1;
    $pdo = getDBConnection();
    $pdo->beginTransaction();

    try {
        $stmtGetCat = $pdo->prepare("SELECT id, has_dual_price FROM catalogue_categories WHERE name = ? AND is_active = 1 LIMIT 1");
        $stmtGetSubCat = $pdo->prepare("SELECT id FROM catalogue_sub_categories WHERE name = ? AND category_id = ? AND is_active = 1 LIMIT 1");
        $stmtGetSeries = $pdo->prepare("SELECT id FROM catalogue_series WHERE name = ? AND category_id = ? AND (sub_category_id = ? OR (? IS NULL AND sub_category_id IS NULL)) AND is_active = 1 LIMIT 1");

        $stmtCheckProdById = $pdo->prepare("SELECT id, series_id FROM catalogue_products WHERE id = ? LIMIT 1");
        $stmtCheckProdByCode = $pdo->prepare("SELECT id FROM catalogue_products WHERE code = ? AND series_id = ? LIMIT 1");
        $stmtInsertProd = $pdo->prepare("INSERT INTO catalogue_products (series_id, name, code, hsn_code, price, price_zone2, dimensions, specifications, variant_type, is_active, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'none', 1, ?)");
        $stmtUpdateProd = $pdo->prepare("UPDATE catalogue_products SET series_id=?, name=?, code=?, hsn_code=?, price=?, price_zone2=?, dimensions=?, specifications=?, display_order=? WHERE id=?");

        $stmtDelImages = $pdo->prepare("DELETE FROM catalogue_product_images WHERE product_id = ?");
        $stmtInsertImage = $pdo->prepare("INSERT INTO catalogue_product_images (product_id, image_url, is_primary, display_order) VALUES (?, ?, ?, ?)");

        $successCount = 0;
        $updateCount = 0;
        $skipCount = 0;

        while (($data = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (empty(implode('', $data)))
                continue;

            $catName = trim($data[$map['category']] ?? '');
            $subCatOriginal = $hasSubCategory ? trim($data[$map['subcategory']] ?? '') : '';
            $subCatName = (strtolower($subCatOriginal) === 'none' || $subCatOriginal === '') ? null : $subCatOriginal;

            $seriesName = trim($data[$map['series']] ?? '');
            $prodName = trim($data[$map['productname']] ?? '');
            $prodCode = trim($data[$map['productcode']] ?? '');
            $hsnCode = trim($data[$map['hsncode']] ?? '');
            $price = intval(preg_replace('/[^\d]/', '', $data[$map['price']] ?? 0));
            $priceZone2 = $hasPriceZone2 ? (trim($data[$map['pricezone2']] ?? '') !== '' ? intval(preg_replace('/[^\d]/', '', $data[$map['pricezone2']])) : null) : null;
            $dims = $hasDimensions ? trim($data[$map['dimensions']] ?? '') : '';
            $specs = $hasSpecifications ? str_replace(["\r\n", "\r", "\n"], '<br>', trim($data[$map['specifications']] ?? '')) : '';
            $displayOrder = $hasDisplayOrder ? intval($data[$map['displayorder']] ?? 0) : 0;
            $productId = $hasProductId ? trim($data[$map['productid']] ?? '') : '';

            $images = [];
            for ($i = 1; $i <= 5; $i++) {
                if (isset($map['image' . $i]) && !empty(trim($data[$map['image' . $i]] ?? ''))) {
                    $images[] = trim($data[$map['image' . $i]]);
                }
            }

            if (empty($prodName) && empty($prodCode)) {
                $skipCount++;
                continue;
            }

            // Validation
            if (empty($catName))
                throw new Exception("Row $rowNum: 'Category' is required.");
            if (empty($seriesName))
                throw new Exception("Row $rowNum: 'Series' is required.");
            if (empty($prodName))
                throw new Exception("Row $rowNum: 'ProductName' is required.");
            if (empty($prodCode))
                throw new Exception("Row $rowNum: 'ProductCode' is required.");
            if (empty($hsnCode))
                throw new Exception("Row $rowNum: 'HSNCode' is required.");
            if (empty($images) && !$simulate)
                throw new Exception("Row $rowNum: 'Image1' is required.");

            // Hierarchy
            $stmtGetCat->execute([$catName]);
            $catRow = $stmtGetCat->fetch(PDO::FETCH_ASSOC);
            if (!$catRow)
                throw new Exception("Row $rowNum: Category '$catName' not found.");
            $catId = $catRow['id'];

            $subCatId = null;
            if ($subCatName) {
                $stmtGetSubCat->execute([$subCatName, $catId]);
                $subCatId = $stmtGetSubCat->fetchColumn();
                if (!$subCatId)
                    throw new Exception("Row $rowNum: SubCategory '$subCatName' not found under '$catName'.");
            }

            $stmtGetSeries->execute([$seriesName, $catId, $subCatId, $subCatId]);
            $seriesId = $stmtGetSeries->fetchColumn();
            if (!$seriesId)
                throw new Exception("Row $rowNum: Series '$seriesName' not found.");

            // Product Logic
            $existingId = null;
            if (!empty($productId)) {
                $stmtCheckProdById->execute([$productId]);
                $existingRow = $stmtCheckProdById->fetch(PDO::FETCH_ASSOC);
                if ($existingRow)
                    $existingId = $existingRow['id'];
            }
            if (!$existingId) {
                $stmtCheckProdByCode->execute([$prodCode, $seriesId]);
                $existingId = $stmtCheckProdByCode->fetchColumn();
            }

            $prodId = $existingId;

            if ($existingId) {
                $stmtUpdateProd->execute([$seriesId, $prodName, $prodCode, $hsnCode, $price, $priceZone2, $dims, $specs, $displayOrder, $existingId]);
                $updateCount++;
            }
            else {
                $stmtInsertProd->execute([$seriesId, $prodName, $prodCode, $hsnCode, $price, $priceZone2, $dims, $specs, $displayOrder]);
                $prodId = $pdo->lastInsertId();
                $successCount++;
            }

            // Images
            if (!empty($images) && !$simulate) {
                $validNewImages = [];
                foreach ($images as $idx => $imgPath) {
                    $processedName = processImage($imgPath);
                    if (!$processedName)
                        throw new Exception("Row $rowNum: Could not process image '$imgPath'.");
                    $validNewImages[] = $processedName;
                }

                if ($existingId) {
                    $stmtOld = $pdo->prepare("SELECT image_url FROM catalogue_product_images WHERE product_id = ?");
                    $stmtOld->execute([$prodId]);
                    $oldFiles = $stmtOld->fetchAll(PDO::FETCH_COLUMN);
                    $stmtDelImages->execute([$prodId]);
                    foreach ($oldFiles as $oldF) {
                        if (!in_array($oldF, $validNewImages)) {
                            @unlink(dirname(__DIR__, 3) . '/uploads/catalogue/products/' . $oldF);
                        }
                    }
                }

                $imgOrder = 1;
                foreach ($validNewImages as $imgName) {
                    $isPrimary = ($imgOrder === 1) ? 1 : 0;
                    $stmtInsertImage->execute([$prodId, $imgName, $isPrimary, $imgOrder]);
                    $imgOrder++;
                }
            }
        }

        if ($simulate) {
            $msg = "✅ Simulation Successful! Data is valid. $successCount new products, $updateCount updates.";
            if ($skipCount > 0)
                $msg .= " ($skipCount blank rows skipped.)";
            throw new Exception($msg);
        }

        $pdo->commit();
        $msg = "✅ Import Complete: $successCount Created, $updateCount Updated.";
        if ($skipCount > 0)
            $msg .= " ($skipCount blank rows skipped.)";
        $_SESSION['import_report'] = $msg;

    }
    catch (Exception $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        $_SESSION['import_errors'] = [$e->getMessage()];
        if ($simulate && strpos($e->getMessage(), "Simulation Successful") !== false) {
            $_SESSION['import_report'] = $e->getMessage();
            $_SESSION['import_errors'] = [];
        }
    }

    cleanupZipExtraction();
    fclose($handle);
    header("Location: import.php");
    exit;
}

// ============================================================
// SIZE VARIANTS IMPORT
// ============================================================
if ($productType === 'size') {

    // Validate Required Headers
    $requiredKeys = ['category', 'series', 'productname', 'hsncode', 'variantcode', 'size', 'price'];
    $missingKeys = [];
    foreach ($requiredKeys as $k) {
        if (!isset($map[$k]))
            $missingKeys[] = $k;
    }
    if (!empty($missingKeys)) {
        $_SESSION['import_errors'] = ["Missing required columns for Size Variants: " . implode(', ', $missingKeys) . ". Please use the Size Variants template."];
        fclose($handle);
        header("Location: import.php");
        exit;
    }

    // Optional columns
    $hasProductId = isset($map['productid']);
    $hasSubCategory = isset($map['subcategory']);
    $hasPriceZone2 = isset($map['pricezone2']);
    $hasSpecifications = isset($map['specifications']);
    $hasDisplayOrder = isset($map['displayorder']);
    $hasVariantName = isset($map['variantname']);
    $hasVariantImage = isset($map['variantimage']);
    $hasVariantId = isset($map['variantid']);
    $hasGroupId = isset($map['groupid']);

    // Read ALL rows into memory first so we can group by product
    $allRows = [];
    $rowNum = 1;
    while (($data = fgetcsv($handle)) !== false) {
        $rowNum++;
        if (empty(implode('', $data)))
            continue;
        $allRows[] = ['data' => $data, 'row' => $rowNum];
    }

    // Group rows by Category+SubCategory+Series+ProductName (case-insensitive key)
    // If ProductID is provided, include it in the group key to perfectly separate same-named products.
    $productGroups = [];
    foreach ($allRows as $entry) {
        $data = $entry['data'];
        $catName = trim($data[$map['category']] ?? '');
        $subCatOriginal = $hasSubCategory ? trim($data[$map['subcategory']] ?? '') : '';
        $subCatName = (strtolower($subCatOriginal) === 'none' || $subCatOriginal === '') ? '' : $subCatOriginal;
        $seriesName = trim($data[$map['series']] ?? '');
        $prodName = trim($data[$map['productname']] ?? '');

        $productId = $hasProductId ? trim($data[$map['productid']] ?? '') : '';

        // If ProductID is provided, use it to separate products with exactly the same name
        $groupKey = strtolower("$catName|$subCatName|$seriesName|$prodName");
        if ($productId !== '') {
            $groupKey .= "|$productId";
        }

        if (!isset($productGroups[$groupKey])) {
            $productGroups[$groupKey] = [
                'catName' => $catName,
                'subCatName' => $subCatName ?: null,
                'seriesName' => $seriesName,
                'prodName' => $prodName,
                'variants' => [],
            ];
        }
        $productGroups[$groupKey]['variants'][] = $entry;
    }

    $pdo = getDBConnection();
    $pdo->beginTransaction();

    try {
        // Prepared Statements — Hierarchy
        $stmtGetCat = $pdo->prepare("SELECT id, has_dual_price FROM catalogue_categories WHERE name = ? AND is_active = 1 LIMIT 1");
        $stmtGetSubCat = $pdo->prepare("SELECT id FROM catalogue_sub_categories WHERE name = ? AND category_id = ? AND is_active = 1 LIMIT 1");
        $stmtGetSeries = $pdo->prepare("SELECT id FROM catalogue_series WHERE name = ? AND category_id = ? AND (sub_category_id = ? OR (? IS NULL AND sub_category_id IS NULL)) AND is_active = 1 LIMIT 1");

        // Parent product statements
        $stmtCheckProdById = $pdo->prepare("SELECT id, series_id FROM catalogue_products WHERE id = ? AND variant_type = 'size' LIMIT 1");
        $stmtCheckProdByName = $pdo->prepare("SELECT id FROM catalogue_products WHERE name = ? AND series_id = ? AND variant_type = 'size' LIMIT 1");
        $stmtInsertProd = $pdo->prepare("INSERT INTO catalogue_products (series_id, name, code, hsn_code, price, price_zone2, dimensions, specifications, variant_type, is_active, display_order) VALUES (?, ?, NULL, ?, 0, NULL, ?, ?, 'size', 1, ?)");
        $stmtUpdateProd = $pdo->prepare("UPDATE catalogue_products SET series_id=?, name=?, hsn_code=?, dimensions=?, specifications=?, display_order=? WHERE id=?");

        // Parent gallery images
        $stmtDelImages = $pdo->prepare("DELETE FROM catalogue_product_images WHERE product_id = ?");
        $stmtInsertImage = $pdo->prepare("INSERT INTO catalogue_product_images (product_id, image_url, is_primary, display_order) VALUES (?, ?, ?, ?)");

        // Variant statements
        $stmtCheckVariantById = $pdo->prepare("SELECT id FROM catalogue_product_variants WHERE id = ? AND product_id = ? LIMIT 1");
        $stmtCheckVariant = $pdo->prepare("SELECT id FROM catalogue_product_variants WHERE product_id = ? AND attribute_value = ? LIMIT 1");
        $stmtInsertVariant = $pdo->prepare("INSERT INTO catalogue_product_variants (product_id, name, code, price, price_zone2, attribute_value, image_url, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmtUpdateVariant = $pdo->prepare("UPDATE catalogue_product_variants SET name=?, code=?, price=?, price_zone2=?, attribute_value=?, image_url=?, display_order=? WHERE id=?");

        $newProductCount = 0;
        $updatedProductCount = 0;
        $newVariantCount = 0;
        $updatedVariantCount = 0;

        foreach ($productGroups as $group) {
            $firstRow = $group['variants'][0]['data'];
            $firstRowNum = $group['variants'][0]['row'];

            $catName = $group['catName'];
            $subCatName = $group['subCatName'];
            $seriesName = $group['seriesName'];
            $prodName = $group['prodName'];

            // Validate parent
            if (empty($catName))
                throw new Exception("Row $firstRowNum: 'Category' is required.");
            if (empty($seriesName))
                throw new Exception("Row $firstRowNum: 'Series' is required.");
            if (empty($prodName))
                throw new Exception("Row $firstRowNum: 'ProductName' is required.");

            // Hierarchy validation
            $stmtGetCat->execute([$catName]);
            $catRow = $stmtGetCat->fetch(PDO::FETCH_ASSOC);
            if (!$catRow)
                throw new Exception("Row $firstRowNum: Category '$catName' not found.");
            $catId = $catRow['id'];

            $subCatId = null;
            if ($subCatName) {
                $stmtGetSubCat->execute([$subCatName, $catId]);
                $subCatId = $stmtGetSubCat->fetchColumn();
                if (!$subCatId)
                    throw new Exception("Row $firstRowNum: SubCategory '$subCatName' not found under '$catName'.");
            }

            $stmtGetSeries->execute([$seriesName, $catId, $subCatId, $subCatId]);
            $seriesId = $stmtGetSeries->fetchColumn();
            if (!$seriesId)
                throw new Exception("Row $firstRowNum: Series '$seriesName' not found.");

            // Parent-level fields from first row
            $specs = $hasSpecifications ? str_replace(["\r\n", "\r", "\n"], '<br>', trim($firstRow[$map['specifications']] ?? '')) : '';
            $dims = isset($map['dimensions']) ? trim($firstRow[$map['dimensions']] ?? '') : '';
            $hsnCode = trim($firstRow[$map['hsncode']] ?? '');
            $displayOrder = $hasDisplayOrder ? intval($firstRow[$map['displayorder']] ?? 0) : 0;
            $productId = $hasProductId ? trim($firstRow[$map['productid']] ?? '') : '';

            // Validate HSNCode at parent level (compulsory)
            if (empty($hsnCode))
                throw new Exception("Row $firstRowNum: 'HSNCode' is required.");

            // Parent gallery images from first row
            $parentImages = [];
            for ($i = 1; $i <= 5; $i++) {
                if (isset($map['image' . $i]) && !empty(trim($firstRow[$map['image' . $i]] ?? ''))) {
                    $parentImages[] = trim($firstRow[$map['image' . $i]]);
                }
            }

            // Find or create parent product
            $existingProdId = null;

            // Only attempt ID match if ProductID is truly numeric (so dummy IDs like 'NEW-1' are ignored for DB lookup)
            $isNumericProductId = !empty($productId) && is_numeric($productId);

            // Priority 1: Match by ProductID (if numeric)
            if ($isNumericProductId) {
                $stmtCheckProdById->execute([$productId]);
                $existingRow = $stmtCheckProdById->fetch(PDO::FETCH_ASSOC);
                if ($existingRow)
                    $existingProdId = $existingRow['id'];
            }

            // Priority 2: Match by Name + Series
            // BUT ONLY IF we didn't explicitly pass a dummy ProductID to force a new product
            if (!$existingProdId && empty($productId)) {
                $stmtCheckProdByName->execute([$prodName, $seriesId]);
                $existingProdId = $stmtCheckProdByName->fetchColumn();
            }

            $prodId = $existingProdId;

            if ($existingProdId) {
                $stmtUpdateProd->execute([$seriesId, $prodName, $hsnCode, $dims, $specs, $displayOrder, $existingProdId]);
                $updatedProductCount++;
            }
            else {
                $stmtInsertProd->execute([$seriesId, $prodName, $hsnCode, $dims, $specs, $displayOrder]);
                $prodId = $pdo->lastInsertId();
                $newProductCount++;
            }

            // Handle parent gallery images
            if (!empty($parentImages) && !$simulate) {
                $validNewImages = [];
                foreach ($parentImages as $imgPath) {
                    $processedName = processImage($imgPath);
                    if (!$processedName)
                        throw new Exception("Row $firstRowNum: Could not process parent image '$imgPath'.");
                    $validNewImages[] = $processedName;
                }

                if ($existingProdId) {
                    $stmtOld = $pdo->prepare("SELECT image_url FROM catalogue_product_images WHERE product_id = ?");
                    $stmtOld->execute([$prodId]);
                    $oldFiles = $stmtOld->fetchAll(PDO::FETCH_COLUMN);
                    $stmtDelImages->execute([$prodId]);
                    foreach ($oldFiles as $oldF) {
                        if (!in_array($oldF, $validNewImages)) {
                            @unlink(dirname(__DIR__, 3) . '/uploads/catalogue/products/' . $oldF);
                        }
                    }
                }

                $imgOrder = 1;
                foreach ($validNewImages as $imgName) {
                    $isPrimary = ($imgOrder === 1) ? 1 : 0;
                    $stmtInsertImage->execute([$prodId, $imgName, $isPrimary, $imgOrder]);
                    $imgOrder++;
                }
            }

            // Process each variant row
            $variantOrder = 0;
            foreach ($group['variants'] as $entry) {
                $data = $entry['data'];
                $rn = $entry['row'];
                $variantOrder++;

                $vCode = trim($data[$map['variantcode']] ?? '');
                $vSize = trim($data[$map['size']] ?? '');
                $vPrice = intval(preg_replace('/[^\d]/', '', $data[$map['price']] ?? 0));
                $vPriceZ2 = $hasPriceZone2 ? (trim($data[$map['pricezone2']] ?? '') !== '' ? intval(preg_replace('/[^\d]/', '', $data[$map['pricezone2']])) : null) : null;
                $vName = $hasVariantName ? trim($data[$map['variantname']] ?? '') : '';
                $vImagePath = $hasVariantImage ? trim($data[$map['variantimage']] ?? '') : '';
                $variantId = $hasVariantId ? trim($data[$map['variantid']] ?? '') : '';

                // Validate variant
                if (empty($vCode))
                    throw new Exception("Row $rn: 'VariantCode' is required.");
                if (empty($vSize))
                    throw new Exception("Row $rn: 'Size' is required.");

                // Process variant image
                $vImageUrl = null;
                if (!empty($vImagePath) && !$simulate) {
                    $vImageUrl = processImage($vImagePath);
                    if (!$vImageUrl)
                        throw new Exception("Row $rn: Could not process variant image '$vImagePath'.");
                }

                // Priority 1: Match by VariantID if numeric (lets user change Size on re-import)
                // Priority 2: Match by Size + product
                $existingVariantId = null;
                if (!empty($variantId) && is_numeric($variantId)) {
                    $stmtCheckVariantById->execute([$variantId, $prodId]);
                    $existingVariantId = $stmtCheckVariantById->fetchColumn();
                }
                if (!$existingVariantId) {
                    $stmtCheckVariant->execute([$prodId, $vSize]);
                    $existingVariantId = $stmtCheckVariant->fetchColumn();
                }

                if ($existingVariantId) {
                    // UPDATE — if image is blank, keep existing; if provided, update
                    if (empty($vImagePath)) {
                        // Keep existing image — fetch current one
                        $stmtGetVarImg = $pdo->prepare("SELECT image_url FROM catalogue_product_variants WHERE id = ?");
                        $stmtGetVarImg->execute([$existingVariantId]);
                        $vImageUrl = $stmtGetVarImg->fetchColumn() ?: null;
                    }
                    else {
                        // Delete old variant image if different
                        $stmtGetVarImg = $pdo->prepare("SELECT image_url FROM catalogue_product_variants WHERE id = ?");
                        $stmtGetVarImg->execute([$existingVariantId]);
                        $oldVarImg = $stmtGetVarImg->fetchColumn();
                        if ($oldVarImg && $oldVarImg !== $vImageUrl) {
                            @unlink(dirname(__DIR__, 3) . '/uploads/catalogue/products/' . $oldVarImg);
                        }
                    }

                    $stmtUpdateVariant->execute([$vName, $vCode, $vPrice, $vPriceZ2, $vSize, $vImageUrl, $variantOrder, $existingVariantId]);
                    $updatedVariantCount++;
                }
                else {
                    // INSERT new variant
                    $stmtInsertVariant->execute([$prodId, $vName, $vCode, $vPrice, $vPriceZ2, $vSize, $vImageUrl, $variantOrder]);
                    $newVariantCount++;
                }
            }
        }

        if ($simulate) {
            $msg = "✅ Simulation Successful! Data is valid. Products: $newProductCount new, $updatedProductCount updates. Variants: $newVariantCount new, $updatedVariantCount updates.";
            throw new Exception($msg);
        }

        $pdo->commit();
        $_SESSION['import_report'] = "✅ Import Complete — Products: $newProductCount Created, $updatedProductCount Updated. Variants: $newVariantCount Created, $updatedVariantCount Updated.";

    }
    catch (Exception $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        $_SESSION['import_errors'] = [$e->getMessage()];
        if ($simulate && strpos($e->getMessage(), "Simulation Successful") !== false) {
            $_SESSION['import_report'] = $e->getMessage();
            $_SESSION['import_errors'] = [];
        }
    }

    cleanupZipExtraction();
    fclose($handle);
    header("Location: import.php");
    exit;
}

// ============================================================
// COLOR VARIANTS IMPORT
// ============================================================
if ($productType === 'color') {

    // Validate Required Headers
    $requiredKeys = ['category', 'series', 'productname', 'hsncode', 'variantcode', 'colorname', 'price'];
    $missingKeys = [];
    foreach ($requiredKeys as $k) {
        if (!isset($map[$k]))
            $missingKeys[] = $k;
    }
    if (!empty($missingKeys)) {
        $_SESSION['import_errors'] = ["Missing required columns for Color Variants: " . implode(', ', $missingKeys) . ". Please use the Color Variants template."];
        fclose($handle);
        header("Location: import.php");
        exit;
    }

    // Optional columns
    $hasProductId = isset($map['productid']);
    $hasSubCategory = isset($map['subcategory']);
    $hasPriceZone2 = isset($map['pricezone2']);
    $hasSpecifications = isset($map['specifications']);
    $hasDisplayOrder = isset($map['displayorder']);
    $hasVariantName = isset($map['variantname']);
    $hasVariantImage = isset($map['variantimage']);
    $hasVariantId = isset($map['variantid']);
    $hasColorDisplayName = isset($map['colordisplayname']);
    $hasColourLabel = isset($map['colourlabel']);
    $hasDimensions = isset($map['dimensions']);
    $hasGroupId = isset($map['groupid']);

    // Read ALL rows into memory first so we can group by product
    $allRows = [];
    $rowNum = 1;
    while (($data = fgetcsv($handle)) !== false) {
        $rowNum++;
        if (empty(implode('', $data)))
            continue;
        $allRows[] = ['data' => $data, 'row' => $rowNum];
    }

    // Group rows by Category+SubCategory+Series+ProductName (case-insensitive key)
    // If ProductID is provided, include it in the group key to perfectly separate same-named products.
    $productGroups = [];
    foreach ($allRows as $entry) {
        $data = $entry['data'];
        $catName = trim($data[$map['category']] ?? '');
        $subCatOriginal = $hasSubCategory ? trim($data[$map['subcategory']] ?? '') : '';
        $subCatName = (strtolower($subCatOriginal) === 'none' || $subCatOriginal === '') ? '' : $subCatOriginal;
        $seriesName = trim($data[$map['series']] ?? '');
        $prodName = trim($data[$map['productname']] ?? '');
        
        $productId = $hasProductId ? trim($data[$map['productid']] ?? '') : '';

        // If ProductID is provided, use it to separate products with exactly the same name
        $groupKey = strtolower("$catName|$subCatName|$seriesName|$prodName");
        if ($productId !== '') {
            $groupKey .= "|$productId";
        }

        if (!isset($productGroups[$groupKey])) {
            $productGroups[$groupKey] = [
                'catName' => $catName,
                'subCatName' => $subCatName ?: null,
                'seriesName' => $seriesName,
                'prodName' => $prodName,
                'variants' => [],
            ];
        }
        $productGroups[$groupKey]['variants'][] = $entry;
    }

    $pdo = getDBConnection();
    $pdo->beginTransaction();

    try {
        // Prepared Statements — Hierarchy
        $stmtGetCat = $pdo->prepare("SELECT id, has_dual_price FROM catalogue_categories WHERE name = ? AND is_active = 1 LIMIT 1");
        $stmtGetSubCat = $pdo->prepare("SELECT id FROM catalogue_sub_categories WHERE name = ? AND category_id = ? AND is_active = 1 LIMIT 1");
        $stmtGetSeries = $pdo->prepare("SELECT id FROM catalogue_series WHERE name = ? AND category_id = ? AND (sub_category_id = ? OR (? IS NULL AND sub_category_id IS NULL)) AND is_active = 1 LIMIT 1");

        // Parent product statements
        $stmtCheckProdById = $pdo->prepare("SELECT id, series_id FROM catalogue_products WHERE id = ? AND variant_type = 'color' LIMIT 1");
        $stmtCheckProdByName = $pdo->prepare("SELECT id FROM catalogue_products WHERE name = ? AND series_id = ? AND variant_type = 'color' LIMIT 1");
        $stmtInsertProd = $pdo->prepare("INSERT INTO catalogue_products (series_id, name, code, hsn_code, price, price_zone2, dimensions, specifications, colour_label, variant_type, is_active, display_order) VALUES (?, ?, NULL, ?, 0, NULL, ?, ?, ?, 'color', 1, ?)");
        $stmtUpdateProd = $pdo->prepare("UPDATE catalogue_products SET series_id=?, name=?, hsn_code=?, dimensions=?, specifications=?, colour_label=?, display_order=? WHERE id=?");

        // Parent gallery images
        $stmtDelImages = $pdo->prepare("DELETE FROM catalogue_product_images WHERE product_id = ?");
        $stmtInsertImage = $pdo->prepare("INSERT INTO catalogue_product_images (product_id, image_url, is_primary, display_order) VALUES (?, ?, ?, ?)");

        // Color lookup
        $stmtGetColor = $pdo->prepare("SELECT id, name FROM catalogue_colors WHERE LOWER(name) = LOWER(?) AND is_active = 1 LIMIT 1");

        // Variant statements — deduplicate by attribute_value (display name), NOT color_id,
        // so rows with the same color but different display names each create a separate variant.
        $stmtCheckVariantById = $pdo->prepare("SELECT id FROM catalogue_product_variants WHERE id = ? AND product_id = ? LIMIT 1");
        $stmtCheckVariant = $pdo->prepare("SELECT id FROM catalogue_product_variants WHERE product_id = ? AND attribute_value = ? LIMIT 1");
        $stmtInsertVariant = $pdo->prepare("INSERT INTO catalogue_product_variants (product_id, name, code, price, price_zone2, attribute_value, color_id, image_url, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
        $stmtUpdateVariant = $pdo->prepare("UPDATE catalogue_product_variants SET name=?, code=?, price=?, price_zone2=?, attribute_value=?, color_id=?, image_url=?, display_order=? WHERE id=?");

        $newProductCount = 0;
        $updatedProductCount = 0;
        $newVariantCount = 0;
        $updatedVariantCount = 0;

        foreach ($productGroups as $group) {
            $firstRow = $group['variants'][0]['data'];
            $firstRowNum = $group['variants'][0]['row'];

            $catName = $group['catName'];
            $subCatName = $group['subCatName'];
            $seriesName = $group['seriesName'];
            $prodName = $group['prodName'];

            // Validate parent
            if (empty($catName))
                throw new Exception("Row $firstRowNum: 'Category' is required.");
            if (empty($seriesName))
                throw new Exception("Row $firstRowNum: 'Series' is required.");
            if (empty($prodName))
                throw new Exception("Row $firstRowNum: 'ProductName' is required.");

            // Hierarchy validation
            $stmtGetCat->execute([$catName]);
            $catRow = $stmtGetCat->fetch(PDO::FETCH_ASSOC);
            if (!$catRow)
                throw new Exception("Row $firstRowNum: Category '$catName' not found.");
            $catId = $catRow['id'];

            $subCatId = null;
            if ($subCatName) {
                $stmtGetSubCat->execute([$subCatName, $catId]);
                $subCatId = $stmtGetSubCat->fetchColumn();
                if (!$subCatId)
                    throw new Exception("Row $firstRowNum: SubCategory '$subCatName' not found under '$catName'.");
            }

            $stmtGetSeries->execute([$seriesName, $catId, $subCatId, $subCatId]);
            $seriesId = $stmtGetSeries->fetchColumn();
            if (!$seriesId)
                throw new Exception("Row $firstRowNum: Series '$seriesName' not found.");

            // Parent-level fields from first row
            $specs = $hasSpecifications ? str_replace(["\r\n", "\r", "\n"], '<br>', trim($firstRow[$map['specifications']] ?? '')) : '';
            $dims = isset($map['dimensions']) ? trim($firstRow[$map['dimensions']] ?? '') : '';
            $hsnCode = trim($firstRow[$map['hsncode']] ?? '');
            $colourLabelRaw = $hasColourLabel ? strtolower(trim($firstRow[$map['colourlabel']] ?? '')) : 'colour';
            $colourLabel = in_array($colourLabelRaw, ['colour', 'finish']) ? $colourLabelRaw : 'colour';
            $displayOrder = $hasDisplayOrder ? intval($firstRow[$map['displayorder']] ?? 0) : 0;
            $productId = $hasProductId ? trim($firstRow[$map['productid']] ?? '') : '';

            // Validate HSNCode at parent level (compulsory)
            if (empty($hsnCode))
                throw new Exception("Row $firstRowNum: 'HSNCode' is required.");

            // Parent gallery images from first row
            $parentImages = [];
            for ($i = 1; $i <= 5; $i++) {
                if (isset($map['image' . $i]) && !empty(trim($firstRow[$map['image' . $i]] ?? ''))) {
                    $parentImages[] = trim($firstRow[$map['image' . $i]]);
                }
            }

            // Find or create parent product
            $existingProdId = null;

            // Only attempt ID match if ProductID is truly numeric (so dummy IDs like 'NEW-1' are ignored for DB lookup)
            $isNumericProductId = !empty($productId) && is_numeric($productId);

            // Priority 1: Match by ProductID (if numeric)
            if ($isNumericProductId) {
                $stmtCheckProdById->execute([$productId]);
                $existingRow = $stmtCheckProdById->fetch(PDO::FETCH_ASSOC);
                if ($existingRow)
                    $existingProdId = $existingRow['id'];
            }

            // Priority 2: Match by Name + Series 
            // BUT ONLY IF we didn't explicitly pass a dummy ProductID to force a new product
            if (!$existingProdId && empty($productId)) {
                $stmtCheckProdByName->execute([$prodName, $seriesId]);
                $existingProdId = $stmtCheckProdByName->fetchColumn();
            }

            $prodId = $existingProdId;

            if ($existingProdId) {
                $stmtUpdateProd->execute([$seriesId, $prodName, $hsnCode, $dims, $specs, $colourLabel, $displayOrder, $existingProdId]);
                $updatedProductCount++;
            }
            else {
                $stmtInsertProd->execute([$seriesId, $prodName, $hsnCode, $dims, $specs, $colourLabel, $displayOrder]);
                $prodId = $pdo->lastInsertId();
                $newProductCount++;
            }

            // Handle parent gallery images
            if (!empty($parentImages) && !$simulate) {
                $validNewImages = [];
                foreach ($parentImages as $imgPath) {
                    $processedName = processImage($imgPath);
                    if (!$processedName)
                        throw new Exception("Row $firstRowNum: Could not process parent image '$imgPath'.");
                    $validNewImages[] = $processedName;
                }

                if ($existingProdId) {
                    $stmtOld = $pdo->prepare("SELECT image_url FROM catalogue_product_images WHERE product_id = ?");
                    $stmtOld->execute([$prodId]);
                    $oldFiles = $stmtOld->fetchAll(PDO::FETCH_COLUMN);
                    $stmtDelImages->execute([$prodId]);
                    foreach ($oldFiles as $oldF) {
                        if (!in_array($oldF, $validNewImages)) {
                            @unlink(dirname(__DIR__, 3) . '/uploads/catalogue/products/' . $oldF);
                        }
                    }
                }

                $imgOrder = 1;
                foreach ($validNewImages as $imgName) {
                    $isPrimary = ($imgOrder === 1) ? 1 : 0;
                    $stmtInsertImage->execute([$prodId, $imgName, $isPrimary, $imgOrder]);
                    $imgOrder++;
                }
            }

            // Process each variant row
            $variantOrder = 0;
            foreach ($group['variants'] as $entry) {
                $data = $entry['data'];
                $rn = $entry['row'];
                $variantOrder++;

                $vCode = trim($data[$map['variantcode']] ?? '');
                $vColorName = trim($data[$map['colorname']] ?? '');
                $vColorDisplayName = $hasColorDisplayName ? trim($data[$map['colordisplayname']] ?? '') : '';
                $vPrice = intval(preg_replace('/[^\d]/', '', $data[$map['price']] ?? 0));
                $vPriceZ2 = $hasPriceZone2 ? (trim($data[$map['pricezone2']] ?? '') !== '' ? intval(preg_replace('/[^\d]/', '', $data[$map['pricezone2']])) : null) : null;
                $vName = $hasVariantName ? trim($data[$map['variantname']] ?? '') : '';
                $vImagePath = $hasVariantImage ? trim($data[$map['variantimage']] ?? '') : '';
                $variantId = $hasVariantId ? trim($data[$map['variantid']] ?? '') : '';

                // Validate variant
                if (empty($vCode))
                    throw new Exception("Row $rn: 'VariantCode' is required.");
                if (empty($vColorName))
                    throw new Exception("Row $rn: 'ColorName' is required.");

                // Look up color in Color Library
                $stmtGetColor->execute([$vColorName]);
                $colorRow = $stmtGetColor->fetch(PDO::FETCH_ASSOC);
                if (!$colorRow)
                    throw new Exception("Row $rn: Color '$vColorName' not found in Color Library. Please create it first.");
                $colorId = $colorRow['id'];

                // attribute_value = ColorDisplayName if provided, else ColorName
                $attributeValue = !empty($vColorDisplayName) ? $vColorDisplayName : $colorRow['name'];

                // Process variant image
                $vImageUrl = null;
                if (!empty($vImagePath) && !$simulate) {
                    $vImageUrl = processImage($vImagePath);
                    if (!$vImageUrl)
                        throw new Exception("Row $rn: Could not process variant image '$vImagePath'.");
                }

                // Priority 1: Match by VariantID if numeric (lets user change ColorDisplayName on re-import)
                // Priority 2: Match by attribute_value (display name) + product
                $existingVariantId = null;
                if (!empty($variantId) && is_numeric($variantId)) {
                    $stmtCheckVariantById->execute([$variantId, $prodId]);
                    $existingVariantId = $stmtCheckVariantById->fetchColumn();
                }
                if (!$existingVariantId) {
                    $stmtCheckVariant->execute([$prodId, $attributeValue]);
                    $existingVariantId = $stmtCheckVariant->fetchColumn();
                }

                if ($existingVariantId) {
                    // UPDATE — if image is blank, keep existing; if provided, update
                    if (empty($vImagePath)) {
                        // Keep existing image
                        $stmtGetVarImg = $pdo->prepare("SELECT image_url FROM catalogue_product_variants WHERE id = ?");
                        $stmtGetVarImg->execute([$existingVariantId]);
                        $vImageUrl = $stmtGetVarImg->fetchColumn() ?: null;
                    }
                    else {
                        // Delete old variant image if different
                        $stmtGetVarImg = $pdo->prepare("SELECT image_url FROM catalogue_product_variants WHERE id = ?");
                        $stmtGetVarImg->execute([$existingVariantId]);
                        $oldVarImg = $stmtGetVarImg->fetchColumn();
                        if ($oldVarImg && $oldVarImg !== $vImageUrl) {
                            @unlink(dirname(__DIR__, 3) . '/uploads/catalogue/products/' . $oldVarImg);
                        }
                    }

                    $stmtUpdateVariant->execute([$vName, $vCode, $vPrice, $vPriceZ2, $attributeValue, $colorId, $vImageUrl, $variantOrder, $existingVariantId]);
                    $updatedVariantCount++;
                }
                else {
                    // INSERT new variant
                    $stmtInsertVariant->execute([$prodId, $vName, $vCode, $vPrice, $vPriceZ2, $attributeValue, $colorId, $vImageUrl, $variantOrder]);
                    $newVariantCount++;
                }
            }
        }

        if ($simulate) {
            $msg = "✅ Simulation Successful! Data is valid. Products: $newProductCount new, $updatedProductCount updates. Variants: $newVariantCount new, $updatedVariantCount updates.";
            throw new Exception($msg);
        }

        $pdo->commit();
        $_SESSION['import_report'] = "✅ Import Complete — Products: $newProductCount Created, $updatedProductCount Updated. Variants: $newVariantCount Created, $updatedVariantCount Updated.";

    }
    catch (Exception $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        $_SESSION['import_errors'] = [$e->getMessage()];
        if ($simulate && strpos($e->getMessage(), "Simulation Successful") !== false) {
            $_SESSION['import_report'] = $e->getMessage();
            $_SESSION['import_errors'] = [];
        }
    }

    cleanupZipExtraction();
    fclose($handle);
    header("Location: import.php");
    exit;
}

// Fallback — unsupported type
cleanupZipExtraction();
fclose($handle);
$_SESSION['import_errors'] = ["Unsupported product type: " . htmlspecialchars($productType)];
header("Location: import.php");
exit;

// --- HELPERS ---

function processImage($sourcePath)
{
    $baseDir = dirname(__DIR__, 3) . '/uploads/catalogue/products/';
    if (!file_exists($baseDir)) {
        mkdir($baseDir, 0777, true);
    }

    // 1. Just a filename that already exists (Re-import case)
    if (basename($sourcePath) === $sourcePath && file_exists($baseDir . $sourcePath)) {
        return $sourcePath;
    }

    // 2. Local Path or URL -> Copy
    $ext = pathinfo($sourcePath, PATHINFO_EXTENSION);
    if (!$ext)
        $ext = 'jpg';
    $ext = explode('?', $ext)[0];

    $newFilename = 'prod_' . time() . '_' . uniqid() . '.' . $ext;
    $destPath = $baseDir . $newFilename;

    $content = false;

    if (filter_var($sourcePath, FILTER_VALIDATE_URL)) {
        $content = @file_get_contents($sourcePath);
    }
    elseif (@file_exists($sourcePath)) {
        $content = @file_get_contents($sourcePath);
    }
    else {
        // 3. Check extracted ZIP files (path-suffix matching)
        if (!empty($GLOBALS['zip_file_map'])) {
            $normalizedPath = strtolower(str_replace('\\', '/', $sourcePath));

            // Try matching by path suffix (e.g., "ptmt/10.abs shower with abs arm/04.jpg")
            $parts = explode('/', $normalizedPath);
            $found = false;
            for ($si = 0; $si < count($parts); $si++) {
                $suffix = implode('/', array_slice($parts, $si));
                $key = 'path:' . $suffix;
                if (isset($GLOBALS['zip_file_map'][$key])) {
                    $content = @file_get_contents($GLOBALS['zip_file_map'][$key]);
                    $found = true;
                    break;
                }
            }

            // Fallback: match by filename only (if unique)
            if (!$found) {
                $basename = strtolower(basename($sourcePath));
                $nameKey = 'name:' . $basename;
                if (isset($GLOBALS['zip_file_map'][$nameKey]) && count($GLOBALS['zip_file_map'][$nameKey]) === 1) {
                    $content = @file_get_contents($GLOBALS['zip_file_map'][$nameKey][0]);
                }
            }
        }

        // 4. Legacy staging folder check
        if ($content === false) {
            $stagingPath = dirname(__DIR__, 3) . '/uploads/catalogue/import_staging/' . basename($sourcePath);
            if (@file_exists($stagingPath)) {
                $content = @file_get_contents($stagingPath);
            }
        }
    }

    if ($content !== false) {
        file_put_contents($destPath, $content);
        return $newFilename;
    }

    return false;
}

function cleanupZipExtraction()
{
    $dir = $GLOBALS['zip_extract_dir'] ?? '';
    if (!empty($dir) && is_dir($dir)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
            );
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                @rmdir($file->getPathname());
            }
            else {
                @unlink($file->getPathname());
            }
        }
        @rmdir($dir);
    }
}
