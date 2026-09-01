<?php
// Function to recursively clean up files before deletion


function deleteCatalogueEntity($pdo, $type, $id) {
    $baseDetail = dirname(__DIR__, 2) . '/uploads/catalogue/';

    if ($type === 'product') {
        // Get Images
        $stmt = $pdo->prepare("SELECT image_url FROM catalogue_product_images WHERE product_id = ?");
        $stmt->execute([$id]);
        $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($images as $img) {
            $path = $baseDetail . 'products/' . $img;
            if (file_exists($path)) {
                @unlink($path);
            }
        }
        // Delete Variants first (explicitly)
        $pdo->prepare("DELETE FROM catalogue_product_variants WHERE product_id = ?")->execute([$id]);
        // Delete Images records (explicitly)
        $pdo->prepare("DELETE FROM catalogue_product_images WHERE product_id = ?")->execute([$id]);
        
        return;
    }

    if ($type === 'series') {
        // 1. Find all Products
        $stmtProds = $pdo->prepare("SELECT id FROM catalogue_products WHERE series_id = ?");
        $stmtProds->execute([$id]);
        $products = $stmtProds->fetchAll(PDO::FETCH_COLUMN);
        foreach ($products as $pid) {
            deleteCatalogueEntity($pdo, 'product', $pid);
        }

        // 2. Delete Series Image
        $stmtImg = $pdo->prepare("SELECT image_url FROM catalogue_series WHERE id = ?");
        $stmtImg->execute([$id]);
        $img = $stmtImg->fetchColumn();
        if ($img) {
            $path = $baseDetail . 'series/' . $img;
            if (file_exists($path)) @unlink($path);
        }
        return;
    }

    if ($type === 'sub_category') {
        // 1. Find all Series
        $stmtSeries = $pdo->prepare("SELECT id FROM catalogue_series WHERE sub_category_id = ?");
        $stmtSeries->execute([$id]);
        $allSeries = $stmtSeries->fetchAll(PDO::FETCH_COLUMN);
        foreach ($allSeries as $sid) {
            deleteCatalogueEntity($pdo, 'series', $sid);
        }

        // 2. Delete SubCat Image
        $stmtImg = $pdo->prepare("SELECT image_url FROM catalogue_sub_categories WHERE id = ?");
        $stmtImg->execute([$id]);
        $img = $stmtImg->fetchColumn();
        if ($img) {
            $path = $baseDetail . 'sub_categories/' . $img;
            if (file_exists($path)) @unlink($path);
        }
        return;
    }

    if ($type === 'category') {
        // 1. Find all SubCategories
        $stmtSub = $pdo->prepare("SELECT id FROM catalogue_sub_categories WHERE category_id = ?");
        $stmtSub->execute([$id]);
        $subs = $stmtSub->fetchAll(PDO::FETCH_COLUMN);
        foreach ($subs as $subId) {
            deleteCatalogueEntity($pdo, 'sub_category', $subId);
        }

        // 2. Find all Direct Series (if any, Series can also be directly under Category)
        // Check series where sub_category_id IS NULL AND category_id = ?
        $stmtDirectSeries = $pdo->prepare("SELECT id FROM catalogue_series WHERE category_id = ? AND sub_category_id IS NULL");
        $stmtDirectSeries->execute([$id]);
        $directSeries = $stmtDirectSeries->fetchAll(PDO::FETCH_COLUMN);
        foreach ($directSeries as $sid) {
            deleteCatalogueEntity($pdo, 'series', $sid);
        }

        // 3. Delete Category Image
        $stmtImg = $pdo->prepare("SELECT image_url FROM catalogue_categories WHERE id = ?");
        $stmtImg->execute([$id]);
        $img = $stmtImg->fetchColumn();
        if ($img) {
            $path = $baseDetail . 'categories/' . $img;
            if (file_exists($path)) @unlink($path);
        }
        return;
    }
}
