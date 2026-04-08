<?php
session_start();
echo $_SESSION['user_role'];
if (!isset($_SESSION['user_id'])) {
    // User is not logged in, redirect to login page
    header('Location: /lab_sync/index.php?controller=Auth&action=index');
    exit();
}
?>
<html>
<head>

    <title>Patients</title>
        <link rel="stylesheet" href="/lab_sync/public/styles.css">
        <link rel="stylesheet" href="/lab_sync/public/settingStyles.css">
        <link rel="stylesheet" href="/lab_sync/public/table.css">
        <link rel="stylesheet" href="/lab_sync/public/patientStyles.css">
        <link rel="stylesheet" href="/lab_sync/public/teamStyles.css">
</head>
    <body>
        <!-- Navigation Bar -->
        <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
        <div class="container">
            <!-- Sidebar -->
            <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

            <!-- Main Body Section -->
            <main class="main-content">
                <div class="main-content-header">
                    <h1>Patients</h1>
                    <p class="MC-p">Patients-></p>
                </div>

                <!-- Patient Header with Stats -->
                <div class="team-header-container">
                    <div class="team-header">
                        <h2>Patient Management</h2>
                        <button class="add-user-button"><a href="/lab_sync/index.php?controller=patientController&action=register_patient&role=<?php echo htmlspecialchars($role); ?>">+ Register Walk-in Patient</a></button>
                    </div>

                    <!-- Stats Cards -->
                    <div class="team-stats-grid" style="grid-template-columns: repeat(2, 1fr);">
                        <div class="stat-card-team">
                            <div class="stat-label-team">NEW THIS WEEK</div>
                            <div class="stat-value-team countup" data-target="<?php echo (int)($newThisWeek ?? 10); ?>">0</div>
                            <div class="stat-change"><span class="countup-percent" data-target="<?php echo (int)($percentNewThisWeek ?? 2); ?>">0</span>% increase</div>
                        </div>

                        <div class="stat-card-team">
                            <div class="stat-label-team">TOTAL NEW FOR THE MONTH</div>
                            <div class="stat-value-team countup" data-target="<?php echo (int)($totalThisMonth ?? 40); ?>">0</div>
                            <div class="stat-change"><span class="countup-percent" data-target="<?php echo (int)($percentTotalThisMonth ?? 5); ?>">0</span>% increase</div>
                        </div>
                    </div>
                </div>

                <!-- Search and Controls -->
                <div class="team-controls">
                    <input type="text" class="team-search-bar" placeholder="🔍 Search Patients..." id="patientSearchInput">
                    <button class="team-filter-button">↓ Filter</button>
                </div>

                <!-- Gender Tabs -->
                <div class="nav-bar-container" style="margin-top: 20px; margin-bottom: 20px;">
                    <div class="team-tabs">
                        <button class="team-tab active" data-filter="all" onclick="showSection('all', event)">All Patients</button>
                        <button class="team-tab" data-filter="male" onclick="showSection('male', event)">Male</button>
                        <button class="team-tab" data-filter="female" onclick="showSection('female', event)">Female</button>
                    </div>
                </div>
                    <div id="content-area" class="content-area">
                        <div id="all" class="section">
                            <div class="team-table-container">
                                <table class="team-users-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 40%;">PATIENT</th>
                                            <th style="width: 20%;text-align: center;">GENDER</th>
                                            <th style="width: 20%;text-align: center;">CONTACT</th>
                                            <th style="width: 20%;text-align: center;">ACTIONS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (is_array($patients) && count($patients) > 0): ?>
                                            <?php foreach ($patients as $patient): ?>
                                                <tr class="patient-row"
                                                    data-id="<?php echo htmlspecialchars($patient['patient_id']); ?>"
                                                    data-name="<?php echo htmlspecialchars($patient['patient_name']); ?>"
                                                    data-email="<?php echo htmlspecialchars($patient['email']); ?>"
                                                    data-contact="<?php echo htmlspecialchars($patient['contact_number']); ?>">
                                                    <td class="user-name-cell">
                                                        <div class="user-avatar"><?php echo strtoupper(substr($patient['patient_name'], 0, 2)); ?></div>
                                                        <div class="user-info">
                                                            <div class="user-name"><?php echo htmlspecialchars($patient['patient_name']); ?></div>
                                                            <div class="user-email"><?php echo htmlspecialchars($patient['email']); ?></div>
                                                        </div>
                                                    </td>
                                                    <td class="user-status" style="text-align: center;">
                                                        <span class="status-badge <?php echo strtolower($patient['gender']) === 'male' ? 'status-male' : 'status-female'; ?>">
                                                            <?php echo htmlspecialchars($patient['gender']); ?>
                                                        </span>
                                                    </td>
                                                    <td style="text-align: center;"><?php echo htmlspecialchars($patient['contact_number']); ?></td>
                                                    <td class="user-actions" style="align-items: center; justify-content: center;">
                                                        <button type="button" class="action-btn-edit edit-btn" title="Edit">
                                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                                <path d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </button>
                                                        <button type="button" class="action-btn-delete delete-btn" title="Delete">
                                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                                <path d="M2 4H14M6.5 7V11M9.5 7V11M3 4L4 13C4 13.5 4.5 14 5 14H11C11.5 14 12 13.5 12 13L13 4M5.5 4V2.5C5.5 2.2 5.7 2 6 2H10C10.3 2 10.5 2.2 10.5 2.5V4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" style="text-align: center; padding: 40px;">
                                                    <p>No patients found. <a href="/lab_sync/index.php?controller=patientController&action=register_patient&role=<?php echo htmlspecialchars($role); ?>">Register your first patient</a></p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="male" class="section" style="display:none;">
                            <div class="team-table-container">
                                <table class="team-users-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 40%;">PATIENT</th>
                                            <th style="width: 20%;text-align: center;">GENDER</th>
                                            <th style="width: 20%;text-align: center;">CONTACT</th>
                                            <th style="width: 20%;text-align: center;">ACTIONS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $malePatients = is_array($patients) ? array_filter($patients, function($p) { return $p['gender'] === 'Male'; }) : [];
                                        if (count($malePatients) > 0): ?>
                                            <?php foreach ($malePatients as $patient): ?>
                                                <tr class="patient-row"
                                                    data-id="<?php echo htmlspecialchars($patient['patient_id']); ?>"
                                                    data-name="<?php echo htmlspecialchars($patient['patient_name']); ?>"
                                                    data-email="<?php echo htmlspecialchars($patient['email']); ?>"
                                                    data-contact="<?php echo htmlspecialchars($patient['contact_number']); ?>">
                                                    <td class="user-name-cell">
                                                        <div class="user-avatar"><?php echo strtoupper(substr($patient['patient_name'], 0, 2)); ?></div>
                                                        <div class="user-info">
                                                            <div class="user-name"><?php echo htmlspecialchars($patient['patient_name']); ?></div>
                                                            <div class="user-email"><?php echo htmlspecialchars($patient['email']); ?></div>
                                                        </div>
                                                    </td>
                                                    <td class="user-status" style="text-align: center;">
                                                        <span class="status-badge status-male">
                                                            <?php echo htmlspecialchars($patient['gender']); ?>
                                                        </span>
                                                    </td>
                                                    <td style="text-align: center;"><?php echo htmlspecialchars($patient['contact_number']); ?></td>
                                                    <td class="user-actions" style="align-items: center; justify-content: center;">
                                                        <button type="button" class="action-btn-edit edit-btn" title="Edit">
                                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                                <path d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </button>
                                                        <button type="button" class="action-btn-delete delete-btn" title="Delete">
                                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                                <path d="M2 4H14M6.5 7V11M9.5 7V11M3 4L4 13C4 13.5 4.5 14 5 14H11C11.5 14 12 13.5 12 13L13 4M5.5 4V2.5C5.5 2.2 5.7 2 6 2H10C10.3 2 10.5 2.2 10.5 2.5V4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" style="text-align: center; padding: 40px;">
                                                    <p>No male patients found.</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="female" class="section" style="display:none;">
                            <div class="team-table-container">
                                <table class="team-users-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 40%;">PATIENT</th>
                                            <th style="width: 20%;text-align: center;">GENDER</th>
                                            <th style="width: 20%;text-align: center;">CONTACT</th>
                                            <th style="width: 20%;text-align: center;">ACTIONS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $femalePatients = is_array($patients) ? array_filter($patients, function($p) { return $p['gender'] === 'Female'; }) : [];
                                        if (count($femalePatients) > 0): ?>
                                            <?php foreach ($femalePatients as $patient): ?>
                                                <tr class="patient-row"
                                                    data-id="<?php echo htmlspecialchars($patient['patient_id']); ?>"
                                                    data-name="<?php echo htmlspecialchars($patient['patient_name']); ?>"
                                                    data-email="<?php echo htmlspecialchars($patient['email']); ?>"
                                                    data-contact="<?php echo htmlspecialchars($patient['contact_number']); ?>">
                                                    <td class="user-name-cell">
                                                        <div class="user-avatar"><?php echo strtoupper(substr($patient['patient_name'], 0, 2)); ?></div>
                                                        <div class="user-info">
                                                            <div class="user-name"><?php echo htmlspecialchars($patient['patient_name']); ?></div>
                                                            <div class="user-email"><?php echo htmlspecialchars($patient['email']); ?></div>
                                                        </div>
                                                    </td>
                                                    <td class="user-status" style="text-align: center;">
                                                        <span class="status-badge status-female">
                                                            <?php echo htmlspecialchars($patient['gender']); ?>
                                                        </span>
                                                    </td>
                                                    <td style="text-align: center;"><?php echo htmlspecialchars($patient['contact_number']); ?></td>
                                                    <td class="user-actions" style="align-items: center; justify-content: center;">
                                                        <button type="button" class="action-btn-edit edit-btn" title="Edit">
                                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                                <path d="M3 13.5H13M2 11L11.5 1.5C11.8 1.2 12.3 1.2 12.6 1.5L14.5 3.4C14.8 3.7 14.8 4.2 14.5 4.5L5 14H2V11Z" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </button>
                                                        <button type="button" class="action-btn-delete delete-btn" title="Delete">
                                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                                <path d="M2 4H14M6.5 7V11M9.5 7V11M3 4L4 13C4 13.5 4.5 14 5 14H11C11.5 14 12 13.5 12 13L13 4M5.5 4V2.5C5.5 2.2 5.7 2 6 2H10C10.3 2 10.5 2.2 10.5 2.5V4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" style="text-align: center; padding: 40px;">
                                                    <p>No female patients found.</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

        </div>
        <!-- Edit Patient Modal -->
        <style>
        /* basic modal styles (scoped for this view) */
        #editModal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background: rgba(0,0,0,0.4); }
        #editModal .modal-content { background: #fff; margin: 6% auto; padding: 20px; border-radius: 6px; width: 92%; max-width: 600px; }
        #editModal .close { float: right; font-size: 24px; font-weight: bold; cursor: pointer; }
        #editPatientForm .form-row { margin-bottom: 10px; }
        #editPatientForm label { display: block; font-weight: 600; margin-bottom: 4px; }
        #editPatientForm input[type=text], #editPatientForm input[type=email] { width: 100%; padding: 8px; box-sizing: border-box; }
        #editPatientForm .actions { text-align: right; margin-top: 12px; }
        </style>

        <div id="editModal">
            <div class="modal-content">
                <span id="editModalClose" class="close">&times;</span>
                <h3>Edit Patient</h3>
                <form id="editPatientForm" method="post" action="/lab_sync/index.php?controller=patientController&action=edit_patient&role=<?php echo urlencode($role); ?>">
                    <input type="hidden" name="patient_id" value="" />
                    <div class="form-row">
                        <label for="patient_name">Name</label>
                        <input type="text" id="patient_name" name="patient_name" required />
                    </div>
                    <div class="form-row">
                        <label for="patient_email">Email</label>
                        <input type="email" id="patient_email" name="patient_email" required />
                    </div>
                    <div class="form-row">
                        <label for="contact_number">Contact Number</label>
                        <input type="text" id="contact_number" name="contact_number" />
                    </div>
                    <div class="actions">
                        <button type="button" id="cancelEdit">Cancel</button>
                        <button type="submit" name="edit" value="1">Save changes</button>
                        <button type="submit" name="delete" value="1" style="margin-left:8px; background:#c33; color:#fff;">Delete</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        /* Count-up animation for stat cards */
        function animateCount(el, target, duration) {
            target = Number(target) || 0;
            if (el.getAttribute('data-animated') === '1') return;
            if (target <= 0) { el.textContent = '0'; el.setAttribute('data-animated','1'); return; }
            const start = 0;
            let startTime = null;
            duration = duration || Math.min(1400, 300 + target * 12);
            function step(timestamp) {
                if (!startTime) startTime = timestamp;
                const progress = Math.min((timestamp - startTime) / duration, 1);
                const value = Math.floor(progress * (target - start) + start);
                el.textContent = value;
                if (progress < 1) {
                    requestAnimationFrame(step);
                } else {
                    el.textContent = target;
                    el.setAttribute('data-animated','1');
                }
            }
            requestAnimationFrame(step);
        }

        function animateStats() {
            const els = document.querySelectorAll('.countup');
            const percents = document.querySelectorAll('.countup-percent');
            
            // Collect all targets to find max for synchronized duration
            let maxTarget = 0;
            const targets = [];
            
            els.forEach(el => {
                const dataTarget = el.getAttribute('data-target');
                const target = dataTarget !== null ? Number(dataTarget) : Number(el.textContent.replace(/[^0-9.-]+/g, '')) || 0;
                targets.push({ el, target, type: 'count' });
                maxTarget = Math.max(maxTarget, target);
                el.textContent = '0';
            });

            percents.forEach(el => {
                const dataTarget = el.getAttribute('data-target');
                const target = dataTarget !== null ? Number(dataTarget) : Number(el.textContent.replace(/[^0-9.-]+/g, '')) || 0;
                targets.push({ el, target, type: 'percent' });
                maxTarget = Math.max(maxTarget, target);
                el.textContent = '0';
            });

            // Calculate synchronized duration based on max target
            const syncDuration = Math.min(1400, 300 + maxTarget * 12);
            
            // Start all animations at the same time with same duration
            setTimeout(() => {
                targets.forEach(({ el, target }) => {
                    animateCount(el, target, syncDuration);
                });
            }, 20);
        }

        document.addEventListener('DOMContentLoaded', function () {
            animateStats();
            
            // Handle team-tab switching
            const teamTabs = document.querySelectorAll('.team-tab');
            teamTabs.forEach(tab => {
                tab.addEventListener('click', function(e) {
                    // Remove active class from all tabs
                    teamTabs.forEach(t => t.classList.remove('active'));
                    // Add active class to clicked tab
                    this.classList.add('active');
                });
            });
            
            const modal = document.getElementById('editModal');
            const form = document.getElementById('editPatientForm');
            const closeBtn = document.getElementById('editModalClose');
            const cancelBtn = document.getElementById('cancelEdit');

            function openModalWithData(data) {
                form.elements['patient_id'].value = data.id || '';
                form.elements['patient_name'].value = data.name || '';
                form.elements['patient_email'].value = data.email || '';
                form.elements['contact_number'].value = data.contact || '';
                modal.style.display = 'block';
            }

            document.querySelectorAll('.edit-btn').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    const tr = e.currentTarget.closest('tr');
                    if (!tr) return;
                    const data = {
                        id: tr.dataset.id,
                        name: tr.dataset.name,
                        email: tr.dataset.email,
                        contact: tr.dataset.contact
                    };
                    openModalWithData(data);
                });
            });

            closeBtn.addEventListener('click', function () { modal.style.display = 'none'; });
            cancelBtn.addEventListener('click', function () { modal.style.display = 'none'; });
            window.addEventListener('click', function (e) { if (e.target === modal) modal.style.display = 'none'; });

            // Delete from table button (quick submit with confirmation)
            document.querySelectorAll('.delete-btn').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    const tr = e.currentTarget.closest('tr');
                    if (!tr) return;
                    const id = tr.dataset.id;
                    if (!id) return;
                    if (!confirm('Delete this patient? This action cannot be undone.')) return;
                    const f = document.createElement('form');
                    f.method = 'post';
                    f.action = '/lab_sync/index.php?controller=patientController&action=edit_patient&role=<?php echo urlencode($role); ?>';
                    const inp = document.createElement('input'); inp.type = 'hidden'; inp.name = 'patient_id'; inp.value = id; f.appendChild(inp);
                    const del = document.createElement('input'); del.type = 'hidden'; del.name = 'delete'; del.value = '1'; f.appendChild(del);
                    document.body.appendChild(f);
                    f.submit();
                });
            });
        });
        </script>

        <script src="/lab_sync/public/js/showSection.js"></script>
        <script src="/lab_sync/public/js/showAlert.js"></script>
    </body>


</html>
