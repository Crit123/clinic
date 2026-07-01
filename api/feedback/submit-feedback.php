<?php
// Include the canonical services registry for the dropdown
require_once __DIR__ . '/api/data/services-data.php';
$services = getAllServices();

$base_url = '/booking-system'; // Adjust according to your environment setup
?>
<!DOCTYPE html>
<html class="light scroll-smooth scroll-pt-[80px]" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Share Your Feedback - DentalCare Pro</title>
    <meta name="description" content="Share your experience at DentalCare Pro. Only patients with a completed appointment can leave verified feedback."/>
    <link rel="canonical" href="https://yourdomain.com/booking-system/submit-feedback.php" />
    
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
    </style>
</head>
<body class="bg-background text-on-background font-body-md antialiased overflow-x-hidden selection:bg-primary-container selection:text-on-primary-container min-h-screen flex flex-col">

<?php include 'components/header-component.php'; ?>

<main class="pt-24 flex-grow">
    
    <!-- Hero / Title Section -->
    <section class="pt-12 pb-8 fade-in-up">
        <div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop text-center max-w-2xl">
            <span class="inline-block py-1 px-3 rounded-xl bg-primary-container/20 text-primary font-label-sm text-label-sm mb-4 border border-primary/10">Patient Feedback</span>
            <h1 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-on-background mb-4 tracking-tight">Share Your Experience</h1>
            <p class="font-body-md text-body-md text-on-surface-variant">
                To maintain the integrity of our reviews, feedback can only be submitted by patients with a completed appointment on file. <strong>Your email will be used to verify your recent visit securely.</strong>
            </p>
            <div class="mt-4">
                 <a href="<?= $base_url ?>/reviews.php" class="text-primary hover:text-on-primary-fixed-variant hover:underline font-medium text-sm flex items-center justify-center gap-1 group">
                    Read existing patient reviews <span class="material-symbols-outlined text-[18px] group-hover:translate-x-1 transition-transform duration-300">arrow_right_alt</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Feedback Form Container -->
    <section class="pb-xl fade-in-up">
        <div class="max-w-[700px] mx-auto px-margin-mobile md:px-margin-desktop">
            
            <div class="bg-surface-container-lowest rounded-3xl p-6 md:p-10 border border-outline-variant/30 shadow-[0_8px_30px_rgba(0,0,0,0.04)] relative overflow-hidden">
                
                <!-- Background decoration -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-primary/5 to-transparent pointer-events-none"></div>

                <!-- Form Success State (Hidden by default) -->
                <div id="success-message" class="hidden flex-col items-center justify-center text-center py-10 relative z-10">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mb-6">
                        <span class="material-symbols-outlined text-green-600 text-[40px] fill-icon">check_circle</span>
                    </div>
                    <h2 class="text-2xl font-bold text-on-background mb-3">Feedback Received!</h2>
                    <p class="text-on-surface-variant font-body-md mb-8 max-w-md">
                        Thank you for sharing your thoughts with us. Your feedback has been securely submitted and is currently pending review by our team. It will appear on our reviews page shortly.
                    </p>
                    <a href="<?= $base_url ?>/index.php" class="h-12 px-8 rounded-xl bg-surface-container-high text-primary font-label-md text-label-md hover:bg-primary hover:text-white transition-colors duration-300 flex items-center justify-center font-medium">
                        Return to Homepage
                    </a>
                </div>

                <!-- Feedback Form -->
                <form id="feedback-form" class="flex flex-col gap-6 relative z-10">
                    
                    <!-- Form Error Alert (Hidden by default) -->
                    <div id="form-error" class="hidden items-start gap-3 p-4 bg-error-container/30 border border-error/20 rounded-xl text-error text-sm font-medium">
                        <span class="material-symbols-outlined text-[20px] flex-shrink-0">error</span>
                        <span id="form-error-text">An error occurred. Please try again.</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Display Name -->
                        <div class="flex flex-col gap-1.5">
                            <label for="display_name" class="text-sm font-semibold text-on-background">Display Name <span class="text-error">*</span></label>
                            <input type="text" id="display_name" name="display_name" required maxlength="100" placeholder="E.g. John D." 
                                   class="w-full px-4 py-3 rounded-xl bg-surface border border-outline-variant/40 text-on-background placeholder:text-on-surface-variant/50 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-none">
                            <p class="text-xs text-on-surface-variant/70">How your name will appear publicly.</p>
                        </div>

                        <!-- Verification Email -->
                        <div class="flex flex-col gap-1.5">
                            <label for="email" class="text-sm font-semibold text-on-background">Booking Email <span class="text-error">*</span></label>
                            <input type="email" id="email" name="email" required placeholder="email@example.com"
                                   class="w-full px-4 py-3 rounded-xl bg-surface border border-outline-variant/40 text-on-background placeholder:text-on-surface-variant/50 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-none">
                            <p class="text-xs text-on-surface-variant/70">Used for verification only. Will not be published.</p>
                        </div>
                    </div>

                    <!-- Service Dropdown -->
                    <div class="flex flex-col gap-1.5">
                        <label for="service_key" class="text-sm font-semibold text-on-background">Service Received</label>
                        <div class="relative">
                            <select id="service_key" name="service_key" 
                                    class="w-full px-4 py-3 rounded-xl bg-surface border border-outline-variant/40 text-on-background focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-none appearance-none cursor-pointer">
                                <option value="">General Feedback (No specific service)</option>
                                <?php foreach($services as $key => $service): ?>
                                    <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($service['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
                                <span class="material-symbols-outlined text-on-surface-variant">expand_more</span>
                            </div>
                        </div>
                    </div>

                    <!-- Interactive Star Rating -->
                    <div class="flex flex-col gap-2">
                        <label class="text-sm font-semibold text-on-background">Your Rating <span class="text-error">*</span></label>
                        <div class="flex items-center gap-2" id="star-rating" role="radiogroup" aria-required="true" aria-label="Rating from 1 to 5 stars">
                            <!-- Hidden input to store rating value -->
                            <input type="hidden" name="rating" id="rating-input" required>
                            
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <button type="button" class="star-btn transition-transform hover:scale-110 focus:outline-none" data-value="<?= $i ?>" aria-label="<?= $i ?> Star" role="radio" aria-checked="false">
                                    <span class="material-symbols-outlined text-[36px] text-outline-variant/40 pointer-events-none transition-colors duration-200">star</span>
                                </button>
                            <?php endfor; ?>
                        </div>
                        <p class="text-xs text-on-surface-variant/70" id="rating-feedback-text">Please select a rating.</p>
                    </div>

                    <!-- Comment Textarea -->
                    <div class="flex flex-col gap-1.5 mt-2">
                        <div class="flex justify-between items-end">
                            <label for="comment" class="text-sm font-semibold text-on-background">Your Review <span class="text-error">*</span></label>
                            <span class="text-xs font-medium text-on-surface-variant" id="char-counter">0 / 1000</span>
                        </div>
                        <textarea id="comment" name="comment" required rows="5" minlength="10" maxlength="1000" placeholder="Tell us about your visit..."
                                  class="w-full px-4 py-3 rounded-xl bg-surface border border-outline-variant/40 text-on-background placeholder:text-on-surface-variant/50 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all outline-none resize-y"></textarea>
                        <p class="text-xs text-on-surface-variant/70">Minimum 10 characters.</p>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4 border-t border-outline-variant/20">
                        <button type="submit" id="submit-btn" class="w-full h-12 rounded-xl bg-primary text-on-primary font-label-md text-base hover:bg-on-primary-fixed-variant shadow-[0_4px_14px_rgba(0,71,141,0.25)] hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 flex items-center justify-center gap-2">
                            <span>Submit Feedback</span>
                            <span class="material-symbols-outlined text-[20px] hidden" id="submit-spinner">progress_activity</span>
                        </button>
                    </div>
                </form>
            </div>
            
        </div>
    </section>

</main>

<!-- Footer -->
<footer class="w-full py-12 bg-surface-container-highest border-t border-outline-variant/10 mt-auto fade-in-up">
    <div class="max-w-[1200px] mx-auto px-margin-mobile md:px-margin-desktop text-center md:text-left flex flex-col md:flex-row justify-between items-center gap-4">
        <p class="font-body-md text-sm text-on-surface-variant">© 2026 DentalCare Pro Clinic. All rights reserved.</p>
        <div class="flex gap-4">
            <a href="#" class="text-sm text-on-surface-variant hover:text-primary transition-colors">Privacy Policy</a>
            <a href="#" class="text-sm text-on-surface-variant hover:text-primary transition-colors">Terms of Service</a>
        </div>
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', () => {

        // --- Intersection Observer for fade-in animations ---
        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.fade-in-up').forEach(el => observer.observe(el));


        // --- Character Counter Logic ---
        const commentArea = document.getElementById('comment');
        const charCounter = document.getElementById('char-counter');
        const MAX_CHARS = 1000;

        commentArea.addEventListener('input', (e) => {
            const length = e.target.value.length;
            charCounter.textContent = `${length} / ${MAX_CHARS}`;
            
            if (length < 10 && length > 0) {
                charCounter.classList.add('text-error');
                charCounter.classList.remove('text-on-surface-variant', 'text-green-600');
            } else if (length >= 10 && length <= MAX_CHARS) {
                charCounter.classList.add('text-green-600');
                charCounter.classList.remove('text-error', 'text-on-surface-variant');
            } else {
                charCounter.classList.add('text-on-surface-variant');
                charCounter.classList.remove('text-error', 'text-green-600');
            }
        });


        // --- Star Rating Interactive Logic ---
        const starBtns = document.querySelectorAll('.star-btn');
        const ratingInput = document.getElementById('rating-input');
        const ratingFeedback = document.getElementById('rating-feedback-text');
        let currentSelectedRating = 0;

        const ratingDescriptions = {
            1: "Poor - Needs improvement",
            2: "Fair - Below expectations",
            3: "Good - Met expectations",
            4: "Very Good - Great experience",
            5: "Excellent - Highly recommended!"
        };

        function fillStars(ratingToFill) {
            starBtns.forEach(btn => {
                const val = parseInt(btn.getAttribute('data-value'));
                const icon = btn.querySelector('span');
                
                if (val <= ratingToFill) {
                    icon.classList.add('text-yellow-400', 'fill-icon');
                    icon.classList.remove('text-outline-variant/40');
                } else {
                    icon.classList.remove('text-yellow-400', 'fill-icon');
                    icon.classList.add('text-outline-variant/40');
                }
            });
        }

        starBtns.forEach(btn => {
            // Hover logic
            btn.addEventListener('mouseenter', () => {
                const hoverVal = parseInt(btn.getAttribute('data-value'));
                fillStars(hoverVal);
            });

            // Reset to selected state on mouseout
            btn.addEventListener('mouseleave', () => {
                fillStars(currentSelectedRating);
            });

            // Click logic to lock in rating
            btn.addEventListener('click', () => {
                currentSelectedRating = parseInt(btn.getAttribute('data-value'));
                ratingInput.value = currentSelectedRating;
                fillStars(currentSelectedRating);
                
                ratingFeedback.textContent = ratingDescriptions[currentSelectedRating];
                ratingFeedback.classList.add('text-primary', 'font-medium');
                
                // ARIA updates
                starBtns.forEach(b => b.setAttribute('aria-checked', 'false'));
                btn.setAttribute('aria-checked', 'true');
            });
        });


        // --- Form Submission Logic ---
        const feedbackForm = document.getElementById('feedback-form');
        const submitBtn = document.getElementById('submit-btn');
        const submitSpinner = document.getElementById('submit-spinner');
        const btnText = submitBtn.querySelector('span');
        
        const formError = document.getElementById('form-error');
        const formErrorText = document.getElementById('form-error-text');
        
        const successMessage = document.getElementById('success-message');

        // Error message mapping based on API codes
        const errorMessages = {
            'NO_MATCHING_BOOKING': "We couldn't find a completed appointment matching this email address. Please ensure you're using the exact email you booked with.",
            'RATE_LIMITED': "You have recently submitted feedback. Please wait a bit before submitting again.",
            'COMMENT_TOO_SHORT': "Your comment must be at least 10 characters long.",
            'COMMENT_TOO_LONG': "Your comment cannot exceed 1000 characters.",
            'INVALID_RATING': "Please select a star rating between 1 and 5.",
            'INVALID_EMAIL': "Please provide a valid email address.",
            'DEFAULT': "An unexpected error occurred while submitting your feedback. Please try again."
        };

        function showError(code) {
            const message = errorMessages[code] || errorMessages['DEFAULT'];
            formErrorText.textContent = message;
            formError.classList.remove('hidden');
            formError.classList.add('flex');
            // Scroll slightly up to see error
            formError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function hideError() {
            formError.classList.add('hidden');
            formError.classList.remove('flex');
        }

        function setLoading(isLoading) {
            submitBtn.disabled = isLoading;
            if (isLoading) {
                btnText.textContent = 'Verifying & Submitting...';
                submitSpinner.classList.remove('hidden');
                submitSpinner.classList.add('animate-spin');
                submitBtn.classList.add('opacity-80', 'cursor-not-allowed');
            } else {
                btnText.textContent = 'Submit Feedback';
                submitSpinner.classList.add('hidden');
                submitSpinner.classList.remove('animate-spin');
                submitBtn.classList.remove('opacity-80', 'cursor-not-allowed');
            }
        }

        feedbackForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            hideError();

            // Client side custom validation for rating
            if (!ratingInput.value) {
                showError('INVALID_RATING');
                return;
            }

            setLoading(true);

            const formData = {
                display_name: document.getElementById('display_name').value.trim(),
                email: document.getElementById('email').value.trim(),
                service_key: document.getElementById('service_key').value || null,
                rating: parseInt(ratingInput.value, 10),
                comment: document.getElementById('comment').value.trim()
            };

            try {
                // Pointing to the requested API endpoint
                const response = await fetch('<?= $base_url ?>/api/feedback-create.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (response.ok && result.status === 'success') {
                    // Success: Hide form, show confirmation state
                    feedbackForm.classList.add('hidden');
                    successMessage.classList.remove('hidden');
                    successMessage.classList.add('flex');
                    successMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    // Handled API Error
                    showError(result.error_code || 'DEFAULT');
                }
            } catch (error) {
                // Network or parsing error
                console.error("Submission error:", error);
                showError('DEFAULT');
            } finally {
                setLoading(false);
            }
        });

    });
</script>
</body>
</html>