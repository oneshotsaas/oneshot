<!DOCTYPE html>
<?php
$_section     = $layout_section ?? 'front';
$_validSec    = in_array($_section, ['admin', 'app', 'front'], true) ? $_section : 'front';
$_defaultMode = option("appearance.{$_validSec}_default_mode", 'light');
$_userMode    = userOption('appearance.mode');  // user picks light or dark
$_mode        = in_array($_userMode, ['light', 'dark'], true) ? $_userMode : $_defaultMode;
$_validMode   = in_array($_mode, ['light', 'dark'], true) ? $_mode : 'light';
// Admin defines WHICH theme corresponds to each mode — user just picks the mode
$_theme       = option("appearance.{$_validSec}_{$_validMode}_theme", $_validMode) ?: $_validMode;
$_cssFile     = FCPATH . 'assets/css/themes/custom-' . $_validSec . '-' . $_validMode . '.css';
$_cssUrl      = base_url('assets/css/themes/custom-' . $_validSec . '-' . $_validMode . '.css');
$_logoUrl     = option('branding.logo_url', '/logo-icon.svg');
$_appName     = option('general.app_name', config('App')->name ?? 'OneShot');
?>
<html lang="<?= substr(service('request')->getLocale(), 0, 2) ?>" data-theme="<?= esc($_theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($meta_title ?? $_appName) ?></title>
    <?php if (!empty($meta_desc)): ?>
    <meta name="description" content="<?= esc($meta_desc) ?>">
    <?php endif; ?>
    <?php if (!empty($meta_og)): ?>
    <meta property="og:image" content="<?= esc($meta_og) ?>">
    <?php endif; ?>
    <?php $_faviconUrl = option('branding.favicon_url', ''); ?>
    <link rel="icon" type="image/svg+xml" href="<?= !empty($_faviconUrl) ? esc($_faviconUrl) : '/favicon.svg' ?>">
    <link href="<?= base_url('assets/css/daisyui.css') ?>" rel="stylesheet" type="text/css" />
    <script src="<?= base_url('assets/js/tailwind-browser.js') ?>"></script>
    <?php
    // Sanitize: only a-z, 0-9, hyphens — no path traversal possible
    $_themeSafe = preg_replace('/[^a-z0-9\-]/', '', strtolower($_theme));
    $_themeFile = FCPATH . 'assets/css/themes/' . $_themeSafe . '.css';
    $_themeUrl  = base_url('assets/css/themes/' . $_themeSafe . '.css');
    if ($_themeSafe && is_file($_themeFile)):
    ?>
    <link rel="stylesheet" href="<?= $_themeUrl ?>?v=<?= filemtime($_themeFile) ?>">
    <?php endif; ?>
    <?php if (is_file($_cssFile) && filesize($_cssFile) > 0): ?>
    <link rel="stylesheet" href="<?= $_cssUrl ?>?v=<?= filemtime($_cssFile) ?>">
    <?php endif; ?>
</head>
