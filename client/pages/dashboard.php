<?php
/**
 * dashboard.php
 * Patient Dashboard for DentalCare Pro.
 */

// 1. We require design-config manually at the top so $APP_ENV is available for our logic
require_once __DIR__ . '/../components/design-config.php';

// 2. Set the variables the layout shell needs
$activePage = 'dashboard';
$pageTitle  = 'Patient Dashboard';

// 3. Start intercepting standard output (Output Buffering)
ob_start();
?>

<!-- SKELETON SHIMMER LOADER (Shown by default while fetching) -->
<div id="skeletonLoaderArea" class="space-y-8" aria-hidden="true">
    <!-- Hero card skeleton -->
    <div class="rounded-2xl bg-surface-container-lowest border border-outline-variant h-64 animate-shimmer"></div>
    
    <!-- Quick actions skeleton -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant h-16 animate-shimmer"></div>
        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant h-16 animate-shimmer"></div>
        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant h-16 animate-shimmer"></div>
        <div class="bg-surface-container-lowest rounded-xl border border-outline-variant h-16 animate-shimmer"></div>
    </div>

    <!-- Stats skeleton -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant h-32 animate-shimmer"></div>
        <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant h-32 animate-shimmer"></div>
        <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant h-32 animate-shimmer"></div>
        <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant h-32 animate-shimmer"></div>
    </div>
    
    <!-- Row grids skeleton -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant h-64 animate-shimmer"></div>
        <div class="bg-surface-container-lowest rounded-2xl border border-outline-variant h-64 animate-shimmer"></div>
    </div>
</div>

