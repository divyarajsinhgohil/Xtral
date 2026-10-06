X-TRAL CATALOGUE COVER FIX

The admin cover must remain the selected cover after the PDF loads.
The 3D viewer now keeps the uploaded cover for the book, zoom and thumbnails.
It reads the saved thumbnail filename, including JPG, JPEG, PNG and WebP.
If no usable custom cover is available, the PDF cover is used.

Admin previews, catalogue cards and API responses use image-content versions
so a replacement with the same filename cannot reuse an older cached image.
The catalogue list/API requests are not cached.

Back up your existing website files. Upload/extract this package into the
website root, keeping all paths. Do not put it inside another subfolder.

Updated files:
catalogue.php
catalogue-view.php
assets/js/main.js
assets/js/flipbook.js
assets/css/flipbook.css
xadmin/api/webapi/catalogues.php
xadmin/includes/catalogue_assets.php (NEW - must upload this file)
xadmin/modules/settings/whatsapp_catalogues.php

Also included from the previous 3D viewer repair:
assets/vendor/pdf.min.js
assets/vendor/pdf.worker.min.js
assets/vendor/page-flip.browser.js

No database migration is needed. Do not replace catalogue PDFs or uploads.
If the current uploaded image exists on the server, it will be used.
After deployment, refresh the catalogue page with Ctrl+F5 once.

Verified locally: PHP/JavaScript syntax, same-name image version changes even
with identical timestamps and sizes, API no-store headers, uploaded cover
persistence after loading/page turns, matching cover in zoom and thumbnails,
and PDF fallback when the uploaded cover is missing.

This package has not been uploaded to the live server.
