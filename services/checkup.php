<?php
// Global app config — defines $base_url
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../api/data/services-data.php';

$serviceKey = 'checkup';
$serviceLabel = getServiceLabel($serviceKey);
$serviceDuration = getServiceDuration($serviceKey);

if (!$serviceLabel) {
    header("Location: ../index.php");
    exit;
}

// Content Definitions
$heroDesc = "Maintain your optimal oral health with our comprehensive diagnostic and deep cleaning services, designed to prevent complications before they arise.";
$heroImage = "https://images.unsplash.com/photo-1606811841689-23dfddce3e95?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80";

$overviewText = "Our Routine Checkup & Cleaning is the foundation of preventive dental care. During this appointment, our lead practitioner thoroughly evaluates your oral health, identifies potential issues early, and performs a professional deep cleaning to remove plaque and tartar build-up.";
$whyImportantText = "Plaque can harden into tartar, which cannot be removed by regular brushing and flossing. Left untreated, it leads to cavities and gum disease. Regular checkups ensure your teeth and gums stay healthy, ultimately saving you from complex and costly procedures in the future.";
$idealCandidatesText = "Everyone! We recommend all patients, regardless of age or dental history, undergo a routine checkup and cleaning every 6 months to maintain optimal oral hygiene.";
$beforeVisitText = "Please brush your teeth before arriving. If you have any new medical conditions or are taking new medications, please let our team know during your consultation.";
$aftercareText = "You may experience slight gum sensitivity for a few hours. Wait at least 30 minutes before eating or drinking, and continue your standard daily brushing and flossing routine.";
$pricingText = "A Routine Checkup & Cleaning is typically estimated at \$80–\$150. Cleanings without X-rays generally fall at the lower end of that range; first-time visits or cases needing digital X-rays trend toward the higher end.";

$includedFeatures = [
    ['title' => 'Digital X-Rays', 'desc' => 'High-resolution imaging to detect hidden cavities and bone structure issues.'],
    ['title' => 'Plaque & Tartar Removal', 'desc' => 'Professional ultrasonic scaling to clean hard-to-reach areas.'],
    ['title' => 'Enamel Polishing', 'desc' => 'Removes surface stains and leaves your teeth feeling exceptionally smooth.'],
    ['title' => 'Comprehensive Exam', 'desc' => 'Dr. Santos will check for signs of decay, gum disease, and oral cancer.']
];

$processSteps = [
    ['icon' => 'radiology', 'title' => 'Imaging', 'desc' => 'We begin with comfortable digital X-Rays to get a full view.'],
    ['icon' => 'water_drop', 'title' => 'Cleaning', 'desc' => 'Hygienic plaque and tartar removal using ultrasonic tools.'],
    ['icon' => 'health_and_safety', 'title' => 'Examination', 'desc' => 'A thorough clinical assessment by Dr. Santos.'],
    ['icon' => 'forum', 'title' => 'Consultation', 'desc' => 'We discuss our findings and answer your questions.']
];

$benefitsList = [
    ['icon' => 'shield', 'title' => 'Preventive Care', 'desc' => 'Stop cavities and periodontal disease before they start.'],
    ['icon' => 'air', 'title' => 'Fresher Breath', 'desc' => 'Eliminates bacteria that cause chronic bad breath (halitosis).'],
    ['icon' => 'savings', 'title' => 'Cost Effective', 'desc' => 'Early detection prevents the need for expensive restorative work.']
];

$faqList = [
    ['q' => 'How often should I get a checkup and cleaning?', 'a' => 'The American Dental Association recommends a routine checkup and professional cleaning every 6 months. Patients with a history of periodontal disease may need to visit every 3 to 4 months.'],
    ['q' => 'Does the deep cleaning process hurt?', 'a' => 'For most patients, a standard cleaning is completely painless. If you have sensitive gums or a heavy buildup of tartar, you may feel slight discomfort. We always ensure you are as comfortable as possible and can offer numbing options if needed.'],
    ['q' => 'Will a cleaning make my teeth whiter?', 'a' => 'While a cleaning removes surface stains and tartar (which can be yellowish), it doesn\'t change the natural color of your enamel. If you are looking for significantly whiter teeth, consider our Teeth Whitening service!']
];
?>
<!DOCTYPE html>
<html class="light scroll-smooth scroll-pt-[80px]" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= htmlspecialchars($serviceLabel) ?> - DentalCare Pro</title>
    
    <!-- SEO Meta Tags specific to Routine Checkup & Cleaning -->
    <meta name="description" content="Routine dental checkups and professional cleaning at DentalCare Pro. Digital X-rays, plaque removal, and a full oral health exam in 30–45 minutes.">
    <link rel="canonical" href="https://www.dentalcarepro.example/services/checkup.php">
    <meta property="og:title" content="Routine Checkup & Cleaning - DentalCare Pro">
    <meta property="og:description" content="Preventive dental care with digital X-rays, professional cleaning, and a comprehensive exam.">
    <meta property="og:type" content="website">

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link rel="stylesheet" href="<?= htmlspecialchars($base_url ?? '') ?>/assets/css/responsive.css">
    <script src="<?= htmlspecialchars($base_url ?? '') ?>/assets/js/theme-config.js"></script>
    <link rel="stylesheet" href="<?= htmlspecialchars($base_url ?? '') ?>/assets/css/theme-base.css">
</head>
<body class="bg-background text-on-background font-sans antialiased overflow-x-hidden">

<?php include __DIR__ . '/../components/header-component.php'; ?>
<main>
    <?php include __DIR__ . '/components/service-hero.php'; ?>
    <?php include __DIR__ . '/components/service-overview.php'; ?>
    <?php include __DIR__ . '/components/service-process.php'; ?>
    <?php include __DIR__ . '/components/service-benefits.php'; ?>
    <?php include __DIR__ . '/components/service-faq.php'; ?>
    <?php 
        $currentServiceKey = $serviceKey;
        include __DIR__ . '/components/service-related.php'; 
    ?>
    <?php include __DIR__ . '/components/services-cta.php'; ?>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.fade-in-up').forEach(el => observer.observe(el));
    });
</script>
</body>
</html>