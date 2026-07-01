<?php
/**
 * support.php
 * Patient Support Center for DentalCare Pro.
 * Frontend-only implementation with FAQs, contact info, and ticket submission simulation.
 */

require_once __DIR__ . '/../components/design-config.php';

$activePage = 'support';
$pageTitle  = 'Support Center';

ob_start();
?>

<!-- INTRO HERO BLOCK -->
<div class="fade-in">
    <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg text-primary">Support Center</h2>
    <p class="font-body-md text-on-surface-variant mt-1">Get help with appointments, records, billing, or your account.</p>
</div>

<!-- EMERGENCY BANNER -->
<div class="fade-in mt-6 mb-6">
    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-start gap-3">
            <div class="mt-1 flex-shrink-0 w-8 h-8 rounded-full bg-rose-200 text-rose-700 flex items-center justify-center">
                <span class="material-symbols-outlined text-sm" aria-hidden="true">emergency</span>
            </div>
            <div>
                <h3 class="font-bold text-rose-800 text-lg">Dental Emergency?</h3>
                <p class="text-rose-700 text-sm">If you are experiencing severe pain, bleeding, or dental trauma, skip the standard support ticket.</p>
            </div>
        </div>
        <a href="support/emergency-care.php" class="flex-shrink-0 w-full md:w-auto bg-rose-600 hover:bg-rose-700 text-white font-bold py-2.5 px-5 rounded-xl text-sm transition-colors text-center shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-500 focus-visible:ring-offset-2 border border-transparent">
            Go to Emergency Care
        </a>
    </div>
</div>

<!-- QUICK CONTACT & AI ASSISTANT GRID -->
<div class="fade-in delay-100 grid grid-cols-1 md:grid-cols-2 gap-6">
    
    <!-- Quick Contact Card -->
    <div class="bg-surface-container-lowest border border-slate-100 rounded-2xl p-6 shadow-sm flex flex-col h-full">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#1652a0] flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined" aria-hidden="true">contact_support</span>
            </div>
            <h3 class="font-headline-md text-lg text-slate-800 font-bold">Quick Contact</h3>
        </div>
        <div class="space-y-4 text-sm text-slate-600 flex-1">
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-slate-400 text-[20px] mt-0.5" aria-hidden="true">call</span>
                <div class="leading-tight">
                    <span class="block font-semibold text-slate-800 mb-0.5">Phone</span>
                    <?php echo htmlspecialchars(CLINIC_PHONE); ?>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-slate-400 text-[20px] mt-0.5" aria-hidden="true">mail</span>
                <div class="leading-tight">
                    <span class="block font-semibold text-slate-800 mb-0.5">Email</span>
                    <a href="mailto:<?php echo htmlspecialchars(CLINIC_EMAIL); ?>" class="text-[#1652a0] hover:underline"><?php echo htmlspecialchars(CLINIC_EMAIL); ?></a>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined text-slate-400 text-[20px] mt-0.5" aria-hidden="true">location_on</span>
                <div class="leading-tight">
                    <span class="block font-semibold text-slate-800 mb-0.5">Clinic Address</span>
                    <?php echo htmlspecialchars(CLINIC_ADDRESS); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Assistant Card (Coming Soon) -->
    <div class="bg-surface-container-lowest border border-slate-100 rounded-2xl p-6 shadow-sm relative overflow-hidden group flex flex-col h-full">
        <div class="absolute top-4 right-4">
            <span class="text-[10px] font-bold uppercase tracking-wider bg-indigo-50 text-indigo-600 px-2.5 py-1 rounded border border-indigo-100">Coming Soon</span>
        </div>
        <div class="flex items-center gap-3 mb-4 opacity-60">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined" aria-hidden="true">smart_toy</span>
            </div>
            <h3 class="font-headline-md text-lg text-slate-800 font-bold">AI Support Assistant</h3>
        </div>
        <p class="text-sm text-slate-500 opacity-80 mb-6 flex-1 leading-relaxed">
            Our intelligent virtual assistant will soon be available 24/7 to instantly answer common questions, guide you through your dental records, and assist with scheduling.
        </p>
        <button disabled aria-disabled="true" class="w-full py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-400 text-sm font-bold cursor-not-allowed flex items-center justify-center gap-2 transition-colors">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">chat</span>
            Start Chat (Unavailable)
        </button>
    </div>

</div>

