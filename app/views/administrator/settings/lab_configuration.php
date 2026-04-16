<?php
// $config is passed in from the controller
$c = $config ?? [];
$lab_name          = htmlspecialchars($c['lab_name']          ?? 'Metropolis Diagnostics Center');
$accreditation     = htmlspecialchars($c['accreditation']     ?? 'ISO-15189-2023');
$address           = htmlspecialchars($c['address']            ?? 'Suite 402, Medical Arts Building, Downtown Metro');
$phone             = htmlspecialchars($c['phone']              ?? '+1 (555) 123-4567');
$email             = htmlspecialchars($c['email']              ?? 'contact@metrodiagnostics.com');
$logo_path         = $c['logo_path']         ?? '';

$mf_open           = htmlspecialchars($c['hours_mon_fri_open']    ?? '08:00');
$mf_close          = htmlspecialchars($c['hours_mon_fri_close']   ?? '17:00');
$mf_enabled        = isset($c['hours_mon_fri_enabled']) ? (int)$c['hours_mon_fri_enabled'] : 1;

$sat_open          = htmlspecialchars($c['hours_sat_open']  ?? '09:00');
$sat_close         = htmlspecialchars($c['hours_sat_close'] ?? '14:00');
$sat_enabled       = isset($c['hours_sat_enabled']) ? (int)$c['hours_sat_enabled'] : 1;

$sun_open          = htmlspecialchars($c['hours_sun_open']  ?? '12:00');
$sun_close         = htmlspecialchars($c['hours_sun_close'] ?? '12:00');
$sun_enabled       = isset($c['hours_sun_enabled']) ? (int)$c['hours_sun_enabled'] : 0;

$allow_walkins     = isset($c['allow_walkins'])     ? (int)$c['allow_walkins']     : 1;
$auto_email        = isset($c['auto_email_reports']) ? (int)$c['auto_email_reports'] : 0;
?>

<div id="lab-config-msg" class="settings-msg" style="display:none;"></div>

