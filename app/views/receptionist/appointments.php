<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
?>
<html>
<head>

    <title>Document</title>
</head>
    <title>Appointments</title>
        <link rel="stylesheet" href="/lab_sync/public/styles.css">
        <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
        <link rel="stylesheet" href="/lab_sync/public/table.css">
</head>
    <body>
        <!-- Navigation Bar -->
        <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
        <div class="container">
            <!-- Sidebar -->
            <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

            <!-- Main Body Section -->
            <main class="main-content">
                <div class="Tmain-content">
                    <div class="test-catalog-header">
                        <h1>Appointments</h1>
                        <button id="openCreateAppointment" class="add-test-button">Create Appointment</button>
                    </div>
                    <div>
                        <p class="MC-p">Appointments-></p>
                    </div>
                    <div class="heading-row">
                        <h2 class="heading3">Online Appointment </h2>
                        <div class="user-list">
                            <table class="test-catalog-table">
                                <thead>
                                    <tr>
                                        <th>Appointment ID</th>
                                        <th>Patient ID</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <!-- <th>Test ID</th> -->
                                        <!-- <th>Actions</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($appointmentsOnline as $appointment): ?>
                                    <tr>
                                        <td><?php echo $appointment['appointment_id']; ?></td>
                                        <td><?php echo $appointment['patient_id']; ?></td>
                                        <td><?php echo $appointment['appointment_date']; ?></td>
                                        <td><?php echo $appointment['appointment_time']; ?></td>
                                        <!-- <td>Blood Test</td> -->
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>   
                        </div>
                                 
                </div>
                <div class="heading-row">
                        <h2 class="heading3">Physical/Call Appointments</h2>
                        <div class="user-list">
                            <table class="test-catalog-table">
                                <thead>
                                    <tr>
                                        <th>Appointment ID</th>
                                        <th>Patient ID</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <!-- <th>Test Type</th> -->
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>1</td>
                                        <td>2023-10-01</td>
                                        <td>10:00 AM</td>
                                        <!-- <td>Blood Test</td> -->
                                        <td>
                                            <button class="Status">Cancel</button>
                                            <button class="Status">Reschedule</button>
                                        </td>
                                    </tr>
    
                                </tbody>
                            </table>   
                        </div>
                                 
                </div>
            </main>
    
        <!-- Create Appointment Modal (scoped styles + markup) -->
        <style>
        #createAppointmentModal { display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; overflow:auto; background:rgba(0,0,0,0.4); }
        #createAppointmentModal .modal-content { background:#fff; margin:6% auto; padding:20px; border-radius:6px; width:92%; max-width:520px; }
        #createAppointmentModal .close { float:right; font-size:24px; font-weight:700; cursor:pointer; }
        #createAppointmentForm label{display:block;margin-top:8px;font-weight:600}
        #createAppointmentForm input,#createAppointmentForm select,#createAppointmentForm textarea{width:100%;padding:8px;box-sizing:border-box;margin-top:4px;border:1px solid #ccc;border-radius:4px}
        #createAppointmentForm .actions{margin-top:12px;text-align:right}
        </style>

        <div id="createAppointmentModal">
            <div class="modal-content">
                <span id="createClose" class="close">&times;</span>
                <h3>Create Appointment</h3>
                <form id="createAppointmentForm" method="post" action="/lab_sync/index.php?controller=appointmentsController&action=storeAppointment">
                    <label for="patient_id">Patient ID</label>
                    <input type="number" id="patient_id" name="patient_id" required />

                    <label for="method">Method</label>
                    <select id="method" name="method">
                        <option value="online">Online</option>
                        <option value="physical">Physical</option>
                        <option value="call">Call</option>
                    </select>

                    <label for="appointment_date">Date</label>
                    <input type="date" id="appointment_date" name="appointment_date" required />

                    <label for="appointment_time">Time</label>
                    <input type="time" id="appointment_time" name="appointment_time" required />

                    <label for="reason">Reason</label>
                    <textarea id="reason" name="reason" rows="3"></textarea>

                    <div class="actions">
                        <button type="button" id="cancelCreate">Cancel</button>
                        <button type="submit">Save Appointment</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function(){
            const openBtn = document.getElementById('openCreateAppointment');
            const modal = document.getElementById('createAppointmentModal');
            const closeBtn = document.getElementById('createClose');
            const cancelBtn = document.getElementById('cancelCreate');
            const form = document.getElementById('createAppointmentForm');

            function openModal(){ modal.style.display='block'; }
            function closeModal(){ modal.style.display='none'; }

            openBtn && openBtn.addEventListener('click', function(e){ e.preventDefault(); openModal(); });
            closeBtn && closeBtn.addEventListener('click', closeModal);
            cancelBtn && cancelBtn.addEventListener('click', closeModal);
            window.addEventListener('click', function(e){ if(e.target===modal) closeModal(); });

            // Optional: AJAX submit (keeps user on page)
            form && form.addEventListener('submit', function(e){
                e.preventDefault();
                const fd = new FormData(form);
                fetch(form.action, { method:'POST', body: fd })
                    .then(r=>r.text())
                    .then(txt=>{
                        // simple feedback then reload
                        alert(txt || 'Appointment created');
                        closeModal();
                        window.location.reload();
                    })
                    .catch(err=>{ console.error(err); alert('Error creating appointment.'); });
            });
        });
        </script>

</body>
</html>