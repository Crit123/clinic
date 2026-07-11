<section class="py-16 md:py-24 bg-surface-container-low border-t border-outline-variant/40 fade-in-up">
    <div class="max-w-[1200px] mx-auto px-6 md:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <h2 class="text-3xl md:text-4xl font-extrabold text-on-background mb-4">Treatment Benefits</h2>
            <p class="text-on-surface-variant">Discover how this specialized clinical service can enhance your oral health and overall well-being.</p>
        </div>
        
        <div class="flex flex-col divide-y divide-outline-variant/20 max-w-3xl mx-auto">
            <?php foreach($benefitsList as $index => $benefit): ?>
            <div class="flex items-start gap-6 py-8 group">
                <span class="font-mono text-xs text-primary/50 pt-2 w-8 flex-shrink-0"><?= sprintf('%02d', $index + 1) ?></span>
                <div class="w-14 h-14 rounded-2xl bg-primary/10 text-primary flex items-center justify-center shrink-0 group-hover:bg-primary group-hover:text-white transition-colors duration-300">
                    <span class="icon-line text-[28px]"><?= htmlspecialchars($benefit['icon']) ?></span>
                </div>
                <div>
                    <h4 class="font-bold text-on-background text-lg mb-2"><?= htmlspecialchars($benefit['title']) ?></h4>
                    <p class="text-on-surface-variant text-sm leading-relaxed"><?= htmlspecialchars($benefit['desc']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>  