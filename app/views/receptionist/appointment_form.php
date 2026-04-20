<?php
// appointment_form.php
?>

<form id="createAppointmentForm" 
      method="post" 
      action="/lab_sync/index.php?controller=appointmentsController&action=storeAppointment" 
      class="appointment-form-container">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)($csrfToken ?? ($_SESSION['csrf_token'] ?? ''))); ?>">
    
    <div class="appointment-form-wrapper">
        <!-- Left Column -->
        <div class="appointment-left-column">
            
            <!-- Patient Search Section -->
            <div class="form-section patient-search-section">
                <div class="section-header">
                    <h3>Patient Search</h3>
                </div>
                
                <div class="search-input-group">
                    <label for="patient-search-by" class="search-label">Search by:</label>
                    <select id="patient-search-by" name="patient_search_by" class="search-select">
                        <option value="patient_name">Patient Name</option>
                        <option value="email">Email Address</option>
                    </select>
                </div>

                <input type="text" 
                       class="patient-search-input" 
                       id="patient-search" 
                       name="patient_search" 
                       placeholder="Start typing patient name (e.g. sanju samson)..." 
                       autocomplete="off">
                
                <div id="patient-suggestions" class="patient-suggestions"></div>
                
                <input type="hidden" id="patient_id" name="patient_id" value="">
                <input type="hidden" id="method" name="method" value="physical">

                <!-- Recent Patients Display -->
                <div class="recent-patients" id="recentPatientsContainer" style="display: none;">
                    <p class="recent-label">RECENT</p>
                    <div id="recentPatientsList" class="patients-list"></div>
                </div>

                <!-- Create New Patient Button -->
                <button type="button" id="create-new-patient-trigger" class="create-new-patient-btn">
                    + Create New Patient
                </button>
            </div>

            <!-- Test Selection Section -->
            <div class="form-section test-selection-section">
                <div class="section-header">
                    <h3>Test Selection</h3>
                </div>

                <div class="test-search-group">
                    <div class="test-search-wrapper">
                        <span class="search-icon">🔍</span>
                        <input type="text"
                               id="test-catalog-search"
                               class="test-catalog-search"
                               placeholder="Search by Test Name or ID (e.g. CBC, Metabolic...)"
                               autocomplete="off">
                    </div>
                    <p id="test-search-hint" class="test-search-validation-msg" style="display:none;"></p>
                    <label class="search-results-label">SEARCH RESULTS</label>
                </div>

                <!-- Test Catalog Results -->
                <div id="testCatalogResults" class="test-catalog-results">
                    <ul id="testCatalogTableBody" class="test-results-list">
                        <!-- Tests loaded via JS -->
                    </ul>
                </div>

                <!-- Selected Tests List -->
                <div id="selectedTestsList" class="selected-tests-list" style="display: none;">
                    <div class="selected-tests-header">
                        <h4>Selected Tests</h4>
                        <span class="selected-tests-badge">SELECTED: <span id="selectedTestsCount">0</span></span>
                    </div>
                    <table class="selected-tests-table">
                        <thead>
                            <tr>
                                <th class="col-test-id">TEST ID</th>
                                <th class="col-test-name">TEST NAME</th>
                                <th class="col-category">CATEGORY</th>
                                <th class="col-action"></th>
                            </tr>
                        </thead>
                        <tbody id="selectedTestsTableBody"></tbody>
                    </table>
                </div>

                <!-- Hidden input to store selected test IDs -->
                <input type="hidden" id="selected_test_ids" name="selected_test_ids" value="">
            </div>
        </div>

        <!-- Right Column -->
        <div class="appointment-right-column">
            
            <!-- Schedule Details Section -->
            <div class="form-section schedule-details-section">
                <div class="section-header">
                    <h3>Schedule Details</h3>
                </div>

                <div class="date-time-group">
                    <label for="appointment-date" class="schedule-label">APPOINTMENT DATE</label>
                    <button type="button" id="set-today-date" class="date-badge">TODAY</button>
                    <input type="date" id="appointment-date" name="appointment_date" class="date-input" required>
                </div>

                <div class="time-slots-group">
                    <label class="schedule-label">AVAILABLE TIME SLOTS</label>
                    <div class="time-slots-container">
                        <div class="time-slot-column">
                            <button type="button" class="time-slot">NOW</button>
                            <button type="button" class="time-slot">08:00 AM</button>
                            <button type="button" class="time-slot">01:30 PM</button>
                            <button type="button" class="time-slot">04:00 PM</button>
                        </div>
                        <div class="time-slot-column">
                            <button type="button" class="time-slot">08:30 AM</button>
                            <button type="button" class="time-slot active">11:00 AM</button>
                            <button type="button" class="time-slot">02:45 PM</button>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="appointment-time" name="appointment_time" value="">

                <div class="schedule-details-group">
                    <div class="detail-row">
                        <span class="detail-label">Selected Date</span>
                        <span id="selected-appointment-date" class="detail-value">Not selected</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Selected Time</span>
                        <span id="selected-appointment-time" class="detail-value">Not selected</span>
                    </div>
                </div>
            </div>

            <!-- Clinical Notes -->
            <div class="form-section clinical-notes-section">
                <label for="reason" class="reason-label">CLINICAL NOTES (OPTIONAL)</label>
                <textarea id="reason" 
                          name="reason" 
                          class="reason-textarea" 
                          placeholder="Enter specific symptoms or physician instructions..."></textarea>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <button type="button" id="cancel" class="cancel-button">Cancel</button>
                <button type="submit" name="create_appointment" class="submit-button">Schedule Appointment</button>  
            </div>
        </div>
    </div>

