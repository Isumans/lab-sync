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
</head>
    <body>
        <!-- Navigation Bar -->
        <?php require PUBLIC_PATH . '/navbar.php'; ?>
        <div class="container">
            <!-- Sidebar -->
            <?php require PUBLIC_PATH . '/sidebar.php'; ?>

            <!-- Main Body Section -->
            <main class="main-content">
                 <div class="Tmain-content">
                    <div class="test-catalog-header">
                        <h1>Patient</h1>
                        <button class="add-test-button" ><a href="/lab_sync/index.php?controller=patientController&action=register_patient&role=<?php echo $role; ?>">Register Walk-in Patient</a></button>
                    </div>
                    <div>
                        <p class="MC-p">Patients-></p>
                    </div>
                    <div class="container-cards">
                        <div class="card c-card">
                                <h3>New This Week</h3>
                                <h1 class="countup" data-target="<?php echo (int)($newThisWeek ?? 10); ?>">0</h1>
                                <p><span class="countup-percent" data-target="<?php echo (int)($percentNewThisWeek ?? 2); ?>">0</span>%</p>
                                <!-- <img src="assests/chart1.png" alt="Chart 1" style="width:100%; height:auto;"> -->
                        </div>
                        <div class="card c-card">
                            <h3>Total New For the Month</h3>
                            <h1 class="countup" data-target="<?php echo (int)($totalThisMonth ?? 40); ?>">0</h1>
                            <p><span class="countup-percent" data-target="<?php echo (int)($percentTotalThisMonth ?? 5); ?>">0</span>%</p>
                            <!-- <img src="assests/chart2.png" alt="Chart 2" style="width:100%; height:auto;"> -->
                        </div>
                    

                    </div>
                    <div class="search-and-filter patient-search">
                            
                        <select class="search-option" id="patient-name" name="patient-name" required>
                                    <option value="">Email</option>
                                    <option value="John Doe">UserId</option>
                        </select>
                        <input type="text" class="search-bar" placeholder="  Search Patients...">
                        
                    </div>
                     <div class="nav-bar-container">
                        <div class="nav-bar-line">
                            <a class="navItem" onclick="showSection('all', event)" href="#">All</a>


                            <a class="navItem" onclick="showSection('male', event)" href="#">Male</a>

                            <a class="navItem" onclick="showSection('female', event)" href="#">Female</a>


                        </div>
                    </div>
                    <div id="content-area" class="content-area">
                        <div id="all" class="section">
                            <h2>All Patients</h2>
                            <p>Manage your patients here.</p>
                            <div class="user-list">
                                <table class="test-catalog-table">
                                    <thead>
                                        <tr>
                                            <th>Patient ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Contact Number</th>
                                            <th>Actions</th>
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
                                                    <td><?php echo htmlspecialchars($patient['patient_id']); ?></td>
                                                    <td><?php echo htmlspecialchars($patient['patient_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($patient['email']); ?></td>
                                                    <td><?php echo htmlspecialchars($patient['contact_number']); ?></td>
                                                    <td>
                                                        <button type="button" class="edit-btn" title="Edit"><img src="/lab_sync/public/assests/edit.png" alt="Edit"></button>
                                                        <button type="button" class="delete-btn" title="Delete"><img src="/lab_sync/public/assests/delete.png" alt="Delete"></button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="5">No patients found or database error.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="male" class="section" style="display:none;">
                            <h2>Male Patients</h2>
                            <p>Manage your male patients here.</p>
                            <div class="user-list">
                                <table class="test-catalog-table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>John Doe</td>
                                            <td>johndoe@example.com</td>
                                            <td>
                                                <button class="edit-button">Edit</button>
                                                <button class="delete-button">Delete</button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Jane Smith</td>
                                            <td>janesmith@example.com</td>
                                            <td>
                                                <button class="edit-button">Edit</button>
                                                <button class="delete-button">Delete</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="female" class="section" style="display:none;">
                            <h2>Female Patients</h2>
                            <p>Manage your female patients here.</p>    
                            <div class="user-list">
                                <table class="test-catalog-table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Jane Smith</td>
                                            <td>janesmith@example.com</td>
                                            <td>
                                                <button class="edit-button">Edit</button>
                                                <button class="delete-button">Delete</button>
                                            </td>
                                        </tr>
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
