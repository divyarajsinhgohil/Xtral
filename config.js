/**
 * ═══════════════════════════════════════════════════════════════
 *  XTRAL FRONT — DEPLOY CONFIGURATION
 *  This is the ONLY file you need to edit when deploying live.
 * ═══════════════════════════════════════════════════════════════
 *
 * The front side (xfront) and the admin side (xadmin) can live in
 * different folders or even different domains. The front calls the
 * admin's APIs at the address configured below.
 *
 * HOW TO DEPLOY:
 *   1. Upload xadmin to your server (e.g. https://yourdomain.com/xadmin)
 *   2. Upload xfront anywhere (same server, subdomain, or another host)
 *   3. Change LIVE_API below to your real admin API address
 */
const XTRAL_CONFIG = {
    SITE_NAME: 'X-Tral',

    // API address used when browsing on localhost (XAMPP)
    LOCAL_API: 'http://localhost/Xtral/xadmin/api/webapi',

    // API address used on the live server  ← CHANGE THIS BEFORE DEPLOY
    LIVE_API: 'https://x-tral.com/xadmin/api/webapi',
};

// Picks LOCAL_API on localhost, LIVE_API everywhere else — automatic.
const API_BASE = ['localhost', '127.0.0.1'].includes(window.location.hostname)
    ? XTRAL_CONFIG.LOCAL_API
    : XTRAL_CONFIG.LIVE_API;