<!-- POPULATED DASHBOARD -->
<div id="populatedDashboardArea" class="hidden opacity-0 space-y-8 transition-opacity duration-300">

    <!-- Hero Welcome Card (T = 0ms) -->
    <section class="fade-in relative overflow-hidden rounded-2xl bg-surface-container-lowest shadow-[0_4px_16px_rgba(0,71,141,0.08)] hover:shadow-[0_8px_24px_rgba(0,71,141,0.12)] transition-shadow duration-300"
             aria-label="Welcome and next appointment">
        <div class="absolute inset-0 bg-gradient-to-r from-surface-container-low to-surface-container-lowest opacity-50 z-0" aria-hidden="true"></div>
        <div class="relative z-10 p-8 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            <div>
                <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-primary mb-2">Welcome back, <span id="patientNameEl">...</span></h2>
                <p id="appointmentDetailsEl" class="font-body-lg text-body-lg text-on-surface-variant max-w-xl">
                    Loading your appointment details...
                </p>
            </div>
            <div class="bg-surface-container-low p-6 rounded-xl border border-primary-fixed w-full lg:w-auto flex flex-col items-start min-w-[320px]">
                <div class="flex items-center justify-between w-full mb-4">
                    <span id="appointmentStatusEl" class="inline-flex items-center px-3 py-1 rounded-full bg-secondary-fixed text-on-secondary-fixed font-label-md text-label-md capitalize">Status</span>
                    <span class="material-symbols-outlined text-secondary" aria-hidden="true">calendar_today</span>
                </div>
                <h3 id="doctorNameEl" class="font-headline-md text-headline-md text-on-surface mb-1">Loading...</h3>
                <p id="appointmentDateEl" class="font-body-md text-body-md text-on-surface-variant flex items-center mb-6">
                    <span class="material-symbols-outlined text-sm mr-2" aria-hidden="true">schedule</span>
                    --
                </p>
                <!-- Split action buttons for details & prep checklist -->
                <div class="w-full flex flex-col sm:flex-row gap-3">
                    <a href="appointment-prep.php"
                       class="flex-1 bg-surface-container-highest hover:bg-surface-variant text-primary font-label-md py-3 px-4 rounded-lg transition-all border border-outline-variant flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 hover:-translate-y-0.5 shadow-sm">
                        <span class="material-symbols-outlined text-sm mr-2" aria-hidden="true">checklist</span>
                        Prep List
                    </a>
                    <a href="appointments.php"
                       onclick="showGlobalToast('info', 'Opening appointment details…')"
                       class="flex-1 bg-primary hover:bg-primary-container text-on-primary font-label-md py-3 px-4 rounded-lg transition-all shadow-sm flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 hover:-translate-y-0.5">
                        Details
                        <span class="material-symbols-outlined ml-2 text-sm" aria-hidden="true">arrow_forward</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Actions Panel (T = 100ms) -->
    <section class="fade-in delay-100" aria-label="Quick Actions">
        <div id="quickActionsContainerEl" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="emergency-care.php" 
               class="flex items-center gap-3 p-4 bg-rose-50/50 hover:bg-rose-50 border border-rose-100 hover:border-rose-200 rounded-xl transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                <div class="w-10 h-10 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-xl">emergency</span>
                </div>
                <div class="truncate">
                    <p class="text-xs font-semibold text-rose-900 uppercase tracking-wider">Emergency</p>
                    <p class="text-[11px] text-rose-700/80 truncate">Urgent Care Center</p>
                </div>
            </a>

            <a href="appointment-prep.php" 
               class="flex items-center gap-3 p-4 bg-surface-container-lowest hover:bg-surface-container-low border border-outline-variant rounded-xl transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-primary">
                <div class="w-10 h-10 rounded-lg bg-primary-container/20 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-xl">checklist</span>
                </div>
                <div class="truncate">
                    <p class="text-xs font-bold text-on-surface uppercase tracking-wider">Prep Center</p>
                    <p class="text-[11px] text-on-surface-variant truncate">Visit Readiness List</p>
                </div>
            </a>

            <a href="profile-settings.php" 
               class="flex items-center gap-3 p-4 bg-surface-container-lowest hover:bg-surface-container-low border border-outline-variant rounded-xl transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-primary">
                <div class="w-10 h-10 rounded-lg bg-secondary-fixed-dim/20 text-on-secondary-fixed flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-xl">settings_accessibility</span>
                </div>
                <div class="truncate">
                    <p class="text-xs font-bold text-on-surface uppercase tracking-wider">My Profile</p>
                    <p class="text-[11px] text-on-surface-variant truncate">Medical & Contacts</p>
                </div>
            </a>

            <a href="support-center.php" 
               class="flex items-center gap-3 p-4 bg-surface-container-lowest hover:bg-surface-container-low border border-outline-variant rounded-xl transition-all duration-200 hover:-translate-y-0.5 hover:shadow-sm focus:outline-none focus:ring-2 focus:ring-primary">
                <div class="w-10 h-10 rounded-lg bg-surface-variant text-surface-tint flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-xl">help_center</span>
                </div>
                <div class="truncate">
                    <p class="text-xs font-bold text-on-surface uppercase tracking-wider">Support</p>
                    <p class="text-[11px] text-on-surface-variant truncate">FAQs & Clinic Help</p>
                </div>
            </a>
        </div>
    </section>

    <!-- Statistics Cards Row (T = 200ms) -->
    <section class="fade-in delay-200 grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6"
             aria-label="Appointment and record statistics">

        <a href="appointments.php?filter=upcoming"
           class="block bg-surface-container-lowest p-4 sm:p-6 rounded-2xl shadow-[0_4px_16px_rgba(0,71,141,0.08)] hover:-translate-y-1 hover:shadow-[0_8px_24px_rgba(0,71,141,0.12)] transition-all duration-300 border border-surface-container focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-primary-fixed flex items-center justify-center text-primary" aria-hidden="true">
                    <span class="material-symbols-outlined text-lg sm:text-xl">event_upcoming</span>
                </div>
                <span class="text-[10px] sm:text-xs text-outline font-semibold hidden sm:block">View List</span>
            </div>
            <p class="font-label-md text-[10px] sm:text-label-md text-on-surface-variant uppercase tracking-wider mb-1 truncate">Upcoming</p>
            <p id="upcomingCountEl" class="font-headline-lg text-2xl sm:text-headline-lg text-primary" aria-hidden="true">0</p>
        </a>

        <a href="appointments.php?filter=completed"
           class="block bg-surface-container-lowest p-4 sm:p-6 rounded-2xl shadow-[0_4px_16px_rgba(0,71,141,0.08)] hover:-translate-y-1 hover:shadow-[0_8px_24px_rgba(0,71,141,0.12)] transition-all duration-300 border border-surface-container focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-surface-variant flex items-center justify-center text-surface-tint" aria-hidden="true">
                    <span class="material-symbols-outlined text-lg sm:text-xl">check_circle</span>
                </div>
                <span class="text-[10px] sm:text-xs text-outline font-semibold hidden sm:block">Review Hist.</span>
            </div>
            <p class="font-label-md text-[10px] sm:text-label-md text-on-surface-variant uppercase tracking-wider mb-1 truncate">Completed</p>
            <p id="completedCountEl" class="font-headline-lg text-2xl sm:text-headline-lg text-primary" aria-hidden="true">0</p>
        </a>

        <a href="appointments.php?filter=all"
           class="block bg-surface-container-lowest p-4 sm:p-6 rounded-2xl shadow-[0_4px_16px_rgba(0,71,141,0.08)] hover:-translate-y-1 hover:shadow-[0_8px_24px_rgba(0,71,141,0.12)] transition-all duration-300 border border-surface-container focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-secondary-fixed-dim flex items-center justify-center text-on-secondary-fixed" aria-hidden="true">
                    <span class="material-symbols-outlined text-lg sm:text-xl">history</span>
                </div>
                <span class="text-[10px] sm:text-xs text-outline font-semibold hidden sm:block">View Log</span>
            </div>
            <p class="font-label-md text-[10px] sm:text-label-md text-on-surface-variant uppercase tracking-wider mb-1 truncate">Total Visits</p>
            <p id="totalVisitsCountEl" class="font-headline-lg text-2xl sm:text-headline-lg text-primary" aria-hidden="true">0</p>
        </a>

        <a href="dental-records.php"
           class="block bg-surface-container-lowest p-4 sm:p-6 rounded-2xl shadow-[0_4px_16px_rgba(0,71,141,0.08)] hover:-translate-y-1 hover:shadow-[0_8px_24px_rgba(0,71,141,0.12)] transition-all duration-300 border border-surface-container focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-surface-container-highest flex items-center justify-center text-primary-container" aria-hidden="true">
                    <span class="material-symbols-outlined text-lg sm:text-xl">folder_shared</span>
                </div>
                <span class="text-[10px] sm:text-xs text-outline font-semibold hidden sm:block">Browse</span>
            </div>
            <p class="font-label-md text-[10px] sm:text-label-md text-on-surface-variant uppercase tracking-wider mb-1 truncate">Records</p>
            <p id="recordsCountEl" class="font-headline-lg text-2xl sm:text-headline-lg text-primary" aria-hidden="true">0</p>
        </a>
    </section>

    <!-- Row 1 Grid: Personal Health Notes & Visit Frequency (T = 300ms) -->
    <section class="fade-in delay-300 grid grid-cols-1 lg:grid-cols-2 gap-6"
             aria-label="Care plan and frequency tracking">

        <!-- Personal Health Notes Card -->
        <div class="bg-surface-container-lowest p-6 rounded-2xl shadow-[0_4px_16px_rgba(0,71,141,0.08)] border border-surface-container flex flex-col"
             aria-label="Personal Health Notes">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-headline-md text-lg text-primary flex items-center">
                    <span class="material-symbols-outlined mr-2 text-secondary" aria-hidden="true">clinical_notes</span>
                    Personal Health Notes
                </h3>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-md bg-primary-container/30 text-primary">Directives</span>
            </div>

            <div class="flex-1">
                <ul id="healthNotesListEl" class="space-y-4" aria-live="polite">
                    <!-- Dynamic Health Notes injected by JS -->
                    <li class="p-4 rounded-xl bg-surface-container-low border-l-4 border-primary transition-all duration-200 hover:bg-surface-container-high group">
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-primary text-xl mt-0.5">dentistry</span>
                            <div>
                                <p class="text-sm text-on-surface font-semibold leading-snug">Switch to a soft-bristle brush</p>
                                <p class="text-xs text-on-surface-variant mt-1 leading-relaxed">Dr. Jenkins recommends soft bristles to prevent receding gums around lower incisors.</p>
                                <span class="block text-[10px] text-outline font-medium uppercase mt-2">Dr. Sarah Jenkins · 1 month ago</span>
                            </div>
                        </div>
                    </li>
                    <li class="p-4 rounded-xl bg-surface-container-low border-l-4 border-secondary transition-all duration-200 hover:bg-surface-container-high group">
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-secondary text-xl mt-0.5">water_drop</span>
                            <div>
                                <p class="text-sm text-on-surface font-semibold leading-snug">Enamel Care routine</p>
                                <p class="text-xs text-on-surface-variant mt-1 leading-relaxed">Consider a gentle fluoride rinse before bed to help strengthen enamel in wear-prone areas.</p>
                                <span class="block text-[10px] text-outline font-medium uppercase mt-2">Dr. Sarah Jenkins · 1 month ago</span>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Visit Frequency Card -->
        <div class="bg-surface-container-lowest p-6 rounded-2xl shadow-[0_4px_16px_rgba(0,71,141,0.08)] border border-surface-container flex flex-col">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-headline-md text-lg text-primary flex items-center">
                    <span class="material-symbols-outlined mr-2 text-secondary" aria-hidden="true">calendar_clock</span>
                    Visit Frequency
                </h3>
                <span id="visitNudgeBadgeEl"
                      class="text-xs font-bold px-2.5 py-1 rounded-md bg-surface-container-high text-on-surface-variant">
                    Loading...
                </span>
            </div>

            <div class="flex-1 space-y-5">

                <!-- Visit History & Next Recommendation Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Last Visit -->
                    <div class="bg-surface-container-low rounded-xl p-4 border border-outline-variant">
                        <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1">Last Visit</p>
                        <p id="lastVisitServiceEl" class="font-body-lg text-on-surface font-semibold text-sm">--</p>
                        <p id="lastVisitDetailsEl" class="font-body-sm text-xs text-on-surface-variant mt-0.5">--</p>
                    </div>
                    
                    <!-- Recommended Next Visit -->
                    <div class="bg-surface-container-low rounded-xl p-4 border border-primary-fixed border-dashed relative overflow-hidden">
                        <div class="absolute inset-0 bg-primary-container opacity-10 pointer-events-none"></div>
                        <p class="text-xs font-semibold text-primary uppercase tracking-wider mb-1 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">event_upcoming</span> Next Rec.
                        </p>
                        <p id="nextRecommendedServiceEl" class="font-body-lg text-on-surface font-semibold text-sm relative z-10">--</p>
                        <p id="nextRecommendedDueEl" class="font-body-sm text-xs text-primary font-medium mt-0.5 relative z-10">--</p>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-surface-container-low rounded-xl p-4 border border-outline-variant text-center">
                        <p id="daysSinceLastEl" class="font-headline-lg text-2xl font-bold text-primary">--</p>
                        <p class="text-xs text-on-surface-variant mt-1">Days Since Last Visit</p>
                    </div>
                    <div class="bg-surface-container-low rounded-xl p-4 border border-outline-variant text-center">
                        <p id="avgGapEl" class="font-headline-lg text-2xl font-bold text-primary">--</p>
                        <p class="text-xs text-on-surface-variant mt-1">Avg. Days Between Visits</p>
                    </div>
                </div>

                <!-- Nudge Message -->
                <div id="visitNudgeMessageEl"
                     class="rounded-xl p-4 border text-sm font-body-md flex items-start gap-3"
                     aria-live="polite">
                    <span class="material-symbols-outlined text-base mt-0.5" id="visitNudgeIconEl" aria-hidden="true">info</span>
                    <span id="visitNudgeTextEl">Checking your visit history...</span>
                </div>

            </div>

            <a href="appointments.php"
               class="mt-6 w-full py-2.5 border border-outline-variant hover:bg-surface-container-low text-primary font-label-md rounded-lg transition-colors flex items-center justify-center focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                View All Appointments
                <span class="material-symbols-outlined ml-2 text-sm" aria-hidden="true">arrow_forward</span>
            </a>
        </div>
    </section>

    <!-- Row 2 Grid: Clinic Announcements & Recent Activity (T = 400ms) -->
    <section class="fade-in delay-400 grid grid-cols-1 lg:grid-cols-2 gap-6"
             aria-label="Clinic announcements and log logs">

        <!-- Clinic Announcements Card -->
        <div class="bg-surface-container-lowest p-6 rounded-2xl shadow-[0_4px_16px_rgba(0,71,141,0.08)] border border-surface-container flex flex-col"
             aria-label="Clinic Announcements">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-headline-md text-lg text-primary flex items-center">
                    <span class="material-symbols-outlined mr-2 text-secondary" aria-hidden="true">campaign</span>
                    Clinic Announcements
                </h3>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-md bg-secondary-fixed text-on-secondary-fixed">Global Notice</span>
            </div>

            <div class="flex-1">
                <ul id="clinicAnnouncementsListEl" class="space-y-4" aria-live="polite">
                    <!-- Dynamic Announcements injected by JS -->
                    <li class="p-4 rounded-xl bg-rose-50/30 border border-rose-100 transition-all duration-200 hover:bg-rose-50/50">
                        <div class="flex items-start gap-3">
                            <span class="inline-block px-2 py-0.5 text-[10px] font-bold bg-rose-100 text-rose-800 rounded uppercase tracking-wider mt-0.5 shrink-0">Alert</span>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-rose-900">Holiday Closure Schedule</p>
                                <p class="text-xs text-rose-700/90 mt-1 leading-relaxed">The clinic will be closed from December 24th to December 26th. For urgent cases, please utilize the Emergency Care line.</p>
                            </div>
                        </div>
                    </li>
                    <li class="p-4 rounded-xl bg-surface-container-low border border-outline-variant transition-all duration-200 hover:bg-surface-container-high">
                        <div class="flex items-start gap-3">
                            <span class="relative flex h-2 w-2 mt-2 shrink-0">
                                <span class="animate-pulse absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span class="inline-block px-2 py-0.5 text-[10px] font-bold bg-emerald-100 text-emerald-800 rounded uppercase tracking-wider shrink-0">New</span>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-on-surface">Now Offering Invisalign Aligners</p>
                                <p class="text-xs text-on-surface-variant mt-1 leading-relaxed">Achieve your perfect smile discreetly. Ask Dr. Jenkins during your next visit if orthodontic aligners are right for you!</p>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Recent Activity Timeline -->
        <div class="bg-surface-container-lowest p-6 rounded-2xl shadow-[0_4px_16px_rgba(0,71,141,0.08)] border border-surface-container">
            <h3 class="font-headline-md text-lg text-primary mb-6 flex items-center">
                <span class="material-symbols-outlined mr-2 text-secondary" aria-hidden="true">history_edu</span>
                Recent Activity
            </h3>

            <ol id="activityTimelineEl" class="relative border-l-2 border-surface-variant ml-3 space-y-6" aria-label="Recent activity timeline">
                <!-- Javascript will populate timeline here -->
            </ol>
        </div>
    </section>
