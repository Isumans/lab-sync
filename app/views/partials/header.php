<?php if (!defined('PARTIALS_PATH')) require_once __DIR__ . '/../../bootstrap.php';
$title = $title ?? 'LabSync';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="stylesheet" href="<?= asset('public/css/patient.css') ?>" />
</head>
<body>
<header class="navbar">
  <div class="nav-logo" onclick="location.href='<?= asset('index.php') ?>'" style="cursor:pointer">
    <img src="<?= asset('public/assests/Labsync-3.png') ?>" alt="LabSync Logo"/>
    <span>LabSync</span>
  </div>
  <nav class="nav-links">
    <a href="<?= asset('index.php#tests') ?>">Tests</a>
    <a href="<?= asset('index.php#how') ?>">How it works</a>
    <a href="<?= asset('index.php#help') ?>">Help</a>
  </nav>
  <div class="nav-actions">
    <!-- As requested: show Login / Sign up on the front page nav -->
    <a href="#" class="login">Login</a>
    <a href="#" class="signup">Sign up</a>
  </div>
</header>
