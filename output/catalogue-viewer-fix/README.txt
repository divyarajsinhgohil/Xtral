X-TRAL CATALOGUE 3D VIEWER FIX

The live website returns 404 for these required files:
assets/vendor/pdf.min.js
assets/vendor/pdf.worker.min.js
assets/vendor/page-flip.browser.js

Upload the six website files in this ZIP to the website root (the folder
containing catalogue-view.php). Keep the assets subfolders and replace the
corresponding files. Do not replace the uploads folder or the catalogue PDF.
Back up the existing website files before replacing them.

Included website files:
catalogue-view.php
assets/js/flipbook.js
assets/css/flipbook.css
assets/vendor/pdf.min.js
assets/vendor/pdf.worker.min.js
assets/vendor/page-flip.browser.js

The viewer now recovers from missing libraries with pinned fallback copies,
waits for the front cover to render before dismissing the loading screen,
and provides Try again / Open PDF buttons if loading fails.

Verified locally: front-cover display, page turning, fallback loading with
both vendor scripts missing, and recovery controls when fallback is blocked.
PHP and JavaScript syntax checks passed.

After upload: open /catalogue, choose Open 3D Lookbook, and refresh once.
The live server has not been changed by this local repair.
