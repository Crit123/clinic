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

<section class="py-16 md:py-24 bg-surface-container-lowest border-t border-outline-variant/40 fade-in-up">
    <div class="max-w-[800px] mx-auto px-6 md:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-extrabold text-on-background mb-4">Frequently Asked Questions</h2>
            <p class="text-on-surface-variant">Common inquiries regarding this specific treatment.</p>
        </div>
        
        <div class="space-y-4">
            <?php foreach($faqList as $index => $faq): ?>
            <details class="group border border-outline-variant rounded-2xl bg-surface-container-lowest overflow-hidden shadow-sm open:border-primary/40 open:ring-1 open:ring-primary/20 transition-all duration-300" <?= $index === 0 ? 'open' : '' ?>>
                <summary class="flex justify-between items-center gap-4 cursor-pointer p-6 font-bold text-on-surface hover:text-primary transition-colors focus:outline-none list-none [&::-webkit-details-marker]:hidden">
                    <span><?= htmlspecialchars($faq['q']) ?></span>
                    <span aria-hidden="true" class="relative w-5 h-5 shrink-0">
                        <span class="absolute inset-0 m-auto w-3.5 h-[2px] rounded-full bg-outline group-open:bg-primary transition-colors duration-300"></span>
                        <span class="absolute inset-0 m-auto w-3.5 h-[2px] rounded-full bg-outline group-open:bg-primary rotate-90 group-open:rotate-0 transition-all duration-300"></span>
                    </span>
                </summary>
                <div class="px-6 pb-6 text-on-surface-variant leading-relaxed border-t border-outline-variant/20 mt-2 pt-4 text-sm">
                    <?= htmlspecialchars($faq['a']) ?>
                </div>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>