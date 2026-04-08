<?php
// appointment_form.php
?>

<form id="createAppointmentForm" 
      method="post" 
      action="/lab_sync/index.php?controller=appointmentsController&action=storeAppointment" 
      class="appointment-form-container">
    
    <div class="appointment-form-wrapper">
        <!-- Left Column -->
        <div class="appointment-left-column">
            
            <!-- Patient Search Section -->
            <div class="form-section patient-search-section">
                <div class="section-header">
                    <span class="section-icon">👥</span>
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
                       placeholder="Start typing patient name (e.g. John Doe)..." 
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
                <button type="button" class="create-new-patient-btn">
                    + Create New Patient
                </button>
            </div>

            <!-- Test Selection Section -->
            <div class="form-section test-selection-section">
                <div class="section-header">
                    <span class="section-icon">🧪</span>
                    <h3>Test Selection</h3>
                    <span class="selected-tests-badge">SELECTED TESTS: <span id="selectedTestsCount">0</span></span>
                </div>

                <!-- Search Laboratory Catalog -->
                <div class="test-search-group">
                    <label class="test-label">SEARCH LABORATORY CATALOG</label>
                    <div class="test-search-wrapper">
                        <span class="search-icon">🔍</span>
                        <input type="text" 
                               id="test-catalog-search" 
                               class="test-catalog-search" 
                               placeholder="Find tests by name, code, or category (e.g. 'Glucose', 'L002')..."
                               autocomplete="off">
                    </div>
                </div>

                <div class="prominent-tests-section">
                    <div class="prominent-tests-header">
                        <h4>Latest 3 Tests</h4>
                    </div>
                    <div id="prominentTestsCards" class="prominent-tests-cards">
                        <div class="empty-state">Loading latest tests...</div>
                    </div>
                </div>

                <!-- Test Catalog Results -->
                <div id="testCatalogResults" class="test-catalog-results">
                    <table class="test-catalog-table">
                        <thead>
                            <tr>
                                <th class="col-test-id">TEST ID</th>
                                <th class="col-test-name">TEST NAME</th>
                                <th class="col-category">CATEGORY</th>
                                <th class="col-action"></th>
                            </tr>
                        </thead>
                        <tbody id="testCatalogTableBody">
                            <!-- Tests will be loaded here -->
                        </tbody>
                    </table>
                </div>

                <!-- Selected Tests List -->
                <div id="selectedTestsList" class="selected-tests-list" style="display: none;">
                    <div class="selected-tests-header">
                        <h4>Selected Tests</h4>
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
                        <tbody id="selectedTestsTableBody">
                            <!-- Selected tests will be shown here -->
                        </tbody>
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
                    <span class="section-icon">📅</span>
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