</form>

<div id="createPatientModal" class="appointment-edit-modal create-patient-modal" aria-hidden="true">
    <div class="appointment-edit-dialog create-patient-dialog" role="dialog" aria-modal="true" aria-labelledby="createPatientTitle">
        <form id="createPatientModalForm" novalidate>
            <input type="hidden" id="createPatientCsrf" name="csrf_token" value="<?php echo htmlspecialchars((string)($csrfToken ?? ($_SESSION['csrf_token'] ?? ''))); ?>">

            <div class="appointment-edit-header">
                <div>
                    <h2 id="createPatientTitle">Create New Patient</h2>
                    <p class="appointment-edit-subtitle">REGISTER PATIENT FOR APPOINTMENT</p>
                </div>
                <button id="createPatientClose" type="button" class="appointment-edit-close" aria-label="Close create patient modal">&times;</button>
            </div>

            <div id="createPatientAlert" class="appointment-edit-alert" hidden></div>

            <div class="appointment-edit-body">
                <section class="edit-section-card">
                    <div class="edit-section-title">
                        <span class="section-icon" aria-hidden="true"></span>
                        <h3>Patient Basic Information</h3>
                    </div>

                    <div class="create-patient-grid">
                        <div class="create-patient-field create-patient-field-full">
                            <label class="edit-label" for="createPatientName">Patient Name</label>
                            <input type="text" id="createPatientName" name="patient_name" maxlength="120" autocomplete="off" required>
                            <p class="create-patient-field-error" data-field-error="patient_name"></p>
                        </div>

                        <div class="create-patient-field">
                            <label class="edit-label" for="createPatientDob">Date of Birth</label>
                            <input type="date" id="createPatientDob" name="date_of_birth" required>
                            <p class="create-patient-field-error" data-field-error="date_of_birth"></p>
                        </div>

                        <div class="create-patient-field">
                            <label class="edit-label" for="createPatientGender">Gender</label>
                            <select id="createPatientGender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                            <p class="create-patient-field-error" data-field-error="gender"></p>
                        </div>

                        <div class="create-patient-field">
                            <label class="edit-label" for="createPatientContact">Contact Number</label>
                            <input type="tel" id="createPatientContact" name="contact_no" maxlength="25" pattern="^[0-9+()\-\s]{7,25}$" autocomplete="off" required>
                            <p class="create-patient-field-error" data-field-error="contact_no"></p>
                        </div>

                        <div class="create-patient-field">
                            <label class="edit-label" for="createPatientEmail">Email Address</label>
                            <input type="email" id="createPatientEmail" name="email" maxlength="120" autocomplete="off" required>
                            <p class="create-patient-field-error" data-field-error="email"></p>
                        </div>
                    </div>
                </section>
            </div>

            <div class="appointment-edit-footer">
                <button type="button" id="createPatientCancel" class="edit-cancel-btn">Cancel</button>
                <button type="submit" id="createPatientSubmit" class="edit-submit-btn">
                    <span aria-hidden="true"></span>
                    Create Patient
                </button>
            </div>
        </form>
    </div>
</div>
