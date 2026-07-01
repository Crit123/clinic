<?php
$base_url = '/booking-system';
require_once __DIR__ . '/../api/data/services-data.php';

$serviceKey = 'implants';
$serviceLabel = getServiceLabel($serviceKey);
$serviceDuration = getServiceDuration($serviceKey);

if (!$serviceLabel) { header("Location: ../index.php"); exit; }

$heroDesc = "Take the first step towards a complete smile. Schedule a thorough consultation to evaluate your candidacy for permanent, natural-looking dental implants.";
$heroImage = "https://images.unsplash.com/photo-1598256989800-fea5ce5146c1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80";

$overviewText = "A Dental Implants Consultation is a comprehensive evaluation appointment. Dental implants are the gold standard for replacing missing teeth, consisting of titanium posts that fuse with your jawbone. Because this is an advanced surgical procedure, Dr. Santos requires this dedicated hour to assess your anatomical suitability, review your medical history, and map out a custom treatment plan.";
$whyImportantText = "Not everyone is an immediate candidate for implants due to bone density loss or underlying health conditions. This consultation ensures the procedure will be safe, successful, and long-lasting for your specific physiology. It also gives you the opportunity to understand the timeline and investment involved.";
$idealCandidatesText = "Adults who have lost one or more teeth due to injury, decay, or periodontal disease. Patients must have healthy gums and adequate bone to support the implant, or be willing to undergo bone grafting procedures.";
$beforeVisitText = "If you have recent dental X-rays or records from a previous dentist, please bring them or have them emailed to our clinic beforehand. Be prepared to discuss your complete medical history.";
$aftercareText = "As this is a consultation only, there is no physical downtime or aftercare required. We will provide you with a comprehensive informational packet and a clear financial breakdown to review at home.";

$pricingText = "The consultation itself is typically estimated at \$0–\$50 (often waived if you proceed with treatment). A single dental implant, including the post, abutment, and crown, generally runs \$3,000–\$5,000 — your exact cost depends on whether bone grafting or other prep work is needed, which we'll outline clearly after your 3D scan.";

$includedFeatures = [
    ['title' => '3D CBCT Imaging', 'desc' => 'Advanced 3-dimensional scans to evaluate your jawbone volume and nerve pathways.'],
    ['title' => 'Clinical Oral Exam', 'desc' => 'Dr. Santos will evaluate the health of your surrounding teeth and gums.'],
    ['title' => 'Custom Treatment Plan', 'desc' => 'A step-by-step roadmap detailing the timeline, necessary grafting (if any), and recovery.'],
    ['title' => 'Financial Overview', 'desc' => 'A transparent discussion regarding costs, insurance coordination, and financing options.']
];

$processSteps = [
    ['icon' => 'radiology', 'title' => 'Imaging', 'desc' => 'We take high-tech 3D scans of your jaw structure.'],
    ['icon' => 'dentistry', 'title' => 'Assessment', 'desc' => 'Dr. Santos reviews the scans and performs a physical exam.'],
    ['icon' => 'forum', 'title' => 'Discussion', 'desc' => 'We explain your options, timeline, and what to expect.'],
    ['icon' => 'request_quote', 'title' => 'Planning', 'desc' => 'We provide a clear cost breakdown and schedule the procedure.']
];

$benefitsList = [
    ['icon' => 'check_circle', 'title' => 'Clarity & Confidence', 'desc' => 'Fully understand the procedure and risks before committing.'],
    ['icon' => 'architecture', 'title' => 'Precision Planning', 'desc' => '3D imaging allows for flawlessly accurate implant placement mapping.'],
    ['icon' => 'timeline', 'title' => 'No Surprises', 'desc' => 'Clear timelines and transparent pricing presented upfront.']
];

$faqList = [
    ['q' => 'Will I get the implant during this consultation?', 'a' => 'No. This appointment is strictly for evaluation and planning. Implant surgery is a precise procedure that is scheduled for a future date following your consultation.'],
    ['q' => 'How do I know if I have enough bone for implants?', 'a' => 'That is exactly what this consultation determines! Our 3D CBCT scanner allows Dr. Santos to accurately measure your bone density. If it is insufficient, we can discuss bone grafting options.'],
    ['q' => 'Is this consultation covered by insurance?', 'a' => 'Coverage varies significantly between providers. Our front desk will happily verify your benefits prior to your appointment to let you know what is covered.']
];
?>
<!DOCTYPE html>
<html class="light scroll-smooth" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= htmlspecialchars($serviceLabel) ?> - DentalCare Pro</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="Dental implants consultation at DentalCare Pro. 3D CBCT imaging, custom treatment planning, and transparent cost breakdown for permanent tooth replacement.">
    <link rel="canonical" href="https://www.dentalcarepro.example/services/implants.php">
    <meta property="og:title" content="Dental Implants Consultation - DentalCare Pro">
    <meta property="og:description" content="Comprehensive implant evaluation with 3D imaging and a clear treatment timeline.">
    <meta property="og:type" content="website">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="<?= htmlspecialchars($base_url ?? '') ?>/assets/css/responsive.css">
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-background": "#0b1c30",
                        "surface-container-high": "#dce9ff",
                        "on-surface": "#0b1c30",
                        "surface-container-lowest": "#ffffff",
                        "background": "#f8f9ff",
                        "surface-tint": "#005db6",
                        "primary-container": "#005eb8",
                        "surface-container": "#e5eeff",
                        "surface-container-highest": "#d3e4fe",
                        "outline": "#727783",
                        "on-primary": "#ffffff",
                        "outline-variant": "#c2c6d4",
                        "primary": "#00478d",
                        "on-surface-variant": "#424752",
                        "surface": "#f8f9ff",
                        "surface-container-low": "#eff4ff",
                    },
                    fontFamily: { sans: ["Inter"] }
                }
            }
        }
    </script>
    <style>
        .fade-in-up { opacity: 0; transform: translateY(30px); transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
        .fade-in-up.is-visible { opacity: 1; transform: translateY(0); }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
    </style>
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