<form class="config-form" id="lab-config-form" enctype="multipart/form-data">
    <input type="hidden" name="existing_logo_path" value="<?= $logo_path ?>">

    <!-- General Information Section -->
    <div class="config-section">
        <h3>General Information</h3>

        <div class="config-row">
            <div class="config-group">
                <label>Laboratory Logo</label>
                <div class="logo-upload">
                    <div class="logo-preview" id="logo-preview-wrap">
                        <?php if (!empty($logo_path)): ?>
                            <img id="logo-preview-img" src="<?= htmlspecialchars($logo_path) ?>" alt="Lab Logo" style="width:80px;height:80px;object-fit:cover;border-radius:8px;">
                        <?php else: ?>
                            <svg id="logo-preview-img" width="80" height="80" viewBox="0 0 80 80" fill="none">
                                <rect width="80" height="80" fill="#F0F0F0" rx="8"/>
                                <path d="M40 35C42.2091 35 44 33.2091 44 31C44 28.7909 42.2091 27 40 27C37.7909 27 36 28.7909 36 31C36 33.2091 37.7909 35 40 35Z" fill="#4A5568"/>
                                <path d="M40 37C34.4772 37 30 41.4772 30 47V53H50V47C50 41.4772 45.5228 37 40 37Z" fill="#4A5568"/>
                            </svg>
                        <?php endif; ?>
                    </div>
                    <div class="logo-upload-area">
                        <input type="file" id="logo-input" name="logo" accept="image/*" style="display:none;">
                        <label for="logo-input" class="upload-label">
                            <p>Click to upload or drag and drop</p>
                            <small>PNG, JPG (max 2MB)</small>
                        </label>
                    </div>
                </div>
            </div>

            <div class="config-column">
                <div class="config-group">
                    <label for="lab-name">Lab Official Name</label>
                    <input type="text" id="lab-name" name="lab_name" placeholder="Enter lab official name" value="<?= $lab_name ?>" maxlength="120" required>
                </div>

                <div class="config-group">
                    <label for="accreditation">Accreditation Number</label>
                    <input type="text" id="accreditation" name="accreditation" placeholder="Enter accreditation number" value="<?= $accreditation ?>" maxlength="80" required>
                </div>
            </div>
        </div>

        <div class="config-row">
            <div class="config-group full-width">
                <label for="address">Physical Address</label>
                <textarea id="address" name="address" placeholder="Enter physical address" rows="3" maxlength="255" required><?= $address ?></textarea>
            </div>
        </div>

        <div class="config-row">
            <div class="config-group">
                <label for="phone">Primary Phone</label>
                <input type="tel" id="phone" name="phone" placeholder="Enter primary phone" value="<?= $phone ?>" maxlength="25" pattern="^[0-9+()\-\s]{7,25}$" title="Use 7-25 characters: digits, space, plus, parentheses, or hyphen." required>
            </div>

            <div class="config-group">
                <label for="email">Official Email</label>
                <input type="email" id="email" name="email" placeholder="Enter official email" value="<?= $email ?>" maxlength="120" required>
            </div>
        </div>
    </div>

    <!-- Operational Hours Section -->
    <div class="config-section">
        <div class="operational-header">
            <h3>Operational Hours</h3>
        </div>

        <div class="operational-hours">
            <!-- Monday - Friday -->
            <div class="hours-row">
                <div class="day-label">Monday - Friday</div>
                <div class="time-inputs">
                    <div class="time-group">
                        <input type="time" name="hours_mon_fri_open" value="<?= $mf_open ?>" required>
                        <span class="time-to">to</span>
                        <input type="time" name="hours_mon_fri_close" value="<?= $mf_close ?>" required>
                    </div>
                    <label class="toggle">
                        <input type="checkbox" name="hours_mon_fri_enabled" <?= $mf_enabled ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="<?= $mf_enabled ? 'status-open' : 'status-closed' ?>"><?= $mf_enabled ? 'Open' : 'Closed' ?></span>
                </div>
            </div>

            <!-- Saturday -->
            <div class="hours-row">
                <div class="day-label">Saturday</div>
                <div class="time-inputs">
                    <div class="time-group">
                        <input type="time" name="hours_sat_open" value="<?= $sat_open ?>" required>
                        <span class="time-to">to</span>
                        <input type="time" name="hours_sat_close" value="<?= $sat_close ?>" required>
                    </div>
                    <label class="toggle">
                        <input type="checkbox" name="hours_sat_enabled" <?= $sat_enabled ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="<?= $sat_enabled ? 'status-open' : 'status-closed' ?>"><?= $sat_enabled ? 'Open' : 'Closed' ?></span>
                </div>
            </div>

            <!-- Sunday -->
            <div class="hours-row">
                <div class="day-label">Sunday</div>
                <div class="time-inputs">
                    <div class="time-group">
                        <input type="time" name="hours_sun_open" value="<?= $sun_open ?>" required>
                        <span class="time-to">to</span>
                        <input type="time" name="hours_sun_close" value="<?= $sun_close ?>" required>
                    </div>
                    <label class="toggle">
                        <input type="checkbox" name="hours_sun_enabled" <?= $sun_enabled ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="<?= $sun_enabled ? 'status-open' : 'status-closed' ?>"><?= $sun_enabled ? 'Open' : 'Closed' ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="config-section">
        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 10c0 7-3 10-8 10S4 17 4 10 7 0 12 0s8 3 8 10z"></path>
                    </svg>
                </div>
                <div class="feature-info">
                    <h4>Allow Walk-ins</h4>
                    <p>Patients can visit without appointment</p>
                </div>
                <label class="toggle">
                    <input type="checkbox" name="allow_walkins" <?= $allow_walkins ? 'checked' : '' ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>

            <div class="feature-item">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
                <div class="feature-info">
                    <h4>Auto-Email Reports</h4>
                    <p>Send results once verified</p>
                </div>
                <label class="toggle">
                    <input type="checkbox" name="auto_email_reports" <?= $auto_email ? 'checked' : '' ?>>
                    <span class="toggle-slider"></span>
                </label>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="config-actions">
        <button type="submit" class="save-btn">Save Changes</button>
        <button type="reset" class="cancel-btn">Cancel</button>
    </div>
</form>