<!-- TWO-COLUMN LAYOUT FOR FAQ & FORM -->
<div class="fade-in delay-200 grid grid-cols-1 lg:grid-cols-5 gap-8 items-start mt-8">
    
    <!-- FAQ Accordion -->
    <div class="lg:col-span-3 space-y-4">
        <h3 class="font-headline-md text-xl text-primary font-bold mb-5">Frequently Asked Questions</h3>
        
        <div class="space-y-3">
            <div class="bg-surface-container-lowest border border-slate-100 rounded-xl overflow-hidden shadow-sm transition-all group">
                <button onclick="toggleFaq(this)" aria-expanded="false" class="w-full text-left px-5 py-4 flex items-center justify-between focus-visible:outline-none focus-visible:bg-slate-50 hover:bg-slate-50 transition-colors">
                    <span class="font-semibold text-sm text-slate-800 pr-4">How do I reschedule an appointment?</span>
                    <span class="material-symbols-outlined text-slate-400 transition-transform duration-200 faq-icon flex-shrink-0" aria-hidden="true">add</span>
                </button>
                <div class="hidden px-5 pb-5 text-sm text-slate-600 border-t border-slate-50 pt-3 leading-relaxed">
                    To reschedule, navigate to the <a href="appointments.php" class="text-[#1652a0] font-semibold hover:underline">Appointments</a> page, locate your upcoming visit in the list, and click "Reschedule." You will be prompted to select a new preferred date. Please note that rescheduling requires at least 24 hours' notice to avoid lab preparation fees.
                </div>
            </div>

            <div class="bg-surface-container-lowest border border-slate-100 rounded-xl overflow-hidden shadow-sm transition-all group">
                <button onclick="toggleFaq(this)" aria-expanded="false" class="w-full text-left px-5 py-4 flex items-center justify-between focus-visible:outline-none focus-visible:bg-slate-50 hover:bg-slate-50 transition-colors">
                    <span class="font-semibold text-sm text-slate-800 pr-4">Where can I find my X-rays?</span>
                    <span class="material-symbols-outlined text-slate-400 transition-transform duration-200 faq-icon flex-shrink-0" aria-hidden="true">add</span>
                </button>
                <div class="hidden px-5 pb-5 text-sm text-slate-600 border-t border-slate-50 pt-3 leading-relaxed">
                    Your radiological imagery, including bitewing X-rays and 3D scans, are securely available under the <a href="dental-records.php" class="text-[#1652a0] font-semibold hover:underline">Dental Records</a> section. Scans are typically uploaded and made visible in your portal 24-48 hours after your clinic visit.
                </div>
            </div>

            <div class="bg-surface-container-lowest border border-slate-100 rounded-xl overflow-hidden shadow-sm transition-all group">
                <button onclick="toggleFaq(this)" aria-expanded="false" class="w-full text-left px-5 py-4 flex items-center justify-between focus-visible:outline-none focus-visible:bg-slate-50 hover:bg-slate-50 transition-colors">
                    <span class="font-semibold text-sm text-slate-800 pr-4">How do I update my insurance info?</span>
                    <span class="material-symbols-outlined text-slate-400 transition-transform duration-200 faq-icon flex-shrink-0" aria-hidden="true">add</span>
                </button>
                <div class="hidden px-5 pb-5 text-sm text-slate-600 border-t border-slate-50 pt-3 leading-relaxed">
                    You can update your active insurance provider, member ID, and upload a photo of your new insurance card in your <a href="profile-settings.php" class="text-[#1652a0] font-semibold hover:underline">Profile Settings</a> under the "Billing & Insurance" tab.
                </div>
            </div>

            <div class="bg-surface-container-lowest border border-slate-100 rounded-xl overflow-hidden shadow-sm transition-all group">
                <button onclick="toggleFaq(this)" aria-expanded="false" class="w-full text-left px-5 py-4 flex items-center justify-between focus-visible:outline-none focus-visible:bg-slate-50 hover:bg-slate-50 transition-colors">
                    <span class="font-semibold text-sm text-slate-800 pr-4">Can I join a waitlist for an earlier date?</span>
                    <span class="material-symbols-outlined text-slate-400 transition-transform duration-200 faq-icon flex-shrink-0" aria-hidden="true">add</span>
                </button>
                <div class="hidden px-5 pb-5 text-sm text-slate-600 border-t border-slate-50 pt-3 leading-relaxed">
                    Yes. If you have an upcoming appointment scheduled but would prefer to come in sooner, go to your <a href="appointments.php" class="text-[#1652a0] font-semibold hover:underline">Appointments</a> and click the "Waitlist" button next to your visit. We will send you an SMS alert if an earlier slot matching your treatment type opens up.
                </div>
            </div>
        </div>
    </div>

    <!-- Support Ticket Form -->
    <div class="lg:col-span-2 bg-surface-container-lowest border border-slate-100 rounded-2xl p-6 shadow-sm">
        <div class="mb-6">
            <h3 class="font-headline-md text-lg text-slate-800 font-bold mb-1">Send a Message</h3>
            <p class="text-xs text-slate-500 font-medium">Our support team responds within 1 business day.</p>
        </div>
        
        <form id="supportForm" onsubmit="handleSupportSubmit(event)" class="space-y-4">
            <div class="space-y-1.5">
                <label for="category" class="block text-xs font-bold text-secondary-text uppercase tracking-wide">Category</label>
                <select id="category" required aria-required="true" class="w-full border-slate-200 rounded-xl text-sm text-slate-700 bg-white focus:border-[#1652a0] focus:ring-[#1652a0] shadow-sm py-2.5">
                    <option value="" disabled selected>Select a topic...</option>
                    <option value="appointments">Appointments & Scheduling</option>
                    <option value="billing">Billing & Insurance</option>
                    <option value="records">Dental Records / X-Rays</option>
                    <option value="account">Account Settings</option>
                    <option value="other">Other Inquiry</option>
                </select>
            </div>

            <div class="space-y-1.5">
                <label for="subject" class="block text-xs font-bold text-secondary-text uppercase tracking-wide">Subject</label>
                <input type="text" id="subject" required aria-required="true" placeholder="Brief description of your issue" class="w-full border-slate-200 rounded-xl text-sm text-slate-700 placeholder-slate-400 focus:border-[#1652a0] focus:ring-[#1652a0] shadow-sm py-2.5"/>
            </div>

            <div class="space-y-1.5">
                <label for="message" class="block text-xs font-bold text-secondary-text uppercase tracking-wide">Message</label>
                <textarea id="message" required aria-required="true" rows="5" placeholder="How can we help you today?" class="w-full border-slate-200 rounded-xl text-sm text-slate-700 placeholder-slate-400 focus:border-[#1652a0] focus:ring-[#1652a0] shadow-sm resize-none py-2.5"></textarea>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-[#1652a0] hover:bg-primary-container text-white py-3 px-6 rounded-xl font-bold text-sm transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#1652a0] focus-visible:ring-offset-2 flex items-center justify-center gap-2 shadow-sm">
                    <span class="material-symbols-outlined text-lg" aria-hidden="true">send</span>
                    Submit Ticket
                </button>
            </div>
        </form>
    </div>

</div>

<script>
function toggleFaq(btn) {
    const content = btn.nextElementSibling;
    const icon = btn.querySelector('.faq-icon');
    const isHidden = content.classList.contains('hidden');

    if (isHidden) {
        content.classList.remove('hidden');
        icon.textContent = 'remove';
        btn.classList.add('bg-slate-50');
        btn.setAttribute('aria-expanded', 'true');
    } else {
        content.classList.add('hidden');
        icon.textContent = 'add';
        btn.classList.remove('bg-slate-50');
        btn.setAttribute('aria-expanded', 'false');
    }
}

function handleSupportSubmit(e) {
    e.preventDefault();
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<span class="material-symbols-outlined animate-spin text-lg">sync</span> Sending...';
    submitBtn.disabled = true;

    setTimeout(() => {
        // Use custom inline message box to completely avoid native alert()
        if (typeof showGlobalToast === 'function') {
            showGlobalToast('success', 'Your support ticket has been submitted. We will contact you shortly.');
        } else {
            const fallbackToast = document.createElement('div');
            fallbackToast.className = 'fixed bottom-6 right-6 bg-slate-800 text-white px-6 py-4 rounded-xl shadow-xl z-50 font-medium text-sm flex items-center gap-3';
            fallbackToast.innerHTML = `<span class="material-symbols-outlined text-emerald-400">check_circle</span> Your support ticket has been submitted. We will contact you shortly.`;
            document.body.appendChild(fallbackToast);
            
            setTimeout(() => {
                fallbackToast.style.transition = 'opacity 0.5s ease';
                fallbackToast.style.opacity = '0';
                setTimeout(() => fallbackToast.remove(), 500);
            }, 5000);
        }
        
        e.target.reset();
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 600);
}
</script>

<?php
$pageContent = ob_get_clean();
require_once __DIR__ . '/../components/layout/main-layout.php';
?>