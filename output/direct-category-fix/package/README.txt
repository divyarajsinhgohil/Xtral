X-TRAL TRUE DIRECT-CATEGORY PRODUCTS FIX

This package replaces the earlier admin-products-fix package.

INSTALL ON x-tral.com
1. Back up the website files being replaced and export the database from your hosting control panel.
2. Extract this ZIP in the website root (usually public_html), preserving the xadmin paths. Upload xadmin/includes/catalogue_product_hierarchy.php first, then all remaining files. No config/db.php or credentials are included.
3. Open Admin > Products > Repair Direct Products, or /xadmin/modules/catalogue/products/repair_direct.php while signed in.
4. Select the automatically created HEALTH FAUCETS series. Check its product count (expected 9). Leave genuine series unselected.
5. Click "Move selected products and delete their empty placeholder series". This also prepares the missing database fields. A success message confirms the number of moved products.
6. Return to Products. Edit a Health Faucet: Main Category must be HEALTH FAUCETS and Series must be blank / Direct to Main Category. Save it. No new series should appear.

WHAT CHANGES
- Adds missing category_id and sub_category_id columns and allows series_id to be NULL. Upgrade runs before write transactions, avoiding MySQL implicit-commit problems.
- Create/edit with blank Series stores category_id directly and series_id=NULL. No series is created automatically.
- Validates category, sub-category and series ownership; clears stale sub-category selection when a series is selected.
- Direct labels require a NULL series_id, rather than guessing from equal category/series names.
- Editor, product list, category filters, public product API, exports and QR lookups support direct products.
- Existing products are moved in place; IDs, images, codes, prices, variants and QR links are retained.
- The repair saves a JSON backup for each selected series in xadmin/logs/direct-category-backup-*.json before moving products. This folder is protected from web access. Preserve these backups. They contain the original series row and each product's original hierarchy for recovery by your site administrator.
- A selected placeholder is deleted only after all its products are detached. Other linked features or duplicate product codes block the move. Failed moves roll back.

OPTIONAL DATABASE PREPARATION
The repair page also has "Prepare database for direct products", which upgrades the schema without moving or deleting any records. Normal create/edit saves also perform the required schema upgrade automatically. The database account needs ALTER permission for the one-time upgrade.

VALIDATION
- All 14 PHP files passed syntax validation.
- Actual create, edit and duplicate-code handlers tested against isolated legacy-schema fixtures.
- Schema upgrade and repeated runs tested; no automatic series insertion.
- Repair preserves product data and images, removes only the selected placeholder, protects real series, blocks duplicate codes and rolls back on backup failure.
- Product listing/filtering and editor tested with both legacy and current layouts; full list/edit rendering checked locally.
- Public API verified to return existing direct products with series_id=null; exports include direct products.

The live files and database have not been changed by building this ZIP. Upload and run the signed-in repair step to apply it on the live site.
