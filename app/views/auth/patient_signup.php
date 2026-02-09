<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Signup</title>
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/signupStyles.css">
    <style>
        .error-text {
            color: red;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
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
                        <img src="/lab_sync/public/assests/Labsync-3.png">Lab<span style="color:#3DBDEC">Sync</span>
                    </div>
                </div>
            </header>
            
            <main class="login-form-area">
                <h2>SignUp to start your session</h2>
                <!-- <p class="secure-text">Secure, quick, and easy.</p> -->
                
                <?php if (!empty($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form action="/lab_sync/index.php?controller=home&action=signup" method="POST" onsubmit="return validateForm()">
                    <div class="input-group">
                        <label for="name">Name</label>
                        <input class="input1" type="text" id="name" name="name" required>
                        <span class="error-text" id="nameError"></span>
                    </div>
                    
                    <div class="input-group">
                        <label for="email">Email</label>
                        <input class="input1" type="email" id="email" name="email" placeholder="one@example.com" required>
                        <span class="error-text" id="emailError"></span>
                    </div>
                    
                    <div class="input-group">
                        <label for="contact_number">Contact Number</label>
                        <input class="input1" type="text" id="contact_number" name="contact_number" placeholder="1234567890" required>
                        <span class="error-text" id="contactError"></span>
                    </div>
                    
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input class="input1" type="password" id="password" name="password" placeholder="••••••••••" required>
                        <span class="error-text" id="passwordError"></span>
                    </div>
                    
                    <a href="#" class="reset-password-link">Reset Password</a>
                    <button type="submit" class="login-button">Sign-Up</button>
                </form>
                
                <script>
                function validateForm() {
                    let isValid = true;
                    
                    // Clear all error messages
                    document.getElementById('nameError').textContent = '';
                    document.getElementById('emailError').textContent = '';
                    document.getElementById('contactError').textContent = '';
                    document.getElementById('passwordError').textContent = '';
                    
                    // Validate Name (letters and spaces only)
                    const name = document.getElementById('name').value.trim();
                    const nameRegex = /^[a-zA-Z\s]+$/;
                    if (!name) {
                        document.getElementById('nameError').textContent = 'Name is required.';
                        isValid = false;
                    } else if (!nameRegex.test(name)) {
                        document.getElementById('nameError').textContent = 'Name must contain only letters and spaces.';
                        isValid = false;
                    }
                    
                    // Validate Email
                    const email = document.getElementById('email').value.trim();
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!email) {
                        document.getElementById('emailError').textContent = 'Email is required.';
                        isValid = false;
                    } else if (!emailRegex.test(email)) {
                        document.getElementById('emailError').textContent = 'Please enter a valid email address.';
                        isValid = false;
                    }
                    
                    // Validate Contact Number (digits only, starts with 0)
                    const contact = document.getElementById('contact_number').value.trim();
                    const contactRegex = /^0\d+$/;
                    if (!contact) {
                        document.getElementById('contactError').textContent = 'Contact number is required.';
                        isValid = false;
                    } else if (!/^\d+$/.test(contact)) {
                        document.getElementById('contactError').textContent = 'Contact number must contain only digits.';
                        isValid = false;
                    } else if (!contactRegex.test(contact)) {
                        document.getElementById('contactError').textContent = 'Contact number must start with 0.';
                        isValid = false;
                    }
                    
                    // Validate Password (at least 8 characters)
                    const password = document.getElementById('password').value;
                    if (!password) {
                        document.getElementById('passwordError').textContent = 'Password is required.';
                        isValid = false;
                    } else if (password.length < 8) {
                        document.getElementById('passwordError').textContent = 'Password must be at least 8 characters long.';
                        isValid = false;
                    }
                    
                    return isValid;
                }
                </script>
                
                <a href="#" class="login-with-code">
                    <sub>Already, Have Account!</sub><br/>Login
                </a>
            </main>
        </div>
    </div>
</body>
</html>