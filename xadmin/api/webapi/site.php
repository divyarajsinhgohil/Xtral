<?php
/**
 * GET /api/webapi/site.php
 * Site-wide company info for the front website: name, logo (header/footer),
 * and contact details. Managed in Admin → Configuration → Company Info.
 */
require_once __DIR__ . '/config.php';

requireMethod('GET');

jsonResponse(true, [
    'name'     => getSetting('company_name') ?: 'X-Tral',
    'logo'     => uploadUrl(getSetting('company_logo'), 'settings'),
    'email'    => getSetting('company_email') ?: null,
    'phone'    => getSetting('company_toll_free') ?: null,
    'whatsapp' => getSetting('company_whatsapp') ?: null,
    'website'  => getSetting('company_website') ?: null,
    'address'  => getSetting('company_address') ?: null,
    'social_facebook'  => getSetting('social_facebook') ?: null,
    'social_instagram' => getSetting('social_instagram') ?: null,
    'social_youtube'   => getSetting('social_youtube') ?: null,
    'social_twitter'   => getSetting('social_twitter') ?: null,
    'price_label_1'    => getPriceLabel1(),
    'price_label_2'    => getPriceLabel2(),
]);
