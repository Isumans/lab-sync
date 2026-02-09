<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$user_id = $_SESSION['user_id'] ?? null;
$email = $_SESSION['email'] ?? null;
$user_role = $_SESSION['user_role'] ?? null;
?>
<header class="navbar">
  <div class="nav-logo" style="cursor:pointer">
    <a href="/lab_sync/index.php">
    <img src="/lab_sync/public/assests/Labsync-3.png" alt="LabSync Logo"/>
    <span>LabSync</span>
</a>
  </div>
  <nav class="nav-links">
    <a href="index.php?controller=home&action=explore">Tests</a>
    <a href="index.php?controller=home&action=how">How it works</a>
    <a href="index.php?controller=home&action=about">About</a>
  </nav>
  <div class="nav-actions">
    <!-- Show different nav based on login state and role -->
     <?php if (isset($user_id) && isset($user_role)){ ?>
      <?php if($user_role === 'admin' || $user_role === 'receptionist' || $user_role === 'technician'){ ?>
        <a href="/lab_sync/index.php?controller=dashboard&action=index" class="login"><span class="user-role"><?php echo htmlspecialchars($user_role); ?> panel</span></a>
      <?php } ?>
       
       <a href="/lab_sync/index.php?controller=Auth&action=logout" class="login">Logout</a>
        <?php if($user_role === 'patient'){ ?>
          <a href="/lab_sync/index.php?controller=profile&action=view" ><img id="user-icon" class="profile-logo" src="/lab_sync/public/assests/user.png"></a>
        <?php } ?>
     <?php } else { ?>
       <a href="/lab_sync/index.php?controller=Auth&action=login" class="login">Login</a>
       <a href="/lab_sync/index.php?controller=Auth&action=patient_signup" class="signup">Sign up</a>
     <?php } ?>
  </div>
</header>
