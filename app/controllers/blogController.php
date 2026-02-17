<?php
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../../config/paths.php';
}

class blogController {
    
    // Mock blog posts data
    private function getMockPosts() {
        return [
            [
                'title' => 'Understanding Your Complete Blood Count (CBC) Test',
                'slug' => 'understanding-cbc-test',
                'excerpt' => 'Learn what a CBC test measures, why it\'s important, and how to prepare for this common blood test that provides valuable insights into your overall health.',
                'content' => "A Complete Blood Count (CBC) test is one of the most common blood tests performed in medical diagnostics. This comprehensive test measures several components of your blood, including red blood cells, white blood cells, and platelets.\n\nWhat Does a CBC Test Measure?\n\nThe CBC test provides critical information about:\n- Red blood cell count (RBC): Indicates your blood's oxygen-carrying capacity\n- White blood cell count (WBC): Shows your immune system's health\n- Hemoglobin: The protein that carries oxygen in your blood\n- Hematocrit: The proportion of red blood cells in your blood\n- Platelet count: Important for blood clotting\n\nWhy Is It Important?\n\nDoctors order CBC tests to screen for, diagnose, or monitor various conditions including anemia, infections, blood disorders, and immune system disorders. It's often part of routine health checkups.\n\nHow to Prepare\n\nFor most CBC tests, no special preparation is needed. You can eat and drink normally before the test. However, if your blood sample will be used for additional tests, you may need to fast. Always follow your doctor's specific instructions.\n\nUnderstanding Your Results\n\nYour results will show whether your blood cell counts are within normal ranges. Abnormal results don't always indicate a serious problem—they can be caused by temporary conditions, medications, or lifestyle factors. Your healthcare provider will interpret the results in context with your overall health.\n\nIf you need a CBC test, book your appointment at LabSync today. Our experienced technicians ensure a quick, comfortable experience with accurate results delivered promptly.",
                'category' => 'New Tests',
                'date' => '2026-02-15',
                'author' => 'LabSync Medical Team',
                'featured_image' => null
            ],
            [
                'title' => 'How to Prepare for Your Fasting Blood Sugar Test',
                'slug' => 'prepare-fasting-blood-sugar',
                'excerpt' => 'Essential preparation tips for accurate fasting blood sugar results, including what to eat, when to fast, and common mistakes to avoid.',
                'content' => "Proper preparation for a fasting blood sugar test is crucial for accurate results. This guide will help you prepare correctly and avoid common mistakes.\n\nWhat Is a Fasting Blood Sugar Test?\n\nA fasting blood sugar (FBS) test measures your blood glucose level after you haven't eaten for at least 8 hours. It's a key diagnostic tool for diabetes and prediabetes.\n\nPreparing for Your Test\n\n1. Fasting Duration: Fast for 8-12 hours before your test. Water is allowed and encouraged, but no other beverages.\n\n2. Schedule Morning Appointments: Most people find it easier to fast overnight and schedule tests for early morning.\n\n3. Continue Regular Medications: Unless specifically instructed otherwise by your doctor, continue taking your regular medications. However, inform the lab technician about any medications you're taking.\n\n4. Avoid Strenuous Exercise: Skip intense workouts the morning of your test, as they can affect blood sugar levels.\n\nCommon Mistakes to Avoid\n\n- Don't drink coffee, tea, or juice during your fasting period\n- Avoid chewing gum or mints\n- Don't smoke before the test\n- Don't skip the test if you feel slightly hungry—that's normal\n\nWhat to Expect\n\nThe blood draw takes only a few minutes. You may feel a brief pinch when the needle is inserted. After the test, you can immediately eat and drink normally.\n\nResults typically indicate:\n- Normal: Less than 100 mg/dL\n- Prediabetes: 100-125 mg/dL\n- Diabetes: 126 mg/dL or higher on two separate tests\n\nSchedule your fasting blood sugar test at LabSync for professional, comfortable service and quick results.",
                'category' => 'Patient Instructions',
                'date' => '2026-02-12',
                'author' => 'Dr. Samantha Wells',
                'featured_image' => null
            ],
            [
                'title' => 'New Service: Comprehensive Thyroid Panel Now Available',
                'slug' => 'thyroid-panel-announcement',
                'excerpt' => 'We\'re excited to announce our expanded thyroid testing services, offering comprehensive panels to help diagnose and monitor thyroid conditions.',
                'content' => "LabSync is pleased to announce the addition of comprehensive thyroid panel testing to our services. This expanded offering helps patients and healthcare providers get complete thyroid health insights.\n\nWhat's Included in Our Thyroid Panel?\n\nOur comprehensive thyroid panel measures:\n- TSH (Thyroid Stimulating Hormone)\n- Free T4 (Thyroxine)\n- Free T3 (Triiodothyronine)\n- Thyroid Antibodies (TPO and TG)\n\nWhy Thyroid Health Matters\n\nThe thyroid gland plays a crucial role in regulating metabolism, energy levels, body temperature, and overall hormonal balance. Thyroid disorders affect millions of people and can cause symptoms like:\n- Unexplained weight changes\n- Fatigue or hyperactivity\n- Temperature sensitivity\n- Mood changes\n- Hair loss or skin changes\n\nWho Should Get Tested?\n\nConsider thyroid testing if you:\n- Experience unexplained fatigue or weight changes\n- Have a family history of thyroid disease\n- Are pregnant or planning pregnancy\n- Have been diagnosed with other autoimmune conditions\n- Take medications that may affect thyroid function\n\nConvenient Testing Process\n\nNo special preparation is required for most thyroid tests. The blood sample collection takes just minutes, and results are typically available within 24-48 hours.\n\nOur state-of-the-art laboratory ensures accurate, reliable results that you and your healthcare provider can trust for diagnosis and treatment decisions.\n\nBook your thyroid panel test today through our online booking system or call our friendly team to schedule your appointment.",
                'category' => 'New Tests',
                'date' => '2026-02-10',
                'author' => 'LabSync Team',
                'featured_image' => null
            ],
            [
                'title' => 'Understanding Your Lipid Profile Results',
                'slug' => 'understanding-lipid-profile',
                'excerpt' => 'A comprehensive guide to interpreting your cholesterol and lipid panel results, and what they mean for your heart health.',
                'content' => "Your lipid profile is a crucial indicator of cardiovascular health. Understanding these results empowers you to make informed decisions about your health and lifestyle.\n\nWhat Is a Lipid Profile?\n\nA lipid profile measures the fats in your blood, including:\n- Total Cholesterol: Overall cholesterol level\n- LDL (Low-Density Lipoprotein): Often called \"bad\" cholesterol\n- HDL (High-Density Lipoprotein): Often called \"good\" cholesterol\n- Triglycerides: A type of fat used for energy\n\nInterpreting Your Numbers\n\nTotal Cholesterol:\n- Desirable: Less than 200 mg/dL\n- Borderline high: 200-239 mg/dL\n- High: 240 mg/dL and above\n\nLDL Cholesterol:\n- Optimal: Less than 100 mg/dL\n- Near optimal: 100-129 mg/dL\n- Borderline high: 130-159 mg/dL\n- High: 160 mg/dL and above\n\nHDL Cholesterol:\n- Low (risk factor): Less than 40 mg/dL for men, less than 50 mg/dL for women\n- High (protective): 60 mg/dL and above\n\nTriglycerides:\n- Normal: Less than 150 mg/dL\n- Borderline high: 150-199 mg/dL\n- High: 200 mg/dL and above\n\nWhat Affects Your Lipid Levels?\n\nMany factors influence cholesterol and triglycerides:\n- Diet (saturated fats, trans fats, cholesterol intake)\n- Physical activity level\n- Body weight\n- Smoking\n- Genetics\n- Age and gender\n- Certain medications and medical conditions\n\nImproving Your Lipid Profile\n\nIf your results show areas for improvement:\n1. Adopt a heart-healthy diet rich in fruits, vegetables, and whole grains\n2. Exercise regularly (aim for 150 minutes of moderate activity weekly)\n3. Maintain a healthy weight\n4. Quit smoking\n5. Limit alcohol consumption\n6. Consider medication if recommended by your healthcare provider\n\nSchedule your lipid profile test at LabSync to take control of your heart health. Early detection and management can significantly reduce your risk of heart disease.",
                'category' => 'Health Education',
                'date' => '2026-02-08',
                'author' => 'Dr. Michael Chen',
                'featured_image' => null
            ],
            [
                'title' => 'Preparing Children for Blood Tests: A Parent\'s Guide',
                'slug' => 'children-blood-test-guide',
                'excerpt' => 'Practical advice for parents on how to prepare children for blood tests, reduce anxiety, and make the experience as comfortable as possible.',
                'content' => "Taking your child for a blood test can be stressful for both parent and child. These strategies can help make the experience smoother and less frightening.\n\nBefore the Appointment\n\n1. Be Honest: Explain in age-appropriate terms what will happen. Don't say it won't hurt—instead, acknowledge it might pinch briefly.\n\n2. Use Positive Language: Instead of \"shot\" or \"needle,\" try \"quick pinch\" or \"small poke.\"\n\n3. Read Books or Watch Videos: Many children's resources explain blood tests in kid-friendly ways.\n\n4. Role Play: Practice with a toy doctor kit to familiarize your child with the process.\n\n5. Plan Rewards: Promise a small treat or fun activity afterward (but not dependent on \"being brave\").\n\nDuring the Test\n\n1. Stay Calm: Children pick up on parental anxiety. Your calm demeanor is reassuring.\n\n2. Bring Comfort Items: A favorite toy, blanket, or stuffed animal can provide security.\n\n3. Offer Distraction: Bring books, toys, or let them watch videos on your phone.\n\n4. Physical Comfort: Hold smaller children on your lap. Let older children squeeze your hand or a stress ball.\n\n5. Encourage Deep Breathing: Practice slow, deep breaths together.\n\n6. Praise Coping Efforts: Acknowledge their bravery regardless of tears or resistance.\n\nWhat Our Team Does to Help\n\nAt LabSync, our pediatric-trained phlebotomists:\n- Use child-friendly language\n- Work quickly and efficiently\n- Offer stickers and small rewards\n- Create a welcoming, colorful environment\n- Use the smallest appropriate needles\n- Give children as much control as appropriate (choosing which arm, etc.)\n\nAfter the Test\n\n1. Praise and Reward: Follow through with promised treats or activities.\n\n2. Apply Bandage Together: Let them help or choose a fun bandage.\n\n3. Discuss Feelings: Talk about the experience and answer questions honestly.\n\n4. Normalize the Experience: Remind them that many children (and adults) get blood tests.\n\nWhen to Prepare\n\nFor young children (under 5), tell them the morning of the test or the night before. Older children can handle a few days' notice, which allows time to process and ask questions.\n\nSpecial Considerations\n\nIf your child has had negative medical experiences:\n- Consider scheduling the first appointment of the day when wait times are shorter\n- Request an experienced pediatric phlebotomist\n- Discuss anxiety management strategies with your pediatrician\n- Be patient—building positive associations takes time\n\nAt LabSync, we understand that pediatric patients need extra care and patience. Our team is trained to make blood tests as quick and comfortable as possible. Book your child's appointment with confidence.",
                'category' => 'Patient Instructions',
                'date' => '2026-02-05',
                'author' => 'LabSync Pediatric Team',
                'featured_image' => null
            ],
            [
                'title' => 'Vitamin D Testing: Why It Matters Year-Round',
                'slug' => 'vitamin-d-testing-importance',
                'excerpt' => 'Discover why vitamin D testing is important regardless of season, who should get tested, and how to address deficiency.',
                'content' => "Vitamin D deficiency is surprisingly common, even in sunny climates. Understanding your vitamin D status is an important part of preventive healthcare.\n\nWhy Vitamin D Matters\n\nVitamin D is essential for:\n- Bone health and calcium absorption\n- Immune system function\n- Mood regulation\n- Cardiovascular health\n- Muscle function\n\nDeficiency has been linked to osteoporosis, increased infection risk, depression, and various chronic diseases.\n\nWho Should Get Tested?\n\nConsider vitamin D testing if you:\n- Spend most time indoors\n- Use sunscreen diligently (which blocks vitamin D production)\n- Have darker skin (melanin reduces vitamin D synthesis)\n- Are over 65 (aging reduces vitamin D production)\n- Have conditions affecting nutrient absorption\n- Experience unexplained fatigue, bone pain, or muscle weakness\n- Are pregnant or breastfeeding\n\nUnderstanding Your Results\n\nVitamin D levels are measured in ng/mL:\n- Deficient: Less than 20 ng/mL\n- Insufficient: 20-29 ng/mL\n- Sufficient: 30-50 ng/mL\n- High: Greater than 50 ng/mL\n\nMost experts recommend maintaining levels between 30-50 ng/mL for optimal health.\n\nTest Preparation\n\nNo special preparation is required for vitamin D testing. You can eat and drink normally before the test, and it can be done at any time of day.\n\nAddressing Deficiency\n\nIf you're deficient, your healthcare provider may recommend:\n1. Vitamin D supplements (D3 is most effective)\n2. Increased sun exposure (15-30 minutes several times weekly)\n3. Dietary changes (fatty fish, fortified foods, egg yolks)\n4. Retesting after 3-6 months of supplementation\n\nSeasonal Considerations\n\nMany people assume vitamin D is only a concern in winter, but:\n- Indoor lifestyles affect vitamin D year-round\n- Sunscreen use (important for skin cancer prevention) blocks vitamin D production\n- Air pollution can filter UV rays\n- Living far from the equator reduces UV exposure even in summer\n\nThe Safety of Supplementation\n\nVitamin D supplementation is generally safe, but testing is important because:\n- Individual needs vary widely\n- Too much vitamin D can cause toxicity\n- Knowing your baseline helps you and your doctor determine the right dose\n\nSchedule your vitamin D test at LabSync today. It's a simple blood test that provides valuable information for your long-term health strategy.",
                'category' => 'Health Education',
                'date' => '2026-02-01',
                'author' => 'Dr. Elena Rodriguez',
                'featured_image' => null
            ],
            [
                'title' => 'Introducing Express Testing: Results in 2 Hours',
                'slug' => 'express-testing-announcement',
                'excerpt' => 'Need urgent test results? Our new express testing service provides accurate results for select tests in just 2 hours.',
                'content' => "LabSync is excited to introduce Express Testing—a premium service for patients who need rapid results without compromising accuracy.\n\nWhat Is Express Testing?\n\nOur Express Testing service rushes your blood sample through our laboratory, prioritizing analysis and result reporting. For select tests, you'll receive accurate results within 2 hours of sample collection.\n\nAvailable Tests\n\nExpress Testing is currently available for:\n- Complete Blood Count (CBC)\n- Basic Metabolic Panel\n- Liver Function Tests\n- Kidney Function Tests\n- Thyroid Stimulating Hormone (TSH)\n- Blood Glucose\n- Hemoglobin A1C\n\nMore tests will be added to the express service based on demand and technical feasibility.\n\nWho Benefits from Express Testing?\n\nThis service is ideal for:\n- Patients with urgent medical appointments\n- Travelers needing results before departure\n- Pre-operative patients with tight surgical schedules\n- Anyone who needs peace of mind quickly\n- Professionals with limited time\n\nHow It Works\n\n1. Book Online: Select \"Express Service\" when booking your appointment\n2. Arrive Promptly: Come to your scheduled appointment time\n3. Quick Collection: Our skilled phlebotomists collect your sample efficiently\n4. Priority Processing: Your sample goes directly to our laboratory\n5. Rapid Results: Results delivered to your portal and physician within 2 hours\n\nQuality Assurance\n\nSpeed doesn't mean compromised quality. Express Testing uses:\n- The same rigorous quality controls as standard testing\n- The same accurate, calibrated equipment\n- The same experienced laboratory technicians\n- The same professional quality standards\n\nPricing\n\nExpress Testing includes a premium service fee in addition to the standard test cost. Pricing details are available when you book your appointment or by contacting our customer service team.\n\nStandard vs. Express Testing\n\nStandard Testing:\n- Results in 24-48 hours\n- No additional fee\n- Available for all tests\n- Ideal for routine monitoring and checkups\n\nExpress Testing:\n- Results in 2 hours\n- Premium service fee applies\n- Available for select tests\n- Ideal for urgent needs\n\nBooking Your Express Test\n\nReady to use Express Testing? Book online through our patient portal, or call our dedicated express service line. Same-day appointments are often available.\n\nAt LabSync, we understand that sometimes you can't wait days for critical health information. Express Testing gives you speed, accuracy, and peace of mind when you need it most.",
                'category' => 'New Tests',
                'date' => '2026-01-28',
                'author' => 'LabSync Team',
                'featured_image' => null
            ],
            [
                'title' => 'What to Expect During Your First Lab Visit',
                'slug' => 'first-lab-visit-guide',
                'excerpt' => 'A complete walkthrough of what happens during your first visit to LabSync, from check-in to receiving your results.',
                'content' => "Visiting a diagnostic laboratory for the first time can feel unfamiliar. This guide walks you through every step so you know exactly what to expect at LabSync.\n\nBefore You Arrive\n\n1. Confirm Your Appointment: You'll receive a confirmation email with your appointment time and location.\n\n2. Check Test Requirements: Some tests require fasting or other preparation. Review your appointment details carefully.\n\n3. Bring Required Items:\n   - Photo ID\n   - Insurance card (if applicable)\n   - Doctor's prescription or test order\n   - List of current medications\n\n4. Wear Comfortable Clothing: Short sleeves or sleeves that roll up easily make blood draws more comfortable.\n\nCheck-In Process\n\nWhen you arrive:\n1. Approach the reception desk\n2. Provide your name and appointment time\n3. Present your ID and insurance card\n4. Complete or update patient information forms\n5. Review and sign consent forms\n\nThe waiting area offers:\n- Comfortable seating\n- Water and coffee\n- Reading materials\n- Free WiFi\n- Clean, well-lit environment\n\nThe Collection Process\n\n1. A phlebotomist or nurse will call your name\n2. You'll be escorted to a private collection room\n3. The staff member will verify your identity and test orders\n4. They'll explain the procedure\n5. For blood tests:\n   - You'll sit in a comfortable chair\n   - The phlebotomist will apply a tourniquet to your arm\n   - They'll clean the site with antiseptic\n   - A small needle will be inserted briefly\n   - Blood will be collected into one or more vials\n   - The needle will be removed and pressure applied\n   - A bandage will be placed over the site\n\nThe entire collection process typically takes 5-10 minutes.\n\nAfter Collection\n\n1. You'll receive aftercare instructions\n2. Ask any questions about next steps\n3. Schedule follow-up appointments if needed\n4. You're free to leave immediately (unless fasting—we offer snacks!)\n\nPost-Test Care\n\n- Keep the bandage on for at least 2 hours\n- Avoid heavy lifting with that arm for a few hours\n- If bruising occurs, it's normal and will fade\n- Drink plenty of fluids\n- If you feel dizzy, sit down immediately and notify staff\n\nGetting Your Results\n\nResults are delivered through:\n1. Our secure online patient portal (email notification sent when ready)\n2. Direct transmission to your healthcare provider\n3. Phone call for urgent or abnormal results\n\nTypical turnaround times:\n- Routine tests: 24-48 hours\n- Specialized tests: 3-7 days\n- Express service: 2 hours (for select tests)\n\nWhat Makes LabSync Different\n\n- Minimal wait times with appointment system\n- Experienced, gentle phlebotomists\n- State-of-the-art equipment\n- Rigorous quality controls\n- Friendly, professional staff\n- Clean, comfortable facilities\n- Convenient online booking and results\n\nCommon First-Timer Questions\n\nQ: Will it hurt?\nA: You may feel a brief pinch or sting, but our skilled staff minimizes discomfort.\n\nQ: What if I'm afraid of needles?\nA: Let us know! We have strategies to help, including distraction techniques and letting you lie down.\n\nQ: Can I bring someone with me?\nA: Absolutely. Support persons are welcome in the waiting area and can accompany you to the collection room if that makes you more comfortable.\n\nQ: What if I faint?\nA: It's rare, but if you have a history of fainting, tell the phlebotomist. We'll have you lie down during collection.\n\nQ: How accurate are the results?\nA: Our laboratory follows strict quality controls and participates in proficiency programs to ensure the highest accuracy.\n\nReady for Your First Visit?\n\nBook your appointment at LabSync today. Our team is ready to make your first laboratory experience comfortable, efficient, and professional. If you have questions before your visit, don't hesitate to call us—we're here to help.",
                'category' => 'Patient Instructions',
                'date' => '2026-01-25',
                'author' => 'LabSync Customer Care Team',
                'featured_image' => null
            ]
        ];
    }

