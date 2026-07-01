<section class="py-16 bg-surface-container border-t border-slate-200 fade-in-up">
    <div class="max-w-[1200px] mx-auto px-6 md:px-8">
        <h3 class="text-2xl font-bold text-slate-900 mb-8">Explore Other Services</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php 
            $allServices = getAllServices();
            foreach($allServices as $key => $svc): 
                if ($key === $currentServiceKey) continue;
            ?>
            <a href="<?= htmlspecialchars($base_url ?? '') ?>/services/<?= urlencode($key) ?>.php" class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-primary/30 group transition-all flex flex-col justify-between h-full">
                <div>
                    <h4 class="font-bold text-slate-900 group-hover:text-primary transition-colors mb-2"><?= htmlspecialchars($svc['label']) ?></h4>
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-slate-100 text-[11px] font-medium text-slate-500">
                        <span class="material-symbols-outlined text-[14px]">schedule</span> <?= htmlspecialchars($svc['duration']) ?>
                    </span>
                </div>
                <div class="mt-6 flex items-center text-sm font-bold text-primary group-hover:gap-2 transition-all">
                    View Details <span class="material-symbols-outlined text-[18px]">arrow_right_alt</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>