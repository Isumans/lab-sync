<?php
// Absolute filesystem paths
define('APP_PATH', realpath(__DIR__));                // .../lab-sync/app
define('VIEW_PATH', APP_PATH . DIRECTORY_SEPARATOR . 'views');
define('PARTIALS_PATH', VIEW_PATH . DIRECTORY_SEPARATOR . 'partials');

/**
 * Compute BASE_URL relative to the web server document root.
 * Works whether you open /index.php or a deep view like /app/views/patient/book.php
 * Result will be like "/lab-sync" for XAMPP htdocs\lab-sync\...
 */
$docRootFs = rtrim(str_replace('\\','/', realpath($_SERVER['DOCUMENT_ROOT'])), '/');
$projectRootFs = rtrim(str_replace('\\','/', realpath(dirname(APP_PATH))), '/');  // one level up from /app
$base = substr($projectRootFs, strlen($docRootFs));      // => "/lab-sync"
if ($base === false) { $base = ''; }
if ($base === '' || $base[0] !== '/') { $base = '/' . ltrim($base, '/'); }
define('BASE_URL', $base);

/** Helper to build asset URLs from project root */
function asset(string $path): string { return BASE_URL . '/' . ltrim($path, '/'); }
