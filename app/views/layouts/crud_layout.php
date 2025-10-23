<?php
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /lab_sync/index.php?controller=Auth&action=login');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $pageTitle ?? 'LabSync'; ?></title>
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/table.css">
    <?php if (isset($extraStyles)) echo $extraStyles; ?>
</head>
<body>
    <?php require PUBLIC_PATH . '/navbar.php'; ?>
    <div class="container">
        <?php require PUBLIC_PATH . '/sidebar.php'; ?>
        <main class="main-content">
            <?php echo $content ?? ''; ?>
        </main>
    </div>
</body>
</html>