</div>

<!-- EMPTY STATE (new patient, hidden by default) -->
<div id="emptyDashboardArea" class="hidden transition-opacity duration-300">
    <section class="bg-surface-container-lowest rounded-2xl p-8 text-center border border-surface-container shadow-[0_4px_16px_rgba(0,0,0,0.04)] max-w-2xl mx-auto py-16">
        <div class="mx-auto flex justify-center mb-6" aria-hidden="true">
            <svg class="w-48 h-48 text-primary/10" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-hidden="true">
                <circle cx="100" cy="100" r="80" fill="currentColor"/>
                <path d="M100 65C85 65 75 75 75 90C75 115 90 135 100 155C110 135 125 115 125 90C125 75 115 65 100 65Z"
                      stroke="#003164" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <h3 class="font-headline-lg text-primary mb-3">Begin Your Smile Journey</h3>
        <p class="font-body-md text-on-surface-variant max-w-md mx-auto mb-8">
            You don't have any appointments yet. Let's start with a comprehensive routine checkup.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="book-appointment.php"
               class="bg-primary hover:bg-primary-container text-on-primary py-3 px-6 rounded-lg font-bold transition-all focus:outline-none focus:ring-2 focus:ring-primary">
                Schedule Initial Visit
            </a>
            <button id="tourStartBtn"
                    onclick="startPortalTour(this)"
                    class="tour-portal-btn border border-outline hover:bg-surface-container-low text-primary py-3 px-6 rounded-lg font-bold transition-all focus:outline-none focus:ring-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed">
                Tour the Portal
            </button>
        </div>
    </section>
