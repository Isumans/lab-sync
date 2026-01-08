<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Signup</title>

    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/signupStyles.css"> 

    <style>
        /* Inline validation messages */
        .error-text {
            color: red;
            font-size: 13px;
            margin-top: 4px;
        }
    </style>
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
                    <img src="/lab_sync/public/assests/Labsync-3.png">
                    Lab<span style="color:#3DBDEC">Sync</span>
                </div>                    
            </div>
        </header>

        <main class="login-form-area">

            <h2>SignUp to start your session</h2>

            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form id="signupForm"
                  action="/lab_sync/index.php?controller=home&action=signup"
                  method="POST"
                  novalidate>

                <div class="input-group">
                    <label for="username">Username</label>
                    <input class="input1" type="text" id="username" name="username"
                           required minlength="3" maxlength="20"
                           placeholder="At least 3 characters">
                    <div class="error-text" id="usernameError"></div>
                </div>

                <div class="input-group">
                    <label for="email">Email</label>
                    <input class="input1" type="email" id="email" name="email"
                           placeholder="one@example.com" required>
                    <div class="error-text" id="emailError"></div>
                </div>

                <div class="input-group">
                    <label for="contact_number">Contact Number</label>
                    <input class="input1" type="tel" id="contact_number" name="contact_number"
                           placeholder="10 digit number"
                           required pattern="[0-9]{10}">
                    <div class="error-text" id="contactError"></div>
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input class="input1" type="password" id="password" name="password"
                           placeholder="Minimum 6 characters"
                           required minlength="6">
                    <div class="error-text" id="passwordError"></div>
                </div>

                <a href="#" class="reset-password-link">Reset Password</a>

                <button type="submit" class="login-button">Sign-Up</button>
            </form>

            <a href="#" class="login-with-code">
                <sub>Already, Have Account!</sub><br/>Login
            </a>

        </main>
    </div>
</div>

<!-- SIMPLE JAVASCRIPT VALIDATION -->
<script>
document.getElementById("signupForm").addEventListener("submit", function (e) {

    let isValid = true;

    // values
    let username = document.getElementById("username").value.trim();
    let email = document.getElementById("email").value.trim();
    let contact = document.getElementById("contact_number").value.trim();
    let password = document.getElementById("password").value;

    // error fields
    let uErr = document.getElementById("usernameError");
    let eErr = document.getElementById("emailError");
    let cErr = document.getElementById("contactError");
    let pErr = document.getElementById("passwordError");

    // reset errors
    uErr.textContent = "";
    eErr.textContent = "";
    cErr.textContent = "";
    pErr.textContent = "";

    // username
    if (username.length < 3) {
        uErr.textContent = "Username must be at least 3 characters";
        isValid = false;
    }

    // email
    if (!email.includes("@")) {
        eErr.textContent = "Please enter a valid email address";
        isValid = false;
    }

    // contact
    if (!/^[0-9]{10}$/.test(contact)) {
        cErr.textContent = "Contact number must be exactly 10 digits";
        isValid = false;
    }

    // password
    if (password.length < 6) {
        pErr.textContent = "Password must be at least 6 characters";
        isValid = false;
    }

    if (!isValid) {
        e.preventDefault(); // stop form submit
    }
});
</script>

</body>
</html>
