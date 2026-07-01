<?php session_start(); ?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Booking Confirmed - DentalCare Pro</title>
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
                        "error": "#ba1a1a",
                        "primary-container": "#005eb8",
                        "primary": "#00478d"
                    },
                    "fontFamily": {
                        "sans": ["Inter"]
                    }
                }
            }
        }
    </script>
<style>
    :root { --primary-color: #00478d; }
    
    .fade-in { animation: fadeIn 0.4s ease-out forwards; }
    @keyframes fadeIn { 0% { opacity: 0; } 100% { opacity: 1; } }

    /* Page Entry Animation */
    @keyframes pageEnter {
        0% { opacity: 0; transform: translateY(16px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .page-enter {
        animation: pageEnter 400ms cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    /* Page Exit Animation */
    @keyframes pageExit {
        0% { opacity: 1; transform: translateY(0); }
        100% { opacity: 0; transform: translateY(-12px); }
    }
    .page-exit {
        animation: pageExit 250ms ease-in forwards;
    }

    /* Stagger Animation for Content Blocks */
    @keyframes staggerEnter {
        0% { opacity: 0; transform: translateY(10px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .stagger-item {
        opacity: 0;
        animation: staggerEnter 300ms ease-out forwards;
    }

    /* Success Icon Bounce Animation */
    @keyframes iconBounce {
        0% { transform: scale(0); }
        60% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
    .icon-bounce {
        animation: iconBounce 400ms cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
    }
</style>
</head>
<body class="bg-background text-on-background antialiased min-h-screen flex flex-col font-sans" style="opacity: 0;">

<?php
// Fallback if component is missing to maintain visual layout constraint
if (file_exists('components/header-component.php')) {
    include 'components/header-component.php';
}
?>

<main class="flex-grow pt-[96px] sm:pt-[106px] pb-24 sm:pb-16 px-4 md:px-8 max-w-[1440px] mx-auto w-full flex flex-col justify-center">

    <!-- Loading State -->
    <div id="loading-state" class="text-center max-w-2xl mx-auto mt-10 fade-in">
        <span class="material-symbols-outlined animate-spin text-primary text-[40px] mb-4">refresh</span>
        <h2 class="text-xl font-bold text-slate-800">Verifying Booking Database...</h2>
    </div>

    <!-- Error State -->
    <div id="error-state" class="hidden text-center max-w-2xl mx-auto mt-10 fade-in">
        <span class="material-symbols-outlined text-red-500 text-[40px] mb-4">error</span>
        <h2 class="text-xl font-bold text-slate-800 mb-2">Booking Not Found</h2>
        <p class="text-sm text-slate-600 mb-6" id="error-message">We couldn't retrieve your appointment data. It may have been canceled or the reference code is invalid.</p>
        <a href="booking.php" class="bg-primary text-white text-sm font-bold px-6 py-3 rounded-xl shadow-sm hover:bg-primary/95 transition-all inline-flex items-center gap-2">
            <span class="material-symbols-outlined text-base">calendar_month</span> Book New Appointment
        </a>
    </div>

    <!-- Success State -->
    <div id="success-state" class="hidden w-full">
        <div class="mb-6 text-center max-w-2xl mx-auto mt-2 sm:mt-4">
            <div id="success-icon-container" class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-[32px]">check_circle</span>
            </div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-slate-900 mb-2">Appointment Confirmed</h1>
            <p class="text-xs sm:text-sm text-slate-600">Your clinical schedule has been securely saved to our database.</p>
        </div>

        <!-- Matching Step 3 Review Architecture -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 sm:p-6 md:p-8 max-w-4xl mx-auto overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 pb-4 border-b border-slate-100 gap-2">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[22px]">verified</span> Confirmed Itinerary
                    </h2>
                </div>
                <span class="text-xs font-bold px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-100 transition-all duration-150 self-start sm:self-auto flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> DB Verified
                </span>
            </div>

            <!-- Comprehensive Information Summaries -->
            <div id="stagger-container" class="space-y-4 sm:space-y-6">

                <div class="bg-slate-50 p-3 sm:p-4 rounded-xl border border-slate-200/60 flex justify-between items-center">
                    <span class="text-[11px] sm:text-xs text-slate-500 font-semibold uppercase tracking-wider">Reference Security Code</span>
                    <span id="display-ref" class="text-xs font-mono font-bold text-primary bg-primary/10 px-2.5 py-1 rounded">--</span>
                </div>

                <!-- Patient Information Display -->
                <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="bg-slate-50/70 px-4 py-3 border-b border-slate-200 flex items-center gap-2 text-slate-800 font-bold text-[11px] sm:text-xs uppercase tracking-wider">
                        <span class="material-symbols-outlined text-sm text-primary">person</span> Patient Information
                    </div>
                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Full Name</span>
                            <p class="font-bold text-slate-800 mt-0.5" id="display-name">--</p>
                        </div>
                        <div>
                            <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Email Address</span>
                            <p class="font-medium text-slate-700 mt-0.5 break-all" id="display-email">--</p>
                        </div>
                        <div>
                            <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Phone Number</span>
                            <p class="font-medium text-slate-700 mt-0.5" id="display-phone">--</p>
                        </div>
                    </div>
                </div>

                <!-- Appointment Details Display -->
                <div class="border border-slate-200 rounded-xl overflow-hidden shadow-sm">
                    <div class="bg-slate-50/70 px-4 py-3 border-b border-slate-200 flex items-center gap-2 text-slate-800 font-bold text-[11px] sm:text-xs uppercase tracking-wider">
                        <span class="material-symbols-outlined text-sm text-primary">medical_services</span> Appointment Details
                    </div>
                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Clinical Service</span>
                            <p class="font-bold text-slate-800 mt-0.5" id="display-service">--</p>
                        </div>
                        <div>
                            <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Assigned Dentist</span>
                            <p class="font-bold text-slate-800 mt-0.5" id="display-dentist">--</p>
                        </div>
                        <div>
                            <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Date</span>
                            <p class="font-bold text-slate-800 mt-0.5" id="display-date">--</p>
                        </div>
                        <div>
                            <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Time</span>
                            <p class="font-bold text-primary mt-0.5" id="display-time">--</p>
                        </div>
                    </div>
                </div>

                <!-- Next steps banner -->
                <div class="flex items-start gap-3 p-4 bg-primary/5 rounded-xl border border-primary/10 text-xs text-slate-600">
                    <span class="material-symbols-outlined text-primary text-[20px] mt-0.5 shrink-0">event_available</span>
                    <div>
                        <p class="font-bold text-slate-900">What's Next?</p>
                        <p class="text-slate-500 mt-0.5 leading-relaxed">We've sent a confirmation email to your address. We'll also remind you 24 hours prior to your scheduled appointment. Please arrive 10 minutes early.</p>
                    </div>
                </div>
            </div>

            <!-- Review Action Interaction Footers -->
            <div class="flex justify-center mt-8 pt-6 border-t border-slate-100">
                <a id="back-to-scheduler" href="booking.php" class="bg-slate-100 text-slate-700 hover:bg-slate-200 text-sm font-bold px-8 py-3 rounded-xl shadow-sm active:scale-[0.99] transition-all flex items-center justify-center gap-2">
                    Back to Scheduler
                </a>
            </div>
        </div>
    </div>
</main>

<footer class="w-full py-10 bg-slate-100 mt-auto px-6 md:px-8 border-t border-slate-200 text-slate-500">
    <div class="max-w-[1440px] mx-auto flex flex-col md:flex-row justify-between items-center gap-6 text-center md:text-left">
        <div>
            <div class="font-bold text-slate-800 text-md tracking-tight flex items-center justify-center md:justify-start gap-2">
                <span class="material-symbols-outlined text-primary text-[20px]">dentistry</span> DentalCare Pro
            </div>
            <p class="text-xs text-slate-500 mt-1">Providing state-of-the-art dental procedures with dynamic scheduling workflows.</p>
        </div>
        <p class="text-xs text-slate-400 font-medium">© 2026 DentalCare Pro Clinic. All rights reserved.</p>
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Trigger page entry animation
        document.body.style.opacity = '';
        document.body.classList.add('page-enter');

        // Purge old localstorage mock data as requested
        localStorage.removeItem('dentalBookingData');

        const urlParams = new URLSearchParams(window.location.search);
        let refCode = urlParams.get('ref');

        // Session fallback if no reference code is found in the URL
        if (!refCode) {
            refCode = sessionStorage.getItem('last_booking_ref');
            if (!refCode) {
                showError("No booking reference code was provided in the URL.");
                return;
            }
        }

        // Fetch Real Booking Data replacing local storage
        fetch(`api/booking-lookup.php?ref=${encodeURIComponent(refCode)}`)
            .then(res => res.json())
            .then(result => {
                if (result.success && result.data && result.data.booking) {
                    populateConfirmedUI(result.data.booking);
                } else {
                    showError(result.message || "Booking not found. It may have been canceled or the reference code is incorrect.");
                }
            })
            .catch(err => {
                console.error("Lookup error:", err);
                showError("Network error while retrieving booking details. Please refresh the page.");
            });

        // Setup page exit animation
        const backBtn = document.getElementById('back-to-scheduler');
        if (backBtn) {
            backBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const href = e.currentTarget.href;
                document.body.classList.add('page-exit');
                setTimeout(() => {
                    window.location.href = href;
                }, 250);
            });
        }
    });

    function populateConfirmedUI(booking) {
        // Initialize stagger classes and icon bounce BEFORE revealing to prevent any styling flash
        const staggerContainer = document.getElementById('stagger-container');
        if (staggerContainer) {
            Array.from(staggerContainer.children).forEach((child, index) => {
                child.style.animationDelay = `${index * 60}ms`;
                child.classList.add('stagger-item');
            });
        }

        const successIcon = document.getElementById('success-icon-container');
        if (successIcon) {
            successIcon.classList.add('icon-bounce');
        }

        // Reveal success container
        document.getElementById('loading-state').classList.add('hidden');
        document.getElementById('success-state').classList.remove('hidden');

        // Populate content text
        document.getElementById('display-ref').textContent    = booking.reference_code;
        document.getElementById('display-name').textContent   = `${booking.first_name} ${booking.last_name}`;
        document.getElementById('display-email').textContent  = booking.email;
        document.getElementById('display-phone').textContent  = booking.phone;

        // service_label is now resolved server-side by booking-lookup.php via services-data.php.
        document.getElementById('display-service').textContent = booking.service_label || booking.service_key;

        document.getElementById('display-dentist').textContent = booking.dentist_name;

        // Ensure cross-browser date parsing alignment
        const dateObj = new Date(booking.appointment_date + "T12:00:00");
        document.getElementById('display-date').textContent = dateObj.toLocaleDateString('en-US', {
            weekday: 'long', month: 'long', day: 'numeric', year: 'numeric'
        });
        document.getElementById('display-time').textContent = booking.appointment_time;
    }

    function showError(msg) {
        document.getElementById('loading-state').classList.add('hidden');
        document.getElementById('error-state').classList.remove('hidden');
        if (msg) {
            document.getElementById('error-message').textContent = msg;
        }
    }
</script>
</body>
</html>