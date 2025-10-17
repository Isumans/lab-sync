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
        <?php require 'C:\xampp\htdocs\lab_sync\public\navbar.php'; ?>
        <div class="container">
            <!-- Sidebar -->
            <?php require 'C:\xampp\htdocs\lab_sync\public\sidebar.php'; ?>

            <!-- Main Body Section -->
            <main class="main-content">
                 <div class="Tmain-content">
                    <div class="test-catalog-header">
                        <h1>Patient</h1>
                        <button class="add-test-button" ><a href="/lab_sync/index.php?controller=Patients&action=Register_patient">Register Walk-in Patient</a></button>
                    </div>
                    <div>
                        <p class="MC-p">Appointments-></p>
                    </div>
                    <div class="container-cards">
                        <div class="card c-card">
                                <h3>New This Week</h3>
                                <h1>10</h1>
                                <p>+2%</p>
                                <!-- <img src="assests/chart1.png" alt="Chart 1" style="width:100%; height:auto;"> -->
                        </div>
                        <div class="card c-card">
                            <h3>Total New For the Month</h3>
                            <h1>40</h1>
                            <p>+5%</p>
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
        <script src="/lab_sync/public/js/showSection.js"></script>
    </body>


</html>