</div>

<!-- PAGE SPECIFIC SCRIPTS -->
<script>
// Expose fetch/render to the window to allow Replay from PortalTour
window.isDashboardLoading = false;
window.isNewPatient = false;

window.loadDashboardData = async () => {
    window.isDashboardLoading = true;
    
    // Disable any Tour buttons to prevent race conditions during fetch
    const tourBtns = document.querySelectorAll('.tour-portal-btn');
    tourBtns.forEach(btn => btn.disabled = true);

    const skeleton = document.getElementById('skeletonLoaderArea');
    const content  = document.getElementById('populatedDashboardArea');
    const emptyState = document.getElementById('emptyDashboardArea');

    // Ensure we are showing the loader state at the beginning of the fetch
    content.classList.add('opacity-0', 'hidden');
    emptyState.classList.add('hidden');
    skeleton.classList.remove('hidden');
    skeleton.removeAttribute('aria-hidden');

    try {
        // 1. Fetch Profile Info
        const profileRes = await fetch('../backend/dashboard-backend.php?action=get_profile');
        const profileData = await profileRes.json();
        if (profileData.success) {
            document.getElementById('patientNameEl').textContent = profileData.profile.first_name;
        }

        // 2. Fetch Stats
        const statsRes = await fetch('../backend/dashboard-backend.php?action=get_booking_stats');
        const statsData = await statsRes.json();
        let isNewPatient = false;
        
        if (statsData.success) {
            const stats = statsData.stats;
            document.getElementById('upcomingCountEl').textContent = stats.upcoming;
            document.getElementById('completedCountEl').textContent = stats.completed;
            document.getElementById('totalVisitsCountEl').textContent = stats.total;
            
            // Assume Records == Total for simplicity here
            document.getElementById('recordsCountEl').textContent = stats.total; 
            
            if (stats.total === 0) {
                isNewPatient = true;
            }
        }
        
        // Expose globally for Tour configuration
        window.isNewPatient = isNewPatient;

        // 3. Fetch Upcoming Appointments
        const upcomingRes = await fetch('../backend/dashboard-backend.php?action=get_bookings&filter=upcoming');
        const upcomingData = await upcomingRes.json();
        
        if (upcomingData.success && upcomingData.bookings.length > 0) {
            const nextAppt = upcomingData.bookings[0];
            
            document.getElementById('appointmentStatusEl').textContent = nextAppt.status;
            document.getElementById('doctorNameEl').textContent = nextAppt.dentist_name;
            
            // Format Date and Time
            const dateObj = new Date(nextAppt.appointment_date);
            const dateStr = dateObj.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
            
            // Convert '14:00' to '2:00 PM'
            const [hours, minutes] = nextAppt.appointment_time.split(':');
            const timeObj = new Date();
            timeObj.setHours(hours, minutes);
            const timeStr = timeObj.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });

            document.getElementById('appointmentDateEl').innerHTML = `<span class="material-symbols-outlined text-sm mr-2" aria-hidden="true">schedule</span> ${dateStr} at ${timeStr}`;
            document.getElementById('appointmentDetailsEl').textContent = `Your next appointment for ${nextAppt.service_key} is scheduled.`;
        } else {
            document.getElementById('appointmentStatusEl').textContent = 'None';
            document.getElementById('doctorNameEl').textContent = 'No Upcoming Appointment';
            document.getElementById('appointmentDateEl').innerHTML = `<span class="material-symbols-outlined text-sm mr-2" aria-hidden="true">schedule</span> --`;
            document.getElementById('appointmentDetailsEl').textContent = 'You have no upcoming appointments scheduled at this time.';
        }

        // 4. Fetch Timeline
        const timelineRes = await fetch('../backend/dashboard-backend.php?action=get_activity_timeline');
        const timelineData = await timelineRes.json();
        
        if (timelineData.success) {
            const timelineEl = document.getElementById('activityTimelineEl');
            timelineEl.innerHTML = ''; 
            
            if (timelineData.timeline.length === 0) {
                 timelineEl.innerHTML = '<p class="text-sm text-on-surface-variant italic">No recent activity.</p>';
            } else {
                // Slice top 5 activities
                timelineData.timeline.slice(0, 5).forEach(item => {
                    let label = "Appointment Activity";
                    let icon = "event";
                    let color = "text-primary";
                    let isFuture = new Date(item.appointment_date) >= new Date();

                    if (item.status === 'confirmed') {
                        label = isFuture ? "Appointment Confirmed" : "Appointment Completed";
                        icon = isFuture ? "event_available" : "check_circle";
                        color = isFuture ? "text-emerald-600" : "text-blue-600";
                    } else if (item.status === 'cancelled') {
                        label = "Appointment Cancelled";
                        icon = "cancel";
                        color = "text-red-600";
                    } else if (item.status === 'pending') {
                        label = "Booking Pending Confirmation";
                        icon = "pending_actions";
                        color = "text-amber-600";
                    }

                    const dateStr = new Date(item.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

                    timelineEl.innerHTML += `
                        <li class="mb-6 ml-6 relative">
                            <span class="absolute flex items-center justify-center w-8 h-8 bg-surface-container-lowest rounded-full -left-10 ring-4 ring-surface-container-lowest text-surface-variant ${color}">
                                <span class="material-symbols-outlined text-sm" aria-hidden="true">${icon}</span>
                            </span>
                            <div class="bg-surface-container-low p-4 rounded-xl border border-outline-variant">
                                <h4 class="font-headline-sm text-sm font-bold text-on-surface mb-1">${label}</h4>
                                <p class="font-body-sm text-xs text-on-surface-variant mb-2">
                                    ${item.service_key} with ${item.dentist_name}
                                </p>
                                <time class="block mb-2 text-[10px] font-medium leading-none text-outline uppercase tracking-wider">${dateStr}</time>
                            </div>
                        </li>
                    `;
                });
            }
        }

        // 5. Fetch Visit Frequency
        const freqRes = await fetch('../backend/dashboard-backend.php?action=get_visit_frequency');
        const freqData = await freqRes.json();

        if (freqData.success) {
            const vf = freqData.visit_frequency;

            // --- Last Visit ---
            if (vf.last_visit) {
                const dateStr = new Date(vf.last_visit.appointment_date).toLocaleDateString('en-US', {
                    weekday: 'short', month: 'short', day: 'numeric', year: 'numeric'
                });
                document.getElementById('lastVisitServiceEl').textContent = vf.last_visit.service_key;
                document.getElementById('lastVisitDetailsEl').textContent =
                    `${vf.last_visit.dentist_name} · ${dateStr}`;
            } else {
                document.getElementById('lastVisitServiceEl').textContent = 'No visits yet';
                document.getElementById('lastVisitDetailsEl').textContent = 'Your completed appointments will appear here.';
            }

            // --- Stats ---
            document.getElementById('daysSinceLastEl').textContent =
                vf.days_since_last_visit !== null ? vf.days_since_last_visit : '--';

            document.getElementById('avgGapEl').textContent =
                vf.average_gap_days !== null ? vf.average_gap_days : '--';
                
            // --- Next Recommended Visit ---
            if (vf.days_since_last_visit !== null && vf.average_gap_days !== null) {
                const daysRemaining = vf.average_gap_days - vf.days_since_last_visit;
                document.getElementById('nextRecommendedServiceEl').textContent = 'Routine Cleaning'; 
                if (daysRemaining > 0) {
                    document.getElementById('nextRecommendedDueEl').textContent = `Due in ${daysRemaining} Days`;
                    document.getElementById('nextRecommendedDueEl').className = 'font-body-sm text-xs text-primary font-medium mt-0.5 relative z-10';
                } else if (daysRemaining === 0) {
                    document.getElementById('nextRecommendedDueEl').textContent = 'Due Today';
                    document.getElementById('nextRecommendedDueEl').className = 'font-body-sm text-xs text-primary font-medium mt-0.5 relative z-10';
                } else {
                    document.getElementById('nextRecommendedDueEl').textContent = `Overdue by ${Math.abs(daysRemaining)} Days`;
                    document.getElementById('nextRecommendedDueEl').className = 'font-body-sm text-xs text-red-600 font-medium mt-0.5 relative z-10';
                }
            } else {
                document.getElementById('nextRecommendedServiceEl').textContent = 'Routine Cleaning';
                document.getElementById('nextRecommendedDueEl').textContent = 'Schedule anytime';
                document.getElementById('nextRecommendedDueEl').className = 'font-body-sm text-xs text-on-surface-variant font-medium mt-0.5 relative z-10';
            }

            // --- Nudge Badge + Message ---
            const nudgeBadge   = document.getElementById('visitNudgeBadgeEl');
            const nudgeBox     = document.getElementById('visitNudgeMessageEl');
            const nudgeIcon    = document.getElementById('visitNudgeIconEl');
            const nudgeText    = document.getElementById('visitNudgeTextEl');

            const nudgeConfig = {
                no_visits: {
                    badge:      'New Patient',
                    badgeCls:   'bg-surface-container-high text-on-surface-variant',
                    boxCls:     'bg-blue-50 border-blue-200 text-blue-800',
                    icon:       'waving_hand',
                    iconCls:    'text-blue-500',
                    message:    'Welcome! Book your first appointment to start tracking your dental health.'
                },
                on_track: {
                    badge:      'On Track',
                    badgeCls:   'bg-emerald-100 text-emerald-800',
                    boxCls:     'bg-emerald-50 border-emerald-200 text-emerald-800',
                    icon:       'check_circle',
                    iconCls:    'text-emerald-500',
                    message:    'Great job! You\'re visiting regularly. Keep up the routine for optimal dental health.'
                },
                due_soon: {
                    badge:      'Due Soon',
                    badgeCls:   'bg-amber-100 text-amber-800',
                    boxCls:     'bg-amber-50 border-amber-200 text-amber-800',
                    icon:       'schedule',
                    iconCls:    'text-amber-500',
                    message:    'It\'s been a while. Dentists recommend a visit every 6 months — consider booking soon.'
                },
                overdue: {
                    badge:      'Overdue',
                    badgeCls:   'bg-red-100 text-red-800',
                    boxCls:     'bg-red-50 border-red-200 text-red-800',
                    icon:       'warning',
                    iconCls:    'text-red-500',
                    message:    'It\'s been over a year since your last visit. We recommend scheduling a checkup as soon as possible.'
                }
            };

            const cfg = nudgeConfig[vf.nudge_status] ?? nudgeConfig['no_visits'];

            // Apply badge
            nudgeBadge.textContent = cfg.badge;
            nudgeBadge.className = `text-xs font-bold px-2.5 py-1 rounded-md ${cfg.badgeCls}`;

            // Apply nudge box
            nudgeBox.className = `rounded-xl p-4 border text-sm font-body-md flex items-start gap-3 ${cfg.boxCls}`;
            nudgeIcon.className = `material-symbols-outlined text-base mt-0.5 ${cfg.iconCls}`;
            nudgeIcon.textContent = cfg.icon;
            nudgeText.textContent = cfg.message;
        }

        // Telemetry state retrieval
        const hasSeenTour = localStorage.getItem('dcpro_tour_completed') === 'true';

        // Update Replay text 
        const tourStartBtn = document.getElementById('tourStartBtn');
        if (tourStartBtn) {
            tourStartBtn.textContent = hasSeenTour ? 'Replay Portal Tour' : 'Tour the Portal';
        }

        // Hide Skeleton and Reveal App
        skeleton.classList.add('hidden');
        skeleton.setAttribute('aria-hidden', 'true');
        
        // Auto-start logic vs. standard reveal
        if (isNewPatient) {
            if (!hasSeenTour) {
                // Instantly inject the mock data so the badge + populated state renders
                // BEFORE the user is shown the empty state flash
                if (typeof window.injectDashboardMockData === 'function') {
                    window.injectDashboardMockData();
                    requestAnimationFrame(() => {
                        startPortalTour(tourStartBtn);
                    });
                }
            } else {
                emptyState.classList.remove('hidden');
            }
        } else {
            content.classList.remove('hidden', 'opacity-0');
        }

    } catch (error) {
        console.error("Error fetching dashboard data:", error);
        skeleton.classList.add('hidden');
        content.classList.remove('hidden', 'opacity-0');
        if (typeof showGlobalToast === 'function') {
            showGlobalToast('error', 'Unable to load all dashboard records.');
        }
    } finally {
        // Safe lock release
        window.isDashboardLoading = false;
        tourBtns.forEach(btn => btn.disabled = false);
    }
};

