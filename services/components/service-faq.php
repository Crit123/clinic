<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(function ($faq) {
        return [
            '@type' => 'Question',
            'name' => $faq['q'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $faq['a']
            ]
        ];
    }, $faqList)
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
</script>

<section class="py-16 md:py-24 bg-surface-container-lowest border-t border-slate-100 fade-in-up">
    <div class="max-w-[800px] mx-auto px-6 md:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-extrabold text-slate-900 mb-4">Frequently Asked Questions</h2>
            <p class="text-slate-600">Common inquiries regarding this specific treatment.</p>
        </div>
        
        <div class="space-y-4">
            <?php foreach($faqList as $index => $faq): ?>
            <details class="group border border-slate-200 rounded-2xl bg-white overflow-hidden shadow-sm open:border-primary/40 open:ring-1 open:ring-primary/20 transition-all duration-300" <?= $index === 0 ? 'open' : '' ?>>
                <summary class="flex justify-between items-center cursor-pointer p-6 font-bold text-slate-800 hover:text-primary transition-colors focus:outline-none list-none [&::-webkit-details-marker]:hidden">
                    <?= htmlspecialchars($faq['q']) ?>
                    <span aria-hidden="true" class="material-symbols-outlined text-slate-400 group-open:rotate-180 group-open:text-primary transition-transform duration-300">expand_more</span>
                </summary>
                <div class="px-6 pb-6 text-slate-600 leading-relaxed border-t border-slate-50 mt-2 pt-4 text-sm">
                    <?= htmlspecialchars($faq['a']) ?>
                </div>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>