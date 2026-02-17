<div id="configuration" class="section" style="display:none;">
    <form class="config-form">
        <!-- General Information Section -->
        <div class="config-section">
            <h3>General Information</h3>
            
            <div class="config-row">
                <div class="config-group">
                    <label>Laboratory Logo</label>
                    <div class="logo-upload">
                        <div class="logo-preview">
                            <svg width="80" height="80" viewBox="0 0 80 80" fill="none">
                                <rect width="80" height="80" fill="#F0F0F0" rx="8"/>
                                <path d="M40 35C42.2091 35 44 33.2091 44 31C44 28.7909 42.2091 27 40 27C37.7909 27 36 28.7909 36 31C36 33.2091 37.7909 35 40 35Z" fill="#4A5568"/>
                                <path d="M40 37C34.4772 37 30 41.4772 30 47V53H50V47C50 41.4772 45.5228 37 40 37Z" fill="#4A5568"/>
                            </svg>
                        </div>
                        <div class="logo-upload-area">
                            <input type="file" id="logo-input" accept="image/*" style="display:none;">
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
                        <input type="text" id="lab-name" name="lab_name" placeholder="Enter lab official name" value="Metropolis Diagnostics Center">
                    </div>

                    <div class="config-group">
                        <label for="accreditation">Accreditation Number</label>
                        <input type="text" id="accreditation" name="accreditation" placeholder="Enter accreditation number" value="ISO-15189-2023">
                    </div>
                </div>
            </div>

            <div class="config-row">
                <div class="config-group full-width">
                    <label for="address">Physical Address</label>
                    <textarea id="address" name="address" placeholder="Enter physical address" rows="3">Suite 402, Medical Arts Building, Downtown Metro</textarea>
                </div>
            </div>

            <div class="config-row">
                <div class="config-group">
                    <label for="phone">Primary Phone</label>
                    <input type="tel" id="phone" name="phone" placeholder="Enter primary phone" value="+1 (555) 123-4567">
                </div>

                <div class="config-group">
                    <label for="email">Official Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter official email" value="contact@metrodiagnostics.com">
                </div>
            </div>
        </div>

        <!-- Operational Hours Section -->
        <div class="config-section">
            <div class="operational-header">
                <h3>Operational Hours</h3>
                <a href="#" class="apply-all-btn">💬 Apply to all days</a>
            </div>

            <div class="operational-hours">
                <div class="hours-row">
                    <div class="day-label">Monday - Friday</div>
                    <div class="time-inputs">
                        <div class="time-group">
                            <input type="time" value="08:00">
                            <span class="time-to">to</span>
                            <input type="time" value="08:00">
                        </div>
                        <label class="toggle">
                            <input type="checkbox" checked>
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="status-open">Open</span>
                    </div>
                </div>

                <div class="hours-row">
                    <div class="day-label">Saturday</div>
                    <div class="time-inputs">
                        <div class="time-group">
                            <input type="time" value="09:00">
                            <span class="time-to">to</span>
                            <input type="time" value="04:00">
                        </div>
                        <label class="toggle">
                            <input type="checkbox" checked>
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="status-open">Open</span>
                    </div>
                </div>

                <div class="hours-row">
                    <div class="day-label">Sunday</div>
                    <div class="time-inputs">
                        <div class="time-group">
                            <input type="time" value="12:00">
                            <span class="time-to">to</span>
                            <input type="time" value="12:00">
                        </div>
                        <label class="toggle">
                            <input type="checkbox">
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="status-closed">Closed</span>
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
                        <input type="checkbox" checked>
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
                        <p>Send results since verified</p>
                    </div>
                    <label class="toggle">
                        <input type="checkbox">
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
</div>
