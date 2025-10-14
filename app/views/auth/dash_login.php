<html>
<head>

    <title>Dashboard Login</title>
    <link rel="stylesheet" href="/lab_sync/public/styles.css">
    <link rel="stylesheet" href="/lab_sync/public/loginStyles.css">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-form">
            <div class="login-header">
                <img src="/lab_sync/public/assests/Labsync-1.png" alt="Logo" class>
                <h1>Login</h1>
            </div>
            

            <form action="/lab_sync/index.php?controller=Auth&action=login" method="post" id="loginV">
                <?php if (!empty($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <div class="label-div">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" id="username" class="form-control" name="username" >
                </div>
                <div class="label-div">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" >
                </div>


                <button class="btn" type="submit">Login</button>
            </form>
        </div>
    </div>
    
</body>
</html>