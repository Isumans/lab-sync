
<?php
$role = (string)($_SESSION['user_role'] ?? '');
$isStaff = in_array($role, ['admin', 'receptionist', 'technician'], true);
$mustChangePassword = intval($_SESSION['must_change_password'] ?? 0) === 1;
$promptDismissed = intval($_SESSION['password_change_prompt_dismissed'] ?? 0) === 1;
$showPasswordReminder = $isStaff && $mustChangePassword && $promptDismissed;

$notifications = [];

if ($showPasswordReminder) {
    $notifications[] = [
        'label' => 'Change Password Now',
        'url' => '/lab_sync/index.php?controller=userController&action=user&forcePasswordChange=true',
    ];
}

$sessionNotifications = $_SESSION['navbar_notifications'] ?? [];
if (is_array($sessionNotifications)) {
    foreach ($sessionNotifications as $item) {
        if (!is_array($item)) {
            continue;
        }

        $label = trim((string)($item['label'] ?? ''));
        $url = trim((string)($item['url'] ?? ''));

        if ($label !== '' && $url !== '') {
            $notifications[] = [
                'label' => $label,
                'url' => $url,
            ];
        }
    }
}

$hasNotifications = !empty($notifications);
?>
<nav class="navbar">
            <div class="navbar-brand"><a href="index.php" style="text-decoration: none;"><img src="/lab_sync/public/assests/Labsync-3.png"><span style="color:#1f2b5b">Lab</span><span style="color:#3DBDEC">Sync</span></a></div>
            <ul class="navbar-menu">
                <li class="navbar-search-wrapper">
                    <form class="search-form" autocomplete="off" onsubmit="return false;">
                        <input class="search-input" id="navbar-search-input" type="text" placeholder="Search 🔎︎" autocomplete="off">
                        <div id="navbar-search-results" class="navbar-search-results" hidden></div>
                    </form>
                </li>
                <li class="navbar-notifications">
                    <button type="button" class="bell-link notification-toggle" aria-haspopup="true" aria-expanded="false" title="Notifications">
                        <img class="bell" src="/lab_sync/public/assests/bell.png" alt="Notifications">
                        <?php if ($hasNotifications): ?>
                            <span class="bell-reminder-dot" aria-hidden="true"></span>
                        <?php endif; ?>
                        <span class="sr-only">Open notifications</span>
                    </button>
                    <div class="notification-dropdown" hidden>
                        <?php if ($hasNotifications): ?>
                            <?php foreach ($notifications as $notification): ?>
                                <a class="notification-item" href="<?php echo htmlspecialchars($notification['url'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($notification['label'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="notification-empty">No new notifications</div>
                        <?php endif; ?>
                    </div>
                </li>
                <li>
                    <a href="/lab_sync/index.php?controller=Auth&action=logout">
                        <img class='bell' src='/lab_sync/public/assests/logout.png' alt='Logout Icon'>
                    </a>
                </li>
            </ul>
        </nav>
<script src="/lab_sync/public/js/navbarNotifications.js"></script>
<script src="/lab_sync/public/js/navbarSearch.js"></script>

        