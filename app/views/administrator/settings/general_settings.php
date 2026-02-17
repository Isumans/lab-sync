<div id="general" class="section" style="display:none;">
    <form class="config-form">
        <!-- Notifications Section -->
        <div class="config-section">
            <div class="section-header">
                <span class="section-icon">🔔</span>
                <h3>Notifications</h3>
            </div>

            <div class="notification-items">
                <div class="notification-item">
                    <div class="notification-info">
                        <h4>SMS Alerts</h4>
                        <p>Send automated text notifications for critical test results.</p>
                    </div>
                    <label class="toggle">
                        <input type="checkbox" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <div class="notification-item">
                    <div class="notification-info">
                        <h4>Email Reports</h4>
                        <p>Weekly laboratory performance summaries and batch test reports.</p>
                    </div>
                    <label class="toggle">
                        <input type="checkbox" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Security & Access Section -->
        <div class="config-section">
            <div class="section-header">
                <span class="section-icon">🔒</span>
                <h3>Security & Access</h3>
            </div>

            <div class="config-row">
                <div class="config-group">
                    <label for="password-policy">Password Policy</label>
                    <p class="field-description">Force users to change password regularly.</p>
                    <select id="password-policy" name="password_policy">
                        <option>Every 30 Days</option>
                        <option selected>Every 60 Days</option>
                        <option>Every 90 Days</option>
                        <option>Every 180 Days</option>
                    </select>
                </div>

                <div class="config-group">
                    <label for="session-timeout">Session Timeout</label>
                    <p class="field-description">Inactivity period before automatic logout.</p>
                    <div class="input-with-unit">
                        <input type="number" id="session-timeout" name="session_timeout" value="15" min="1" max="480">
                        <span class="unit">Minutes</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Preferences Section -->
        <div class="config-section">
            <div class="section-header">
                <span class="section-icon">⚙️</span>
                <h3>System Preferences</h3>
            </div>

            <div class="config-row">
                <div class="config-group">
                    <label for="language">Default Language</label>
                    <select id="language" name="language">
                        <option selected>English (US)</option>
                        <option>English (UK)</option>
                        <option>Spanish</option>
                        <option>French</option>
                        <option>German</option>
                    </select>
                </div>

                <div class="config-group">
                    <label for="timezone">Laboratory Timezone</label>
                    <select id="timezone" name="timezone">
                        <option>(GMT-08:00) Pacific Time (US & Canada)</option>
                        <option>(GMT-07:00) Mountain Time (US & Canada)</option>
                        <option selected>(GMT-05:00) Eastern Time (US & Canada)</option>
                        <option>(GMT+00:00) GMT/UTC</option>
                        <option>(GMT+01:00) Central European Time</option>
                    </select>
                </div>
            </div>

            <div class="config-row">
                <div class="config-group">
                    <label for="currency">Currency Format</label>
                    <select id="currency" name="currency">
                        <option selected>USD ($)</option>
                        <option>EUR (€)</option>
                        <option>GBP (£)</option>
                        <option>CAD ($)</option>
                        <option>AUD ($)</option>
                    </select>
                </div>

                <div class="config-group">
                    <label>Date Format</label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="date_format" value="dd/mm/yyyy" checked>
                            <span>DD/MM/YYYY</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="date_format" value="mm/dd/yyyy">
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
</div>