    // Filter posts based on search and category
    private function filterPosts($posts, $search = '', $category = '', $sort = 'latest') {
        $filtered = $posts;

        // Filter by search
        if (!empty($search)) {
            $filtered = array_filter($filtered, function($post) use ($search) {
                $searchLower = strtolower($search);
                return (
                    strpos(strtolower($post['title']), $searchLower) !== false ||
                    strpos(strtolower($post['excerpt']), $searchLower) !== false ||
                    strpos(strtolower($post['content']), $searchLower) !== false
                );
            });
        }

        // Filter by category
        if (!empty($category) && $category !== 'all') {
            $filtered = array_filter($filtered, function($post) use ($category) {
                return strtolower($post['category']) === strtolower($category);
            });
        }

        // Sort
        if ($sort === 'oldest') {
            usort($filtered, function($a, $b) {
                return strcmp($a['date'], $b['date']);
            });
        } else {
            usort($filtered, function($a, $b) {
                return strcmp($b['date'], $a['date']);
            });
        }

        return $filtered;
    }

    // Format date nicely
    private function formatDate($date) {
        return date('F j, Y', strtotime($date));
    }

    // Get category slug for filtering
    private function getCategorySlug($category) {
        return strtolower(str_replace(' ', '-', $category));
    }

    // Blog list page
    public function index() {
        $posts = $this->getMockPosts();
        
        // Get filter parameters
        $search = $_GET['search'] ?? '';
        $category = $_GET['cat'] ?? '';
        $sort = $_GET['sort'] ?? 'latest';

        // Apply filters
        $posts = $this->filterPosts($posts, $search, $category, $sort);

        // Make available to view
        $filteredPosts = $posts;
        $hasFilters = !empty($search) || !empty($category);
        $searchQuery = $search;
        $activeCategory = $category;
        $activeSort = $sort;

        include VIEW_PATH . '/blog/index.php';
    }

    // Single blog post page
    public function view() {
        $posts = $this->getMockPosts();
        $slug = $_GET['slug'] ?? '';

        // Find post by slug
        $post = null;
        foreach ($posts as $p) {
            if ($p['slug'] === $slug) {
                $post = $p;
                break;
            }
        }

        // If post not found, show error
        if (!$post) {
            $errorMessage = "Post not found";
            include VIEW_PATH . '/blog/not_found.php';
            return;
        }

        include VIEW_PATH . '/blog/view.php';
    }
}
