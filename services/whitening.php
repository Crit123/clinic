<?php
// Global app config — defines $base_url
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../api/data/services-data.php';

$serviceKey = 'whitening';
$serviceLabel = getServiceLabel($serviceKey);
$serviceDuration = getServiceDuration($serviceKey);

if (!$serviceLabel) { header("Location: ../index.php"); exit; }

$heroDesc = "Transform your smile with our professional-grade laser whitening treatments, designed to safely and effectively lift stubborn stains in just one session.";
$heroImage = "https://images.unsplash.com/photo-1590623693240-a15d221cd3d9?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80";

$overviewText = "Our Teeth Whitening service provides rapid, stunning cosmetic enhancements using the latest laser activation technology. Unlike over-the-counter strips, professional whitening occurs in a controlled environment, ensuring maximum brightness while protecting your enamel and gums.";
$whyImportantText = "A bright smile drastically improves self-confidence and leaves a lasting positive impression. Everyday habits like drinking coffee, tea, and red wine can dull your teeth over time. Professional whitening restores that youthful, vibrant appearance safely.";
$idealCandidatesText = "Ideal for individuals with healthy, unrestored teeth and healthy gums who want a brighter smile. It is highly recommended before major social events, weddings, or important professional meetings.";
$beforeVisitText = "We highly recommend having a Routine Checkup & Cleaning a few weeks prior to your whitening session. Clean, tartar-free teeth respond significantly better to the whitening gel.";
$aftercareText = "Avoid staining foods and beverages (like coffee, red wine, soy sauce, and dark berries) for at least 48 hours post-treatment. We will provide a specialized sensitivity toothpaste if you experience any mild temporary discomfort.";

$pricingText = "Professional laser whitening is typically estimated at \$250–\$450 for a single session, depending on your starting shade and desired results.";

$includedFeatures = [
    ['title' => 'Shade Assessment', 'desc' => 'We measure your starting shade to track the exact level of improvement.'],
    ['title' => 'Gum Protection Barrier', 'desc' => 'Application of a protective gel to shield your gums from the bleaching agent.'],
    ['title' => 'Laser Whitening Session', 'desc' => 'Targeted light therapy combined with professional-grade hydrogen peroxide gel.'],
    ['title' => 'Desensitizing Treatment', 'desc' => 'A post-whitening fluoride application to strengthen enamel and reduce sensitivity.']
];

$processSteps = [
    ['icon' => 'palette', 'title' => 'Assessment', 'desc' => 'We match your current shade and discuss your desired outcome.'],
    ['icon' => 'security', 'title' => 'Preparation', 'desc' => 'Your lips and gums are safely isolated and protected.'],
    ['icon' => 'flare', 'title' => 'Whitening', 'desc' => 'Gel is applied and activated by our specialized laser in intervals.'],
    ['icon' => 'mood', 'title' => 'Reveal', 'desc' => 'We remove the gel and reveal your newly brightened smile!']
];

$benefitsList = [
    ['icon' => 'bolt', 'title' => 'Immediate Results', 'desc' => 'Leave the clinic with teeth several shades whiter in just under an hour.'],
    ['icon' => 'verified_user', 'title' => 'Safe & Supervised', 'desc' => 'Performed by professionals, preventing the enamel damage common with DIY kits.'],
    ['icon' => 'star', 'title' => 'Boosted Confidence', 'desc' => 'Enjoy the psychological benefits of a radiant, camera-ready smile.']
];

$faqList = [
    ['q' => 'How long do the whitening results last?', 'a' => 'Results typically last between 1 to 3 years. This largely depends on your lifestyle habits. Minimizing tobacco use and dark-colored beverages will prolong your bright smile.'],
    ['q' => 'Will it make my teeth sensitive?', 'a' => 'Some patients experience mild sensitivity for 24-48 hours. However, our professional process includes a protective barrier and post-treatment desensitizing gel to keep discomfort to an absolute minimum.'],
    ['q' => 'Does it work on crowns or veneers?', 'a' => 'No, whitening agents only work on natural tooth enamel. If you have visible restorations, we can discuss replacing them to match your new, whiter shade.']
];
?>
<!DOCTYPE html>
<html class="light scroll-smooth scroll-pt-[80px]" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta name="description" content="Professional laser teeth whitening at DentalCare Pro. Safe, supervised treatment with visible results in under an hour.">
    <link rel="canonical" href="https://www.dentalcarepro.example/services/whitening.php">
    <meta property="og:title" content="Teeth Whitening - DentalCare Pro">
    <meta property="og:description" content="Laser-activated professional whitening for a brighter, camera-ready smile.">
    <meta property="og:type" content="website">
    <title><?= htmlspecialchars($serviceLabel) ?> - DentalCare Pro</title>
    
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
    <?php $currentServiceKey = $serviceKey; include __DIR__ . '/components/service-related.php'; ?>
    <?php include __DIR__ . '/components/services-cta.php'; ?>
</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) { entry.target.classList.add('is-visible'); obs.unobserve(entry.target); }
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.fade-in-up').forEach(el => observer.observe(el));
    });
</script>
</body>
</html>