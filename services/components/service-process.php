<section class="py-16 md:py-24 bg-surface-container-lowest border-t border-slate-100 fade-in-up">
    <div class="max-w-[1200px] mx-auto px-6 md:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <span class="inline-block py-1 px-3 rounded-full bg-primary/10 text-primary font-bold text-xs uppercase tracking-wider mb-4 border border-primary/20">The Process</span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900">What to Expect During Your Visit</h2>
        </div>
        
        <div class="relative">
            <!-- Connecting Line (Desktop) -->
            <div class="hidden md:block absolute top-10 left-[10%] right-[10%] h-[2px] bg-slate-200 z-0"></div>
            
            <div class="grid grid-cols-1 md:grid-cols-<?= min(count($processSteps), 5) ?> gap-8 relative z-10">
                <?php foreach($processSteps as $index => $step): ?>
                <div class="flex flex-col items-center text-center group">
                    <div class="w-20 h-20 rounded-2xl bg-white border border-slate-200 shadow-md text-primary flex items-center justify-center mb-6 relative group-hover:-translate-y-2 group-hover:border-primary group-hover:shadow-lg transition-all duration-300 z-10">
                        <span class="material-symbols-outlined text-[32px]"><?= htmlspecialchars($step['icon'] ?? 'clinical_notes') ?></span>
                        <div class="absolute -top-3 -right-3 w-8 h-8 rounded-full bg-primary text-white text-sm font-bold flex items-center justify-center shadow-sm border-2 border-white">
                            <?= $index + 1 ?>
                        </div>
                    </div>
                    <h3 class="font-bold text-slate-900 text-lg mb-3"><?= htmlspecialchars($step['title']) ?></h3>
                    <p class="text-sm text-slate-600 leading-relaxed px-2"><?= htmlspecialchars($step['desc']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php if (!empty($aftercareText)): ?>
        <div class="mt-16 bg-surface p-6 rounded-2xl border border-slate-200/60 shadow-sm max-w-4xl mx-auto flex gap-4 items-start">
            <span class="material-symbols-outlined text-primary text-[28px] shrink-0">healing</span>
            <div>
                <h4 class="font-bold text-slate-900 mb-1">Post-Treatment Care</h4>
                <p class="text-sm text-slate-600 leading-relaxed"><?= htmlspecialchars($aftercareText) ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>