<?php
// Include canonical services for dynamic filter generation
require_once __DIR__ . '/api/data/services-data.php';
$services = getAllServices();

$base_url = '/booking-system'; // Base path
?>
<!DOCTYPE html>
<html class="light scroll-smooth scroll-pt-[80px]" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    
    <!-- SEO Meta Tags -->
    <title>Patient Reviews - DentalCare Pro</title>
    <meta name="description" content="Read real reviews and feedback from DentalCare Pro patients. See why thousands trust us for their exceptional dental care."/>
    <link rel="canonical" href="https://dentalcarepro.example/reviews.php" />
    <meta property="og:title" content="Patient Reviews - DentalCare Pro" />
    <meta property="og:description" content="Read real reviews and feedback from DentalCare Pro patients. See why thousands trust us for their exceptional dental care." />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://dentalcarepro.example/reviews.php" />

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-background": "#0b1c30",
                        "on-primary-fixed": "#001b3d",
                        "surface-container-high": "#dce9ff",
                        "on-secondary-fixed": "#191c1e",
                        "on-surface": "#0b1c30",
                        "secondary-fixed-dim": "#c4c7c9",
                        "on-primary-fixed-variant": "#00468c",
                        "secondary": "#5c5f61",
                        "surface-container-lowest": "#ffffff",
                        "background": "#f8f9ff",
                        "surface-tint": "#005db6",
                        "on-tertiary-fixed": "#002113",
                        "error": "#ba1a1a",
                        "primary-container": "#005eb8",
                        "inverse-primary": "#a9c7ff",
                        "surface-container": "#e5eeff",
                        "on-tertiary-container": "#65f2b5",
                        "surface-container-highest": "#d3e4fe",
                        "outline": "#727783",
                        "on-primary": "#ffffff",
                        "outline-variant": "#c2c6d4",
                        "inverse-on-surface": "#eaf1ff",
                        "primary-fixed-dim": "#a9c7ff",
                        "surface-bright": "#f8f9ff",
                        "on-primary-container": "#c8daff",
                        "on-tertiary-fixed-variant": "#005236",
                        "primary": "#00478d",
                        "error-container": "#ffdad6",
                        "on-secondary": "#ffffff",
                        "surface-container-low": "#eff4ff",
                        "tertiary-fixed": "#6ffbbe",
                        "on-surface-variant": "#424752",
                        "on-tertiary": "#ffffff",
                        "tertiary-fixed-dim": "#4edea3",
                        "tertiary": "#005237",
                        "surface": "#f8f9ff",
                        "on-error-container": "#93000a",
                        "surface-variant": "#d3e4fe",
                        "surface-dim": "#cbdbf5",
                        "on-secondary-fixed-variant": "#444749",
                        "primary-fixed": "#d6e3ff",
                        "inverse-surface": "#213145",
                        "on-error": "#ffffff",
                        "secondary-fixed": "#e0e3e5",
                        "secondary-container": "#e0e3e5",
                        "tertiary-container": "#006d4a",
                        "on-secondary-container": "#626567"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "2xl": "1rem",
                        "3xl": "1.5rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "gutter": "24px",
                        "md": "24px",
                        "margin-mobile": "16px",
                        "xs": "4px",
                        "lg": "48px",
                        "xl": "80px",
                        "margin-desktop": "64px",
                        "sm": "12px",
                        "base": "8px"
                    },
                    "fontFamily": {
                        "label-sm": ["Inter"],
                        "body-md": ["Inter"],
                        "headline-md": ["Inter"],
                        "headline-xl": ["Inter"],
                        "body-lg": ["Inter"],
                        "headline-lg-mobile": ["Inter"],
                        "headline-lg": ["Inter"],
                        "label-md": ["Inter"]
                    }
                }
            }
        }
    </script>
    
    <style>
        .fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 1s cubic-bezier(0.16, 1, 0.3, 1), transform 1s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .fade-in-up.is-visible {
            opacity: 1;
            transform: translateY(0);
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .material-symbols-outlined.fill-icon {
            font-variation-settings: 'FILL' 1;
        }
        .gradient-text {
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .loader-shimmer {
            background: linear-gradient(90deg, #f0f4f8 25%, #e0e7ff 50%, #f0f4f8 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }
        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
</head>
<body class="bg-background text-on-background font-body-md antialiased selection:bg-primary-container selection:text-on-primary-container">

<?php include 'components/header-component.php'; ?>

<main class="pt-20 md:pt-24 min-h-screen flex flex-col">
    <!-- Hero Section -->
    <section class="relative bg-surface-container-low py-16 md:py-24 border-b border-outline-variant/20 overflow-hidden fade-in-up">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-primary/5 via-transparent to-transparent pointer-events-none"></div>
        <div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop relative z-10 flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="max-w-xl text-center md:text-left">
                <span class="inline-block py-1 px-3 rounded-xl bg-primary-container/20 text-primary font-label-sm text-label-sm mb-4 border border-primary/10">Community Feedback</span>
                <h1 class="font-headline-xl text-4xl md:text-5xl text-on-background mb-4 tracking-tight font-bold">
                    Patient <span class="bg-gradient-to-r from-primary to-surface-tint gradient-text">Reviews</span>
                </h1>
                <p class="font-body-lg text-lg text-on-surface-variant leading-relaxed">
                    Read genuine experiences from our community. We pride ourselves on transparent, patient-first care and your feedback drives our continuous improvement.
                </p>
            </div>
            
            <!-- Dynamic Stats Card -->
            <div class="bg-surface-container-lowest rounded-3xl p-6 md:p-8 border border-outline-variant/30 shadow-[0_10px_40px_rgba(0,0,0,0.03)] flex flex-col items-center justify-center min-w-[280px]">
                <div id="stats-loader" class="flex flex-col items-center gap-3 w-full">
                    <div class="h-10 w-24 rounded-lg loader-shimmer"></div>
                    <div class="h-6 w-32 rounded-lg loader-shimmer"></div>
                    <div class="h-4 w-40 rounded-lg loader-shimmer"></div>
                </div>
                
                <div id="stats-content" class="hidden flex flex-col items-center gap-2">
                    <span id="stats-avg" class="font-headline-xl text-5xl text-primary font-bold tracking-tight">--</span>
                    <div id="stats-stars" class="flex gap-1">
                        <!-- Populated by JS -->
                    </div>
                    <p class="font-label-md text-sm text-on-surface-variant font-medium mt-1">
                        Based on <span id="stats-count" class="font-bold text-on-background">--</span> reviews
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Filters & Reviews Container -->
    <section class="py-12 flex-grow bg-background">
        <div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop">
            
            <!-- Controls Toolbar -->
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-8 bg-surface-container-lowest p-4 rounded-2xl border border-outline-variant/30 shadow-sm fade-in-up">
                
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <span class="material-symbols-outlined text-on-surface-variant text-[20px]">filter_list</span>
                    <span class="font-semibold text-sm text-on-background mr-2">Filter:</span>
                    <select id="service-filter" class="flex-grow sm:flex-grow-0 bg-surface border border-outline-variant/40 text-on-surface text-sm rounded-xl px-4 py-2 focus:ring-2 focus:ring-primary/50 focus:border-primary outline-none transition-all shadow-sm">
                        <option value="">All Services</option>
                        <?php foreach($services as $key => $service): ?>
                            <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($service['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <span class="material-symbols-outlined text-on-surface-variant text-[20px]">sort</span>
                    <span class="font-semibold text-sm text-on-background mr-2">Sort:</span>
                    <select id="sort-filter" class="flex-grow sm:flex-grow-0 bg-surface border border-outline-variant/40 text-on-surface text-sm rounded-xl px-4 py-2 focus:ring-2 focus:ring-primary/50 focus:border-primary outline-none transition-all shadow-sm">
                        <option value="recent">Most Recent</option>
                        <option value="highest">Highest Rated</option>
                        <option value="lowest">Lowest Rated</option>
                    </select>
                </div>

            </div>

            <!-- Reviews Grid -->
            <div id="reviews-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 fade-in-up">
                <!-- Skeletons (Initial Load) -->
                <div class="bg-surface-container-lowest rounded-3xl p-6 border border-outline-variant/20 h-64 loader-shimmer"></div>
                <div class="bg-surface-container-lowest rounded-3xl p-6 border border-outline-variant/20 h-64 loader-shimmer"></div>
                <div class="bg-surface-container-lowest rounded-3xl p-6 border border-outline-variant/20 h-64 loader-shimmer"></div>
            </div>

            <!-- Empty State -->
            <div id="empty-state" class="hidden flex flex-col items-center justify-center py-20 text-center fade-in-up">
                <div class="w-20 h-20 rounded-full bg-surface-container flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-outline text-[40px]">speaker_notes_off</span>
                </div>
                <h3 class="font-headline-md text-xl font-bold text-on-background mb-2">No reviews found</h3>
                <p class="text-on-surface-variant mb-6 max-w-md">We couldn't find any reviews matching your selected criteria. Try clearing your filters or be the first to share your experience!</p>
                <button onclick="document.getElementById('service-filter').value=''; document.getElementById('sort-filter').value='recent'; fetchReviews();" class="px-6 py-2.5 bg-surface text-primary font-medium rounded-xl border border-outline-variant/30 hover:bg-surface-container transition-colors">
                    Clear Filters
                </button>
            </div>
            
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-16 bg-surface-container border-t border-outline-variant/20 fade-in-up">
        <div class="max-w-3xl mx-auto px-margin-mobile md:px-margin-desktop text-center">
            <div class="w-16 h-16 bg-primary-container rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-sm">
                <span class="material-symbols-outlined text-primary text-[32px] fill-icon">rate_review</span>
            </div>
            <h2 class="font-headline-lg text-3xl font-bold text-on-background mb-4">Share Your Experience</h2>
            <p class="font-body-md text-on-surface-variant mb-8 text-lg">Your feedback helps us continuously elevate our standard of care and helps other patients make informed decisions.</p>
            <a href="<?= $base_url ?>/submit-feedback.php" class="inline-flex items-center gap-2 h-14 px-8 rounded-xl bg-primary text-on-primary font-label-md font-bold text-base hover:bg-on-primary-fixed-variant hover:shadow-[0_8px_20px_rgba(0,71,141,0.25)] hover:-translate-y-0.5 transition-all duration-300">
                Write a Review
                <span class="material-symbols-outlined text-[20px]">edit_document</span>
            </a>
        </div>
    </section>
</main>

<?php include 'components/footer-component.php'; // Reusing standard footer structure from index.php ?>
<!-- Fallback footer inline in case component isn't strictly available in test environment -->
<?php if (!file_exists('components/footer-component.php')): ?>
<footer class="w-full py-xl bg-surface-container-highest fade-in-up border-t border-outline-variant/10">
    <div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
            <div class="flex flex-col gap-4">
                <span class="font-headline-md text-2xl font-bold text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-[32px]">dentistry</span> DentalCare Pro
                </span>
                <p class="font-body-md text-sm text-on-surface-variant max-w-xs mt-2">
                    Exceptional, precision-driven dental care in a state-of-the-art, relaxing environment.
                </p>
            </div>
            <div class="flex flex-col gap-4">
                <h4 class="font-bold text-on-background">Quick Navigation</h4>
                <nav class="flex flex-col gap-2">
                    <a class="text-on-surface-variant hover:text-primary hover:translate-x-1 transition-all text-sm w-fit" href="<?= $base_url ?>/index.php#home">Home</a>
                    <a class="text-on-surface-variant hover:text-primary hover:translate-x-1 transition-all text-sm w-fit" href="<?= $base_url ?>/services/services.php">Our Services</a>
                    <a class="text-on-surface-variant hover:text-primary hover:translate-x-1 transition-all text-sm w-fit" href="<?= $base_url ?>/reviews.php">Patient Reviews</a>
                </nav>
            </div>
            <div class="flex flex-col gap-4">
                <h4 class="font-bold text-on-background">Patient Resources</h4>
                <nav class="flex flex-col gap-2">
                    <a class="text-on-surface-variant hover:text-primary hover:translate-x-1 transition-all text-sm w-fit" href="<?= $base_url ?>/submit-feedback.php">Submit Feedback</a>
                    <a class="text-on-surface-variant hover:text-primary hover:translate-x-1 transition-all text-sm w-fit" href="<?= $base_url ?>/index.php#faq">FAQ</a>
                </nav>
            </div>
            <div class="flex flex-col gap-4">
                <h4 class="font-bold text-on-background">Get in Touch</h4>
                <p class="text-sm text-on-surface-variant flex items-center gap-2"><span class="material-symbols-outlined text-[18px] text-primary">call</span> 555-0148</p>
            </div>
        </div>
        <div class="pt-8 border-t border-outline-variant/20 flex flex-col md:flex-row justify-between items-center gap-4 text-center md:text-left">
            <p class="text-sm text-on-surface-variant">© 2026 DentalCare Pro Clinic. All rights reserved.</p>
        </div>
    </div>
</footer>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', () => {
    
    const serviceFilter = document.getElementById('service-filter');
    const sortFilter = document.getElementById('sort-filter');
    const reviewsGrid = document.getElementById('reviews-grid');
    const emptyState = document.getElementById('empty-state');
    
    // Stats elements
    const statsLoader = document.getElementById('stats-loader');
    const statsContent = document.getElementById('stats-content');
    const statsAvg = document.getElementById('stats-avg');
    const statsCount = document.getElementById('stats-count');
    const statsStars = document.getElementById('stats-stars');

    // API Path (assuming the script runs at root level like index.php)
    const API_ENDPOINT = '<?= $base_url ?>/api/feedback-list.php';

    // Helper: Create Star HTML
    const renderStarsHTML = (rating, sizeClass = 'text-[20px]') => {
        let html = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= Math.floor(rating)) {
                html += `<span class="material-symbols-outlined text-yellow-500 ${sizeClass} fill-icon">star</span>`;
            } else if (i === Math.ceil(rating) && !Number.isInteger(rating)) {
                html += `<span class="material-symbols-outlined text-yellow-500 ${sizeClass} fill-icon">star_half</span>`;
            } else {
                html += `<span class="material-symbols-outlined text-outline-variant ${sizeClass}">star</span>`;
            }
        }
        return html;
    };

    // Helper: Generate Initials
    const getInitials = (name) => {
        if (!name) return 'A';
        const parts = name.split(' ');
        if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
        return parts[0].substring(0, 2).toUpperCase();
    };

    // Build Single Review Card
    const createReviewCard = (review) => {
        const div = document.createElement('div');
        div.className = "bg-surface rounded-3xl p-8 border border-outline-variant/30 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex flex-col gap-4 relative overflow-hidden group";
        
        // Background decorative blob
        div.innerHTML = `
            <div class="absolute top-0 right-0 w-24 h-24 bg-primary/5 rounded-bl-full -z-0 group-hover:scale-125 transition-transform duration-500 pointer-events-none"></div>
        `;

        const contentWrapper = document.createElement('div');
        contentWrapper.className = "relative z-10 flex flex-col h-full";

        // Header: Stars & New Badge & Date
        const headerRow = document.createElement('div');
        headerRow.className = "flex justify-between items-start mb-2";
        
        const starsDiv = document.createElement('div');
        starsDiv.className = "flex gap-1";
        starsDiv.innerHTML = renderStarsHTML(review.rating, 'text-[18px]');
        
        const rightMeta = document.createElement('div');
        rightMeta.className = "flex flex-col items-end gap-1";
        
        if (review.is_new) {
            rightMeta.innerHTML += `<span class="px-2 py-0.5 bg-primary-container text-primary text-[10px] uppercase font-bold rounded-full tracking-wide">New</span>`;
        }
        rightMeta.innerHTML += `<span class="text-xs text-on-surface-variant font-medium">${review.relative_time || review.created_at}</span>`;

        headerRow.appendChild(starsDiv);
        headerRow.appendChild(rightMeta);
        contentWrapper.appendChild(headerRow);

        // Comment
        const comment = document.createElement('p');
        comment.className = "font-body-md text-on-surface-variant italic flex-grow leading-relaxed mt-2";
        comment.textContent = `"${review.comment}"`;
        contentWrapper.appendChild(comment);

        // Footer: User Info & Service Label
        const footer = document.createElement('div');
        footer.className = "flex items-center gap-3 pt-5 mt-5 border-t border-outline-variant/20";
        
        const avatar = document.createElement('div');
        avatar.className = "w-11 h-11 rounded-full bg-surface-container-high flex items-center justify-center text-primary font-bold text-sm flex-shrink-0 border border-primary/10";
        avatar.textContent = getInitials(review.display_name);
        
        const userInfo = document.createElement('div');
        const nameNode = document.createElement('p');
        nameNode.className = "font-label-md text-on-surface font-bold text-sm";
        nameNode.textContent = review.display_name;
        userInfo.appendChild(nameNode);
        
        if (review.service_label) {
            const serviceNode = document.createElement('p');
            serviceNode.className = "font-label-sm text-xs text-on-surface-variant flex items-center gap-1 mt-0.5";
            serviceNode.innerHTML = `<span class="material-symbols-outlined text-[14px] text-green-600">verified</span> ${review.service_label}`;
            userInfo.appendChild(serviceNode);
        }

        footer.appendChild(avatar);
        footer.appendChild(userInfo);
        contentWrapper.appendChild(footer);

        div.appendChild(contentWrapper);
        return div;
    };

    // Fetch and Render
    const fetchReviews = async () => {
        const service = serviceFilter.value;
        const sort = sortFilter.value;

        // Show loading state in grid
        reviewsGrid.innerHTML = `
            <div class="bg-surface-container-lowest rounded-3xl p-6 border border-outline-variant/20 h-64 loader-shimmer"></div>
            <div class="bg-surface-container-lowest rounded-3xl p-6 border border-outline-variant/20 h-64 loader-shimmer"></div>
            <div class="bg-surface-container-lowest rounded-3xl p-6 border border-outline-variant/20 h-64 loader-shimmer"></div>
        `;
        emptyState.classList.add('hidden');

        try {
            const url = new URL(API_ENDPOINT, window.location.origin);
            if (service) url.searchParams.append('service_key', service);
            if (sort) url.searchParams.append('sort', sort);

            const res = await fetch(url);
            if (!res.ok) throw new Error('Network response was not ok');
            const result = await res.json();

            if (result.status === 'success') {
                const { stats, reviews } = result.data;

                // Update Hero Stats
                statsLoader.classList.add('hidden');
                statsContent.classList.remove('hidden');
                statsContent.classList.add('flex');
                
                statsAvg.textContent = stats.average > 0 ? stats.average.toFixed(1) : '0.0';
                statsCount.textContent = stats.total;
                statsStars.innerHTML = renderStarsHTML(stats.average, 'text-[24px]');

                // Render Grid
                reviewsGrid.innerHTML = '';
                if (!reviews || reviews.length === 0) {
                    emptyState.classList.remove('hidden');
                } else {
                    reviews.forEach(review => {
                        reviewsGrid.appendChild(createReviewCard(review));
                    });
                }
            } else {
                throw new Error(result.message || 'Failed to fetch reviews');
            }
        } catch (error) {
            console.error('Error loading reviews:', error);
            reviewsGrid.innerHTML = '';
            emptyState.classList.remove('hidden');
            emptyState.querySelector('h3').textContent = "Something went wrong";
            emptyState.querySelector('p').textContent = "We couldn't load the reviews at this time. Please try again later.";
            emptyState.querySelector('button').textContent = "Try Again";
        }
    };

    // Event Listeners for Filters
    serviceFilter.addEventListener('change', fetchReviews);
    sortFilter.addEventListener('change', fetchReviews);

    // Initial Fetch
    fetchReviews();

    // Intersection Observer for fade-in animations (same as index.php)
    const observerOptions = { root: null, rootMargin: '0px', threshold: 0.1 };
    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                obs.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.fade-in-up').forEach(el => observer.observe(el));
});
</script>

</body>
</html>