<?php
require_once __DIR__ . '/app/bootstrap.php';
$title = 'LabSync - Patient Portal';
include PARTIALS_PATH . '/header.php';
?>
<section class="hero">
  <div class="hero-text">
    <h1>Your Health,<br><span>Simplified</span></h1>
    <p>Book lab tests online, get fast results, and take control of your health journey with LabSync's modern platform.</p>
    <div class="hero-buttons">
      <a href="<?= asset('app/views/patient/explore.php') ?>" class="btn-primary">Book a Test →</a>
      <a href="<?= asset('app/views/patient/dashboard.php') ?>" class="btn-outline">Go to Dashboard</a>
    </div>
  </div>
  <div class="hero-image">
    <img src="<?= asset('app/views/patient/images/image.png') ?>" alt="Hero Image">
    <div class="trust-badge">✅ Trusted by 50,000+ patients</div>
  </div>
</section>

<section id="how" class="how-it-works">
  <h2>How It Works</h2>
  <p class="how-subtext">Choose Test → Pick Time → Get Results</p>
  <div class="steps">
    <div class="step-card"><div class="step-number">1</div>
      <img src="<?= asset('app/views/patient/images/image21.png') ?>" alt="Choose Test">
      <h3>Choose Test</h3><p>Browse our comprehensive catalog.</p>
    </div>
    <div class="step-card"><div class="step-number">2</div>
      <img src="<?= asset('public/images/how2.jpg') ?>" alt="Pick Time">
      <h3>Pick Time</h3><p>Schedule at a convenient time.</p>
    </div>
    <div class="step-card"><div class="step-number">3</div>
      <img src="<?= asset('public/images/how3.jpg') ?>" alt="Get Results">
      <h3>Get Results</h3><p>View securely on your dashboard.</p>
    </div>
  </div>
</section>

<section id="tests" class="tests-section">
  <h2>Popular Lab Tests</h2>
  <p class="how-subtext">Comprehensive health screenings to keep you at your best.</p>

  <div class="test-grid">
    <?php
      $cards = [
        ['Full Blood Count (FBC)','Comprehensive blood health analysis'],
        ['Lipid Profile','Cholesterol/heart risk screening'],
        ['Fasting Blood Sugar (FBS)','Diabetes screening'],
        ['Thyroid Panel (TSH/T3/T4)','Hormone disorders assessment'],
        ['Liver Function Test (LFT)','Detects liver disease'],
        ['Kidney Function Test (KFT)','Detects kidney disease'],
      ];
      foreach ($cards as $c):
        $name=$c[0]; $desc=$c[1];
    ?>
    <div class="test-card">
      <div class="test-icon"></div>
      <h3><?= htmlspecialchars($name) ?></h3>
      <p><?= htmlspecialchars($desc) ?></p>
      <a class="book-btn" href="<?= asset('app/views/patient/book.php?test=' . urlencode($name)) ?>">Book Test</a>
      <a class="details-link" href="<?= asset('app/views/patient/explore.php#' . urlencode($name)) ?>">View details</a>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<section id="help" class="why-section">
  <h2>Why Choose LabSync?</h2>
  <div class="why-cards">
    <div class="why-card"><div class="why-icon-bg"></div><h3>Privacy Protected</h3><p>Your health data is secure.</p></div>
    <div class="why-card"><div class="why-icon-bg"></div><h3>Fast Results</h3><p>Most results in 24–48 hours.</p></div>
    <div class="why-card"><div class="why-icon-bg"></div><h3>Certified Labs</h3><p>Accredited facilities only.</p></div>
  </div>
</section>
<?php include PARTIALS_PATH . '/footer.php'; ?>
