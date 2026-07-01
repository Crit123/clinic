<?php
$isEmerg = $isEmergency ?? false;
$bookingLink = ($base_url ?? '') . '/booking.php?service=' . urlencode($serviceKey ?? 'general');
$callLink = 'tel:+15555550148';
?>
<section class="py-20 bg-primary relative overflow-hidden fade-in-up mt-10 rounded-t-[3rem]">
    <div class="absolute inset-0 bg-gradient-to-br from-primary via-[#00366b] to-surface-tint z-0"></div>
    <div class="absolute top-0 right-0 w-[400px] h-[400px] bg-white/5 rounded-full blur-3xl pointer-events-none"></div>
    
    <div class="max-w-[800px] mx-auto px-6 md:px-8 relative z-10 text-center text-white">
        
        <?php if ($isEmerg): ?>
            <!-- Emergency Messaging Block -->
            <h2 class="text-3xl md:text-5xl font-bold mb-6 tracking-tight">Need immediate help?</h2>
            <p class="text-white/80 text-lg mb-10 max-w-xl mx-auto">Don't wait in pain. Call us during business hours for priority emergency care, or book your follow-up visit online.</p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="<?= $callLink ?>" class="h-14 px-10 rounded-xl bg-red-600 text-white font-bold text-lg hover:bg-red-700 hover:shadow-[0_0_30px_rgba(220,38,38,0.5)] hover:-translate-y-1 transition-all duration-300 flex items-center justify-center shadow-lg gap-2 w-full sm:w-auto">
                    <span class="material-symbols-outlined text-[24px]" aria-hidden="true">call</span>
                    Call Now
                </a>
                <a href="<?= $bookingLink ?>" class="h-14 px-10 rounded-xl border-2 border-white/30 text-white font-bold text-lg hover:bg-white/10 transition-all duration-300 flex items-center justify-center backdrop-blur-sm w-full sm:w-auto">
                    Book Follow-up
                </a>
            </div>
            
        <?php else: ?>
            <!-- Standard Messaging Block -->
            <h2 class="text-3xl md:text-5xl font-bold mb-6 tracking-tight">Ready to improve your smile?</h2>
            <p class="text-white/80 text-lg mb-10 max-w-xl mx-auto">Take the next step in your oral healthcare journey. Our certified team is ready to provide exceptional, comfortable care.</p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="<?= $bookingLink ?>" class="h-14 px-10 rounded-xl bg-white text-primary font-bold text-lg hover:shadow-[0_0_30px_rgba(255,255,255,0.3)] hover:-translate-y-1 transition-all duration-300 flex items-center justify-center shadow-lg w-full sm:w-auto">
                    Book Appointment
                </a>
                <a href="<?= htmlspecialchars($base_url ?? '') ?>/index.php#faq" class="h-14 px-10 rounded-xl border-2 border-white/30 text-white font-bold text-lg hover:bg-white/10 transition-all duration-300 flex items-center justify-center backdrop-blur-sm w-full sm:w-auto">
                    Contact Clinic
                </a>
            </div>
        <?php endif; ?>
        
    </div>
</section>