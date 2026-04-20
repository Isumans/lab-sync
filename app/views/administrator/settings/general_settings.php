<?php
// $settings is passed in from the controller
$s = $settings ?? [];

$sms_alerts      = isset($s['sms_alerts'])      ? (int)$s['sms_alerts']      : 1;
$email_reports   = isset($s['email_reports'])   ? (int)$s['email_reports']   : 1;
$password_policy = $s['password_policy']        ?? '60';
$session_timeout = isset($s['session_timeout']) ? (int)$s['session_timeout'] : 15;
$language        = $s['language']               ?? 'en_US';
$timezone        = $s['timezone']               ?? 'America/New_York';
$currency        = $s['currency']               ?? 'LKR';
$date_format     = $s['date_format']            ?? 'dd/mm/yyyy';

// Helpers
function sel($current, $val) { return $current === $val ? 'selected' : ''; }
function chk($val) { return $val ? 'checked' : ''; }
?>

<div id="general-settings-msg" class="settings-msg" style="display:none;"></div>

<form class="config-form" id="general-settings-form">
    <!-- Notifications Section -->
    <div class="config-section">
        <div class="section-header">
            <span class="section-icon"></span>
            <h3>Notifications</h3>
        </div>

        <div class="notification-items">
            <div class="notification-item">
                <div class="notification-info">
                    <h4>SMS Alerts</h4>
                    <p>Send automated text notifications for critical test results.</p>
                </div>
                <label class="toggle">
                    <input type="checkbox" name="sms_alerts" <?= chk($sms_alerts) ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>

            <div class="notification-item">
                <div class="notification-info">
                    <h4>Email Reports</h4>
                    <p>Weekly laboratory performance summaries and batch test reports.</p>
                </div>
                <label class="toggle">
                    <input type="checkbox" name="email_reports" <?= chk($email_reports) ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>
    </div>

    <!-- Security & Access Section -->
    <div class="config-section">
        <div class="section-header">
            <span class="section-icon"></span>
            <h3>Security &amp; Access</h3>
        </div>

        <div class="config-row">
            <div class="config-group">
                <label for="password-policy">Password Policy</label>
                <p class="field-description">Force users to change password regularly.</p>
                <select id="password-policy" name="password_policy">
                    <option value="30"  <?= sel($password_policy, '30') ?>>Every 30 Days</option>
                    <option value="60"  <?= sel($password_policy, '60') ?>>Every 60 Days</option>
                    <option value="90"  <?= sel($password_policy, '90') ?>>Every 90 Days</option>
                    <option value="180" <?= sel($password_policy, '180') ?>>Every 180 Days</option>
                </select>
            </div>

            <div class="config-group">
                <label for="session-timeout">Session Timeout</label>
                <p class="field-description">Inactivity period before automatic logout.</p>
                <div class="input-with-unit">
                    <input type="number" id="session-timeout" name="session_timeout"
                           value="<?= $session_timeout ?>" min="1" max="480">
                    <span class="unit">Minutes</span>
                </div>
            </div>
        </div>
    </div>

    <!-- System Preferences Section -->
    <div class="config-section">
        <div class="section-header">
            <span class="section-icon"></span>
            <h3>System Preferences</h3>
        </div>

        <div class="config-row">
            <div class="config-group">
                <label for="language">Default Language</label>
                <select id="language" name="language">
                    <option value="en_US" <?= sel($language, 'en_US') ?>>English (US)</option>
                    <option value="en_GB" <?= sel($language, 'en_GB') ?>>English (UK)</option>
                    <option value="es"    <?= sel($language, 'es')    ?>>Spanish</option>
                    <option value="fr"    <?= sel($language, 'fr')    ?>>French</option>
                    <option value="de"    <?= sel($language, 'de')    ?>>German</option>
                </select>
            </div>

            <div class="config-group">
                <label for="timezone">Laboratory Timezone</label>
                <select id="timezone" name="timezone">
                    <option value="America/Los_Angeles"     <?= sel($timezone, 'America/Los_Angeles')     ?>>(GMT-08:00) Pacific Time (US &amp; Canada)</option>
                    <option value="America/Denver"          <?= sel($timezone, 'America/Denver')          ?>>(GMT-07:00) Mountain Time (US &amp; Canada)</option>
                    <option value="America/New_York"        <?= sel($timezone, 'America/New_York')        ?>>(GMT-05:00) Eastern Time (US &amp; Canada)</option>
                    <option value="UTC"                     <?= sel($timezone, 'UTC')                     ?>>(GMT+00:00) GMT/UTC</option>
                    <option value="Europe/Paris"            <?= sel($timezone, 'Europe/Paris')            ?>>(GMT+01:00) Central European Time</option>
                    <option value="Asia/Kolkata"            <?= sel($timezone, 'Asia/Kolkata')            ?>>(GMT+05:30) India Standard Time</option>
                </select>
            </div>
        </div>

        <div class="config-row">
            <div class="config-group">
                <label for="currency">Currency Format</label>
                <select id="currency" name="currency">
                    <option value="LKR" <?= sel($currency, 'LKR') ?>>LKR</option>
                </select>
            </div>

            <div class="config-group">
                <label>Date Format</label>
                <div class="radio-group">
                    <label class="radio-option">
                        <input type="radio" name="date_format" value="dd/mm/yyyy"
                               <?= $date_format === 'dd/mm/yyyy' ? 'checked' : '' ?>>
                        <span>DD/MM/YYYY</span>
                    </label>
                    <label class="radio-option">
                        <input type="radio" name="date_format" value="mm/dd/yyyy"
                               <?= $date_format === 'mm/dd/yyyy' ? 'checked' : '' ?>>
                        <span>MM/DD/YYYY</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="config-actions">
        <button type="reset" class="cancel-btn">Cancel</button>
        <button type="submit" class="save-btn">Save Changes</button>
    </div>
</form>
