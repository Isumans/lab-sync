<?php

if (!isset($_SESSION['user_id'])) {
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}

$profileData = is_array($profileData ?? null) ? $profileData : [];
$activeSessions = is_array($activeSessions ?? null) ? $activeSessions : [];
$csrfToken = (string)($csrfToken ?? ($_SESSION['csrf_token'] ?? ''));

function profileEsc($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$fullName = trim((string)($profileData['full_name'] ?? $profileData['username'] ?? 'Profile User'));
$nameParts = preg_split('/\s+/', $fullName);
$initials = '';
foreach ($nameParts as $part) {
    if ($part !== '' && strlen($initials) < 2) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
}
if ($initials === '') {
    $initials = 'PU';
}

$flash = $_SESSION['flash'] ?? null;
if ($flash) {
    unset($_SESSION['flash']);
}

$roleLabel = ucfirst((string)($profileData['role'] ?? 'staff'));
$isActive = ((string)($profileData['status'] ?? 'active')) === 'active';
$emailNotifications = intval($profileData['email_notifications'] ?? 1) === 1;
$smsAlerts = intval($profileData['sms_alerts'] ?? 0) === 1;
$twofaEnabled = intval($profileData['twofa_enabled'] ?? 0) === 1;
$themeMode = (string)($profileData['theme_mode'] ?? 'System');
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/profileStaff.css">
</head>
<body>
    <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>

    <div class="container">
        <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

        <main class="main-content">
            <section class="staff-profile" aria-label="User Profile">
                <?php
                    $pageTitle = 'User Profile';
                    $pageBreadcrumbText = 'User Profile->';
                    require __DIR__ . '/../../public/partials/page-header.php';
                ?>

                <?php if (is_array($flash)): ?>
                    <?php $flashType = profileEsc($flash['type'] ?? 'info'); ?>
                    <div class="profile-flash profile-flash-<?php echo $flashType; ?>">
                        <?php echo profileEsc($flash['message'] ?? 'Update complete.'); ?>
                    </div>
                <?php endif; ?>

                <section class="profile-hero card-shell">
                    <div class="hero-left">
                        <div class="hero-avatar" aria-hidden="true"><?php echo profileEsc($initials); ?></div>
                        <div>
                            <h2 class="hero-name"><?php echo profileEsc($fullName); ?></h2>
                            <p class="hero-role"><?php echo profileEsc($roleLabel); ?> - Clinical Diagnostics Unit</p>
                            <span class="hero-status <?php echo $isActive ? 'is-active' : 'is-inactive'; ?>">
                                <?php echo $isActive ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                    </div>

                    <div class="hero-actions">
                        <button id="openProfileEditModal" type="button" class="btn btn-soft">Edit Profile</button>
                        <button id="openPasswordModal" type="button" class="btn btn-outline">Change Password</button>
                    </div>
                </section>

                <div class="profile-grid">
                    <section class="card-shell" id="personal-info">
                        <div class="section-head">
                            <h3>Personal Information</h3>
                        </div>

                        <div class="profile-readonly-grid" aria-label="Personal Information Details">
                            <div class="readonly-item">
                                <span class="readonly-label">Full Name</span>
                                <p class="readonly-value"><?php echo profileEsc($fullName); ?></p>
                            </div>
                            <div class="readonly-item">
                                <span class="readonly-label">Email Address</span>
                                <p class="readonly-value"><?php echo profileEsc($profileData['email'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="readonly-item">
                                <span class="readonly-label">Phone Number</span>
                                <p class="readonly-value"><?php echo profileEsc($profileData['contact_number'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="readonly-item">
                                <span class="readonly-label">Date of Birth</span>
                                <p class="readonly-value"><?php echo profileEsc($profileData['date_of_birth'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="readonly-item">
                                <span class="readonly-label">Gender</span>
                                <p class="readonly-value"><?php echo profileEsc($profileData['gender'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="readonly-item readonly-item-wide">
                                <span class="readonly-label">Residential Address</span>
                                <p class="readonly-value"><?php echo profileEsc($profileData['residential_address'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                    </section>

                    <section class="card-shell" id="account-security">
                        <div class="section-head">
                            <h3>Account Security</h3>
                        </div>

                        <form method="POST" action="/lab_sync/index.php?controller=userController&action=toggleTwoFactor" class="security-box">
                            <input type="hidden" name="csrf_token" value="<?php echo profileEsc($csrfToken); ?>">
                            <div>
                                <h4>Two-Factor Authentication</h4>
                                <p>Add an extra layer of account security.</p>
                                <span class="recommend-pill">Recommended</span>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="enable_2fa" value="1" <?php echo $twofaEnabled ? 'checked' : ''; ?> onchange="this.form.submit()">
                                <span class="slider"></span>
                            </label>
                        </form>

                        <h4 class="sub-title">Active Sessions</h4>
                        <div class="session-list">
                            <?php if (count($activeSessions) === 0): ?>
                                <p class="muted-note">No active session records yet. Log in again after running migration to track sessions.</p>
                            <?php else: ?>
                                <?php foreach ($activeSessions as $sessionRow): ?>
                                    <?php
                                        $isCurrentSession = ((string)($sessionRow['session_token'] ?? '') === (string)($_SESSION['session_token'] ?? ''));
                                        $deviceLabel = (string)($sessionRow['device_label'] ?? 'Unknown Device');
                                        $ipAddress = (string)($sessionRow['ip_address'] ?? 'N/A');
                                        $lastSeen = (string)($sessionRow['last_activity'] ?? '');
                                    ?>
                                    <div class="session-item">
                                        <div>
                                            <p class="session-device"><?php echo profileEsc($deviceLabel); ?><?php echo $isCurrentSession ? ' (Current)' : ''; ?></p>
                                            <p class="session-meta"><?php echo profileEsc($ipAddress); ?> - Last seen <?php echo profileEsc($lastSeen); ?></p>
                                        </div>
                                        <?php if ($isCurrentSession): ?>
                                            <span class="current-tag">Current</span>
                                        <?php else: ?>
                                            <form method="POST" action="/lab_sync/index.php?controller=userController&action=revokeSession">
                                                <input type="hidden" name="csrf_token" value="<?php echo profileEsc($csrfToken); ?>">
                                                <input type="hidden" name="user_session_id" value="<?php echo intval($sessionRow['user_session_id'] ?? 0); ?>">
                                                <button type="submit" class="btn btn-outline-danger">Logout</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>

                    <section class="card-shell" id="notification-preferences">
                        <div class="section-head">
                            <h3>Notification Preferences</h3>
                        </div>

                        <form method="POST" action="/lab_sync/index.php?controller=userController&action=updatePreferences" class="stack-form compact">
                            <input type="hidden" name="csrf_token" value="<?php echo profileEsc($csrfToken); ?>">

                            <div class="preference-row">
                                <div>
                                    <p class="pref-title">Email Notifications</p>
                                    <p class="pref-sub">Receive weekly lab summaries and critical alerts.</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="email_notifications" value="1" <?php echo $emailNotifications ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="preference-row">
                                <div>
                                    <p class="pref-title">SMS Alerts</p>
                                    <p class="pref-sub">Immediate text alerts for urgent results.</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="sms_alerts" value="1" <?php echo $smsAlerts ? 'checked' : ''; ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>

                            <div class="grid-two">
                                <label>
                                    <span>Quiet Hours Start</span>
                                    <input type="time" name="quiet_hours_start" value="<?php echo profileEsc(substr((string)($profileData['quiet_hours_start'] ?? '22:00:00'), 0, 5)); ?>">
                                </label>
                                <label>
                                    <span>Quiet Hours End</span>
                                    <input type="time" name="quiet_hours_end" value="<?php echo profileEsc(substr((string)($profileData['quiet_hours_end'] ?? '07:00:00'), 0, 5)); ?>">
                                </label>
                            </div>

                            <div class="section-head section-head-spaced">
                                <h3>Display Preferences</h3>
                            </div>
                            <div class="theme-toggle-grid">
                                <label class="theme-radio">
                                    <input type="radio" name="theme_mode" value="Light" <?php echo $themeMode === 'Light' ? 'checked' : ''; ?>>
                                    <span>Light</span>
                                </label>
                                <label class="theme-radio">
                                    <input type="radio" name="theme_mode" value="Dark" <?php echo $themeMode === 'Dark' ? 'checked' : ''; ?>>
                                    <span>Dark</span>
                                </label>
                                <label class="theme-radio">
                                    <input type="radio" name="theme_mode" value="System" <?php echo $themeMode === 'System' ? 'checked' : ''; ?>>
                                    <span>System</span>
                                </label>
                            </div>

                            <div class="form-row-end">
                                <button type="submit" class="btn btn-primary">Save Preference Settings</button>
                            </div>
                        </form>
                    </section>
                </div>

                <div id="profileEditModal" class="profile-edit-modal" aria-hidden="true">
                    <div class="profile-edit-dialog" role="dialog" aria-modal="true" aria-labelledby="profileEditTitle">
                        <form id="profileEditForm" method="POST" action="/lab_sync/index.php?controller=userController&action=saveProfile" novalidate>
                            <input type="hidden" name="csrf_token" value="<?php echo profileEsc($csrfToken); ?>">

                            <div class="profile-edit-header">
                                <div>
                                    <h2 id="profileEditTitle">Edit Profile</h2>
                                    <p class="profile-edit-subtitle">PERSONAL INFORMATION UPDATE</p>
                                </div>
                                <button id="profileEditClose" type="button" class="profile-edit-close" aria-label="Close edit profile modal">&times;</button>
                            </div>

                            <div id="profileEditAlert" class="profile-edit-alert" hidden></div>

                            <div class="profile-edit-body">
                                <section class="profile-edit-section-card">
                                    <div class="profile-edit-section-title">
                                        <h3>Profile Details</h3>
                                    </div>
                                    <div class="profile-edit-grid">
                                        <label>
                                            <span>Full Name</span>
                                            <input type="text" name="full_name" value="<?php echo profileEsc($fullName); ?>" required>
                                        </label>
                                        <label>
                                            <span>Email Address</span>
                                            <input type="email" name="email" value="<?php echo profileEsc($profileData['email'] ?? ''); ?>" readonly aria-readonly="true">
                                        </label>
                                    </div>
                                </section>

                                <section class="profile-edit-section-card">
                                    <div class="profile-edit-section-title">
                                        <h3>Contact and Demographics</h3>
                                    </div>
                                    <div class="profile-edit-grid profile-edit-grid-three">
                                        <label>
                                            <span>Phone Number</span>
                                            <input type="text" name="contact_number" value="<?php echo profileEsc($profileData['contact_number'] ?? ''); ?>" placeholder="+1 (555) 000-0000">
                                        </label>
                                        <label>
                                            <span>Date of Birth</span>
                                            <input type="date" name="date_of_birth" value="<?php echo profileEsc($profileData['date_of_birth'] ?? ''); ?>">
                                        </label>
                                        <label>
                                            <span>Gender</span>
                                            <select name="gender">
                                                <option value="">Select</option>
                                                <option value="Male" <?php echo (($profileData['gender'] ?? '') === 'Male') ? 'selected' : ''; ?>>Male</option>
                                                <option value="Female" <?php echo (($profileData['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>>Female</option>
                                                <option value="Other" <?php echo (($profileData['gender'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
                                            </select>
                                        </label>
                                    </div>
                                </section>

                                <section class="profile-edit-section-card">
                                    <div class="profile-edit-section-title">
                                        <h3>Residential Address</h3>
                                    </div>
                                    <label>
                                        <span>Address</span>
                                        <textarea name="residential_address" rows="4" placeholder="Enter address"><?php echo profileEsc($profileData['residential_address'] ?? ''); ?></textarea>
                                    </label>
                                </section>
                            </div>

                            <div class="profile-edit-footer">
                                <button id="profileEditCancel" type="button" class="profile-edit-cancel-btn">Cancel</button>
                                <button id="profileEditSubmit" type="submit" class="profile-edit-submit-btn">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="passwordChangeModal" class="profile-edit-modal" aria-hidden="true">
                    <div class="profile-edit-dialog profile-password-dialog" role="dialog" aria-modal="true" aria-labelledby="passwordChangeTitle">
                        <form id="passwordChangeForm" method="POST" action="/lab_sync/index.php?controller=userController&action=changePassword" novalidate>
                            <input type="hidden" name="csrf_token" value="<?php echo profileEsc($csrfToken); ?>">

                            <div class="profile-edit-header">
                                <div>
                                    <h2 id="passwordChangeTitle">Change Password</h2>
                                    <p class="profile-edit-subtitle">ACCOUNT SECURITY UPDATE</p>
                                </div>
                                <button id="passwordChangeClose" type="button" class="profile-edit-close" aria-label="Close password modal">&times;</button>
                            </div>

                            <div id="passwordChangeAlert" class="profile-edit-alert" hidden></div>

                            <div class="profile-edit-body">
                                <section class="profile-edit-section-card">
                                    <div class="profile-edit-section-title">
                                        <h3>Password Credentials</h3>
                                    </div>
                                    <div class="profile-edit-grid">
                                        <label>
                                            <span>Current Password</span>
                                            <input type="password" name="current_password" required>
                                        </label>
                                        <label>
                                            <span>New Password</span>
                                            <input type="password" name="new_password" minlength="8" required>
                                        </label>
                                        <label>
                                            <span>Confirm New Password</span>
                                            <input type="password" name="confirm_password" minlength="8" required>
                                        </label>
                                    </div>
                                </section>
                            </div>

                            <div class="profile-edit-footer">
                                <button id="passwordChangeCancel" type="button" class="profile-edit-cancel-btn">Cancel</button>
                                <button id="passwordChangeSubmit" type="submit" class="profile-edit-submit-btn">Update Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script src="/lab_sync/public/js/userProfileModal.js"></script>
</body>
</html>