document.addEventListener('DOMContentLoaded', () => {
    // Initial boot
    window.loadDashboardData();
});

// Utility toggle functions for Dev Tools
function toggleLoaderState() {
    const skeleton = document.getElementById('skeletonLoaderArea');
    const content  = document.getElementById('populatedDashboardArea');
    const emptyState = document.getElementById('emptyDashboardArea');

    content.classList.add('opacity-0', 'hidden');
    emptyState.classList.add('hidden');
    skeleton.classList.remove('hidden');
    skeleton.removeAttribute('aria-hidden');
    
    if (typeof showGlobalToast === 'function') showGlobalToast('info', 'Simulating portal loading state…');

    setTimeout(() => {
        skeleton.classList.add('hidden');
        skeleton.setAttribute('aria-hidden', 'true');
        content.classList.remove('hidden', 'opacity-0');
        if (typeof showGlobalToast === 'function') showGlobalToast('success', 'Portal contents loaded.');
    }, 2000);
}

function toggleEmptyState() {
    const populated = document.getElementById('populatedDashboardArea');
    const empty     = document.getElementById('emptyDashboardArea');
    const showing   = !populated.classList.contains('hidden');

    populated.classList.toggle('hidden',  showing);
    empty.classList.toggle('hidden',     !showing);

    if (typeof showGlobalToast === 'function') {
        showGlobalToast(
            showing ? 'warning' : 'success',
            showing ? 'Switched to new-patient empty state.' : 'Back to populated dashboard view.'
        );
    }
}

