<html>   
<head>
        <title>Admin Login - LabSync</title>
        <link rel="stylesheet" href="public/styles.css">
    </head>
    <body>
        <div class="login-container">
            <h2>Administrator Login</h2>
            <form action="admin_dashboard.php" method="POST">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Login</button>
            </form>
        </div>
    </body>





</html>