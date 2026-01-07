<?php
// appointment_form.php
?>

<form id="createAppointmentForm" 
      method="post" 
      action="/lab_sync/index.php?controller=appointmentsController&action=storeAppointment" 
      class="appointment-form formStyle">
    
    <!-- Patient Search Section -->
    <label for="patient-search-by">
        Search patient by:
        <select id="patient-search-by" name="patient_search_by" required>
            <option value="email">Email</option>
            <option value="patient_name">Patient Name</option>
        </select>
    </label>

    <input type="text" 
           class="search-bar" 
           id="patient-search" 
           name="patient_search" 
           placeholder="Search patient..." 
           autocomplete="off">
    
    <input type="hidden" id="patient_id" name="patient_id" value="">
    <input type="hidden" id="method" name="method" value="physical">

    <!-- Appointment Details Section -->
    <label for="appointment-date">Appointment Date:</label>
    <input type="date" id="appointment-date" name="appointment_date" required>

    <label for="appointment-time">Appointment Time:</label>
    <input type="time" id="appointment-time" name="appointment_time" required>

    <label for="reason">Reason (optional):</label>
    <input type="text" id="reason" name="reason">

    <!-- Tests Section -->
    <div id="additional-tests">
        <div class="test-group">
            <label for="test-type-1">Test 1 type:</label>
            <select id="test-type-1" name="test-types[]" class="test-select" required>
                <option value="">Select Test Type</option>
                <option value="Blood Test">Blood Test</option>
                <option value="Urine Test">Urine Test</option>
                <option value="X-Ray">X-Ray</option>
                <option value="MRI">MRI</option>
                <option value="CT Scan">CT Scan</option>
                <option value="Other">Other</option>
            </select>
        </div>
    </div>

    <!-- Buttons Section -->
    <button type="button" id="add-test-button" class="add-button">
        + Add Another Test
    </button>
    
    <div class="button-group">
       <button type="button" id="cancel" class="cancel-btn">Cancel</button>
      <button type="submit" name="create_appointment" class="submit-button">Submit</button>  
    </div>

</form>