// Global Tour Launcher
function startPortalTour(triggerElement) {
    if (window.innerWidth < 1024) {
        window.PortalTourInstance = new PortalTourCompact(window.dashboardTourConfigCompact);
    } else {
        window.PortalTourInstance = new PortalTourDesktop(window.dashboardTourConfig);
    }
    window.PortalTourInstance.start(triggerElement);
}
</script>

<!-- Initialize Tour Scripts Sequentially -->
<script src="../components/guided-tour/shared/portal-tour-core.js"></script>
<script src="../components/guided-tour/shared/dashboard-tour-shared.js"></script>

<!-- Mobile & Tablet Concept (<1024px) -->
<script src="../components/guided-tour/mobile-tablet/portal-tour-compact.js"></script>
<script src="../components/guided-tour/mobile-tablet/dashboard-tour-compact.js"></script>

<!-- Desktop Concept (>=1024px) -->
<script src="../components/guided-tour/laptop-desktop/portal-tour-desktop.js"></script>
<script src="../components/guided-tour/laptop-desktop/dashboard-tour-desktop.js"></script>

<?php
// 4. Close the buffer and save everything captured so far into $pageContent
$pageContent = ob_get_clean();

// 5. Require the layout shell, which will handle wrapping $pageContent
require_once __DIR__ . '/../components/layout/main-layout.php';
?>