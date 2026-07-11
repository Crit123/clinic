<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => ($base_url ?? '') . '/index.php'],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Services', 'item' => ($base_url ?? '') . '/index.php#services'],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $serviceLabel],
    ]
], JSON_UNESCAPED_SLASHES) ?>
</script>
<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'serviceType' => $serviceLabel,
    'description' => $heroDesc,
    'provider' => [
        '@type' => 'Dentist',
        'name' => 'DentalCare Pro'
    ]
], JSON_UNESCAPED_SLASHES) ?>
</script>

<section class="pt-32 pb-16 md:pt-40 md:pb-24 bg-surface-container-lowest relative overflow-hidden fade-in-up">
    <!-- Abstract background -->
    <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-primary/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/4 pointer-events-none"></div>
    
    <div class="max-w-[1200px] mx-auto px-6 md:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
            <div>
                <!-- Breadcrumbs -->
                <nav class="flex items-center gap-2 text-[13px] font-medium text-on-surface-variant mb-6" aria-label="Breadcrumb">
                    <a href="<?= htmlspecialchars($base_url ?? '') ?>/index.php" class="hover:text-primary transition-colors">Home</a>
                    <span class="icon-line text-[14px]" aria-hidden="true">chevron_right</span>
                    <a href="<?= htmlspecialchars($base_url ?? '') ?>/index.php#services" class="hover:text-primary transition-colors">Services</a>
                    <span class="icon-line text-[14px]" aria-hidden="true">chevron_right</span>
                    <span class="text-primary font-semibold" aria-current="page"><?= htmlspecialchars($serviceLabel) ?></span>
                </nav>
                
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-surface-container-high text-primary text-xs font-bold uppercase tracking-wider mb-5 border border-primary/10 shadow-sm">
                    <span class="icon-line text-[16px]" aria-hidden="true">schedule</span>
                    <span>Estimated Duration: <span class="font-mono"><?= htmlspecialchars($serviceDuration) ?></span></span>
                </div>
                
                <h1 class="font-display text-4xl md:text-5xl lg:text-6xl font-extrabold text-on-background mb-6 tracking-tight leading-tight">
                    <?= htmlspecialchars($serviceLabel) ?>
                </h1>
                
                <p class="text-lg text-on-surface-variant mb-10 leading-relaxed max-w-xl">
                    <?= htmlspecialchars($heroDesc) ?>
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4">
                    <?php if (!empty($isEmergency)): ?>
                        <a href="tel:+15555550148" class="h-14 px-8 rounded-xl bg-error text-white font-bold text-base hover:bg-error/90 shadow-[0_8px_20px_rgba(186,26,26,0.25)] hover:shadow-[0_12px_25px_rgba(186,26,26,0.35)] hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 flex items-center justify-center gap-2">
                            Call Now <span class="icon-line text-[20px]" aria-hidden="true">call</span>
                        </a>
                        <a href="<?= htmlspecialchars($base_url ?? '') ?>/booking.php?service=<?= urlencode($serviceKey) ?>" class="h-14 px-8 rounded-xl border-2 border-primary/20 text-primary font-bold text-base hover:bg-primary/5 transition-all duration-300 flex items-center justify-center gap-2">
                            Book a Follow-up
                        </a>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($base_url ?? '') ?>/booking.php?service=<?= urlencode($serviceKey) ?>" class="h-14 px-8 rounded-xl bg-primary text-white font-bold text-base hover:bg-primary/95 shadow-[0_8px_20px_rgba(0,71,141,0.25)] hover:shadow-[0_12px_25px_rgba(0,71,141,0.35)] hover:-translate-y-0.5 active:translate-y-0 transition-all duration-300 flex items-center justify-center gap-2">
                            Book Appointment <span class="icon-line text-[20px]" aria-hidden="true">calendar_month</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="relative w-full h-full min-h-[300px] lg:min-h-[450px]">
                <div class="aspect-[4/3] rounded-3xl overflow-hidden shadow-2xl relative group h-full w-full">
                    <div class="absolute inset-0 bg-primary/10 group-hover:bg-transparent transition-colors duration-500 z-10 pointer-events-none"></div>
                    <img src="<?= htmlspecialchars($heroImage) ?>" alt="<?= htmlspecialchars($serviceLabel) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                </div>
            </div>
        </div>
    </div>
</section>  