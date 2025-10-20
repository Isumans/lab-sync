<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>LabSync - Patient Portal</title>
  <link rel="stylesheet" href="css/home.css" />
</head>
<body>
  <!-- ================= NAVBAR ================= -->
  <header class="navbar">
    <div class="nav-logo">
      <img src="../../../public/assests/Labsync-3.png" alt="LabSync Logo">
      <span>LabSync</span>
    </div>
    <nav class="nav-links">
      <a href="#about">About</a>
      <a href="#tests">Tests</a>
      <a href="#help">Help</a>
    </nav>
    <div class="nav-actions">
      <!-- Login now points to dashboard demo -->
      <a href="dashboard.php" class="login">Dashboard</a>
      <a href="book.php" class="signup">Book Test</a>
    </div>
  </header>

  <!-- ================= HERO ================= -->
  <section class="hero">
    <div class="hero-text">
      <h1>Your Health,<br><span>Simplified</span></h1>
      <p>Book lab tests online, get fast results, and take control of your health journey with LabSync's modern platform.</p>
      <div class="hero-buttons">
        <a href="book.php" class="btn-primary">Book a Test →</a>
        <!-- replaced "Login" with Dashboard -->
        <a href="dashboard.php" class="btn-outline">Go to Dashboard</a>
      </div>
    </div>

    <div class="hero-image">
      <img src="images/image.png" alt="Brain Model">
      <div class="trust-badge">✅ Trusted by 50,000+ patients</div>
    </div>
  </section>

  <!-- ================= HOW IT WORKS ================= -->
  <section id="about" class="how-it-works">
    <h2>How It Works</h2>
    <p class="how-subtext">Getting your lab tests has never been easier. Follow these simple steps.</p>

    <div class="steps">
      <div class="step-card">
        <div class="step-number">1</div>
        <img src="images/image2.png" alt="Choose Test">
        <h3>Choose Test</h3>
        <p>Browse our comprehensive catalog and select the tests you need.</p>
      </div>

      <div class="step-card">
        <div class="step-number">2</div>
        <img src="/e-lab-system/public/images/how2.jpg" alt="Pick Time">
        <h3>Pick Time</h3>
        <p>Schedule your appointment at a convenient time and location.</p>
      </div>

      <div class="step-card">
        <div class="step-number">3</div>
        <img src="/e-lab-system/public/images/how3.jpg" alt="Get Results">
        <h3>Get Results</h3>
        <p>Access your results securely online with detailed explanations.</p>
      </div>
    </div>
  </section>

  <!-- ================= POPULAR TESTS ================= -->
  <section id="tests" class="tests-section">
    <h2>Popular Lab Tests</h2>
    <p class="how-subtext">Comprehensive health screenings to keep you at your best.</p>

    <div class="test-grid">
      <!-- All “Book Test” buttons go to book.html with a preselected test via query (?test=...) -->
      <div class="test-card">
        <img src="/e-lab-system/public/images/test1.jpg" alt="Complete Blood Count">
        <h3>Complete Blood Count</h3>
        <p>Comprehensive blood health analysis</p>
        <a href="book.html?test=Complete%20Blood%20Count" class="book-btn">Book Test</a>
      </div>

      <div class="test-card">
        <img src="/e-lab-system/public/images/test2.jpg" alt="Lipid Profile">
        <h3>Lipid Profile</h3>
        <p>Heart health and cholesterol screening</p>
        <a href="book.html?test=Lipid%20Profile" class="book-btn">Book Test</a>
      </div>

      <div class="test-card">
        <img src="/e-lab-system/public/images/test3.jpg" alt="Thyroid Function">
        <h3>Thyroid Function</h3>
        <p>Complete thyroid hormone panel</p>
        <a href="book.html?test=Thyroid%20Function" class="book-btn">Book Test</a>
      </div>

      <div class="test-card">
        <img src="/e-lab-system/public/images/test4.jpg" alt="Diabetes Screening">
        <h3>Diabetes Screening</h3>
        <p>Blood sugar and HbA1c testing</p>
        <a href="book.html?test=Diabetes%20Screening" class="book-btn">Book Test</a>
      </div>

      <div class="test-card">
        <img src="/e-lab-system/public/images/test5.jpg" alt="General Health Checkup">
        <h3>General Health Checkup</h3>
        <p>Comprehensive wellness panel</p>
        <a href="book.html?test=General%20Health%20Checkup" class="book-btn">Book Test</a>
      </div>

      <div class="test-card">
        <img src="/e-lab-system/public/images/test6.jpg" alt="Cognitive Health">
        <h3>Cognitive Health</h3>
        <p>Brain function and memory tests</p>
        <a href="book.html?test=Cognitive%20Health" class="book-btn">Book Test</a>
      </div>
    </div>
  </section>

  <!-- ================= WHY CHOOSE ================= -->
  <section id="help" class="why-section">
    <h2>Why Choose LabSync?</h2>
    <div class="why-cards">
      <div class="why-card">
        <div class="why-icon-bg">
          <img src="/e-lab-system/public/images/icon1.png" alt="Privacy">
        </div>
        <h3>Privacy Protected</h3>
        <p>Your health data is encrypted and secure.</p>
      </div>

      <div class="why-card">
        <div class="why-icon-bg">
          <img src="/e-lab-system/public/images/icon2.png" alt="Fast Results">
        </div>
        <h3>Fast Results</h3>
        <p>Get your results within 24–48 hours.</p>
      </div>

      <div class="why-card">
        <div class="why-icon-bg">
          <img src="/e-lab-system/public/images/icon3.png" alt="Certified Labs">
        </div>
        <h3>Certified Labs</h3>
        <p>All tests performed in accredited facilities.</p>
      </div>
    </div>
  </section>

  <!-- ================= FOOTER ================= -->
  <footer class="footer">
    <div class="footer-top">
      <div class="footer-brand">
        <img src="/e-lab-system/public/images/logo.png" alt="LabSync Logo">
        <p>Making healthcare accessible and convenient for everyone.</p>
      </div>

      <div class="footer-grid">
        <div>
          <h4>Company</h4>
          <a href="#">About</a>
          <a href="#">Careers</a>
          <a href="#">Press</a>
        </div>
        <div>
          <h4>Support</h4>
          <a href="#">Help Center</a>
          <a href="#">Contact</a>
          <a href="#">Privacy</a>
        </div>
        <div>
          <h4>Contact</h4>
          <a href="#">Twitter</a>
          <a href="#">LinkedIn</a>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <p>© 2024 LabSync. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>
