<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Signup</title>
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/signupStyles.css"> 
</head>
<body>

    <div class="login-page-container">
        <div class="visual-section">
            <div class="content-wrapper">
                <h1>Protect Yourself and Your Family — Easy Online Appointments.</h1>
                <div class="doctor-image-stats">
                    <div class="stat-bubble injected">5.7 million test</div>
                    <div class="stat-bubble recovery">98% success rate</div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <header>
                <div class="logo">                
                    <div class="navbar-brand">
                        <img src="/lab_sync/public/assests/Labsync-3.png">Lab<span style="color:#3DBDEC">Sync</span>
                    </div>                    
                </div>
            </header>

            <main class="login-form-area">
                <h2>SignUp to start your session</h2>
                <!-- <p class="secure-text">Secure, quick, and easy.</p> -->

                <form action="/lab_sync/index.php?controller=home&action=signup" method="POST">

                    <div class="input-group">
                        <label for="username">Username</label>
                        <input class="input1" type="username" id="username" name="username" required>
                    </div>
                    <div class="input-group">
                        <label for="email">Email</label>
                        <input class="input1" type="email" id="email" name="email" placeholder="one@example.com" required>
                    </div>
                    <div class="input-group">
                        <label for="contact_number">Contact Number</label>
                        <input class="input1" type="text" id="contact_number" name="contact_number" placeholder="1234567890" required>
                    </div>
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input class="input1" type="password" id="password" name="password" placeholder="••••••••••" required>
                    </div>

                    <a href="#" class="reset-password-link">Reset Password</a>

                    <button type="submit" class="login-button">Sign-Up</button>
                </form>

                <a href="#" class="login-with-code"><sub>Already, Have Account!</sub><br/>Login</a>
            </main>
        </div>
    </div>

</body>
</html>