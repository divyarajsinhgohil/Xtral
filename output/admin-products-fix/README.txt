X-TRAL ADMIN PRODUCTS PAGE FIX

Upload the xadmin folder in this package into the website root (usually public_html), preserving the paths. Back up the existing list.php and edit.php files first.

Files:
xadmin/includes/catalogue_product_hierarchy.php (new helper; upload first)
xadmin/modules/catalogue/products/list.php (replace)
xadmin/modules/catalogue/products/edit.php (replace)

This patch detects whether the database stores category_id and sub_category_id directly on products. It uses the existing series relationship when those columns are absent, avoiding unknown-column failures. The edit page now also loads products with no series.

No database migration or product deletion is needed. Product images, prices, product records and server credentials are not included or changed by this package.

Verified: PHP syntax; product list and edit queries with legacy and current database layouts; direct-product and series-product editor lookup; category filter; full local list and edit-page rendering. The live authenticated page returned an HTTP response failure, but the live PHP error log was inaccessible, so the exact live exception remains unconfirmed.

After upload, refresh the Products page and open a Health Faucet product using its Edit button. If the live failure persists, inspect the hosting PHP error log.
