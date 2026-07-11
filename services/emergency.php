<?php
// Global app config — defines $base_url
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../api/data/services-data.php';

$serviceKey = 'emergency';
$serviceLabel = getServiceLabel($serviceKey);
$serviceDuration = getServiceDuration($serviceKey);
$isEmergency = true; // Triggers the highlight box in the overview and emergency CTAs in components

if (!$serviceLabel) { header("Location: ../index.php"); exit; }

$heroDesc = "Immediate, compassionate care for severe toothaches, broken teeth, and urgent dental injuries. Call us during business hours for the fastest response. We prioritize getting you out of pain fast with priority emergency triage.";
$heroImage = "https://images.unsplash.com/photo-1588776814546-1ffcf47267a5?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80";

$overviewText = "Dental emergencies can be frightening and intensely painful. Our priority emergency slots are specifically reserved in our daily schedule to handle urgent, unforeseen dental problems. Our primary immediate goal is to accurately diagnose the problem, stabilize the tooth or tissue, and most importantly, relieve your pain.";

$pricingText = "Emergency exam and pain-relief visits are typically estimated at \$75–\$150 for the initial assessment and X-ray. Any further treatment needed that same visit — such as an extraction or temporary filling — is quoted separately before we proceed.";

$whyImportantText = "Time is critical in dental emergencies. A knocked-out tooth can often be saved if treated within an hour. Severe infections (abscesses) can spread to other parts of the body if ignored. Seeking prompt professional care minimizes permanent damage and long-term costs.";

$idealCandidatesText = "Anyone experiencing unbearable tooth pain, persistent bleeding from the mouth, a cracked or knocked-out tooth, or severe facial swelling during our regular clinic hours.";
$beforeVisitText = "If a tooth has been knocked out, hold it by the crown (not the root), gently rinse it with water, and try to place it back in the socket. If you cannot, keep it moist in a small cup of milk while you travel to the clinic.";
$aftercareText = "After stabilization, you may be prescribed antibiotics or pain medication. A follow-up appointment is almost always required to complete the permanent restorative work.";

$includedFeatures = [
    ['title' => 'Priority Triage', 'desc' => 'You bypass the standard waiting list for immediate evaluation during business hours.'],
    ['title' => 'Targeted Imaging', 'desc' => 'Focused X-rays of the affected area to locate the source of pain or trauma.'],
    ['title' => 'Pain Management', 'desc' => 'Immediate administration of local anesthetics or palliative care.'],
    ['title' => 'Emergency Intervention', 'desc' => 'Temporary restorations, extractions, or initial root canal therapy as required.']
];

$processSteps = [
    ['icon' => 'call', 'title' => 'Contact Us', 'desc' => 'Call us immediately during business hours to secure a priority assessment slot.'],
    ['icon' => 'healing', 'title' => 'Pain Relief', 'desc' => 'Upon arrival, our first priority is numbing the area and managing your pain.'],
    ['icon' => 'search', 'title' => 'Diagnosis', 'desc' => 'We perform rapid targeted imaging to assess the damage.'],
    ['icon' => 'medical_services', 'title' => 'Stabilization', 'desc' => 'We treat the immediate threat and plan any permanent follow-up care.']
];

$benefitsList = [
    ['icon' => 'timer', 'title' => 'Fast Relief', 'desc' => 'Get out of debilitating pain as quickly as possible.'],
    ['icon' => 'health_and_safety', 'title' => 'Save Your Tooth', 'desc' => 'Prompt action dramatically increases the chances of saving injured teeth.'],
    ['icon' => 'medication', 'title' => 'Infection Control', 'desc' => 'Immediate antibiotic intervention stops dangerous oral infections from spreading.']
];

$faqList = [
    ['q' => 'What qualifies as a dental emergency?', 'a' => 'Severe pain, excessive bleeding, a knocked-out permanent tooth, a severely broken tooth exposing nerves, and swelling of the jaw or face are all emergencies. A minor chipped tooth with no pain can usually wait for a regular appointment.'],
    ['q' => 'Should I go to the hospital ER instead?', 'a' => 'Hospital ERs generally cannot pull or repair teeth, but they can provide pain medication and antibiotics. Go to the ER immediately if your swelling impairs your breathing or swallowing, or if you suspect a broken jaw.'],
    ['q' => 'I don\'t have insurance. Can I still be seen?', 'a' => 'Yes. We never turn away patients in severe pain. We offer transparent emergency assessment fees and will discuss the cost of any necessary procedures before performing them.']
];
?>
<!DOCTYPE html>
<html class="light scroll-smooth scroll-pt-[80px]" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= htmlspecialchars($serviceLabel) ?> - DentalCare Pro</title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="Priority emergency dental care for severe pain, broken teeth, and dental trauma at DentalCare Pro. Fast pain relief during business hours.">
    <link rel="canonical" href="https://www.dentalcarepro.example/services/emergency.php">
    <meta property="og:title" content="Emergency Dental Care - DentalCare Pro">
    <meta property="og:description" content="Priority emergency dental appointments for urgent pain, trauma, and infections.">
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