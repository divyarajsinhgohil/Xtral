<?php
/** Use the image contents as its cache version, including same-name replacements. */
function catalogueImageUrl(?string $url, string $path): ?string
{
    if (!$url || !is_file($path)) {
        return $url;
    }
    $version = hash_file('sha256', $path);
    if ($version === false) {
        return $url;
    }
    return $url . (strpos($url, '?') === false ? '?' : '&') . 'v=' . substr($version, 0, 16);
}
