<section class="py-16 md:py-24 bg-surface-container-low border-t border-slate-100 fade-in-up">
    <div class="max-w-[1200px] mx-auto px-6 md:px-8">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-4">Treatment Benefits</h2>
            <p class="text-slate-600">Discover how this specialized clinical service can enhance your oral health and overall well-being.</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($benefitsList as $benefit): ?>
            <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                <div class="w-14 h-14 rounded-2xl bg-primary/10 text-primary flex items-center justify-center mb-6">
                    <span class="material-symbols-outlined text-[28px]"><?= htmlspecialchars($benefit['icon']) ?></span>
                </div>
                <h4 class="font-bold text-slate-900 text-lg mb-3"><?= htmlspecialchars($benefit['title']) ?></h4>
                <p class="text-slate-600 text-sm leading-relaxed"><?= htmlspecialchars($benefit['desc']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>