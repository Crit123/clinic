<section class="py-16 md:py-24 bg-surface-container-low border-t border-slate-200 fade-in-up relative z-20">
    <div class="max-w-[1200px] mx-auto px-6 md:px-8">
        
        <?php if (!empty($isEmergency)): ?>
            <div class="bg-red-50 border-l-4 border-red-500 rounded-r-xl p-6 mb-12 shadow-sm flex items-start gap-4">
                <span class="material-symbols-outlined text-red-500 text-[32px] shrink-0 mt-1">emergency</span>
                <div>
                    <h3 class="text-xl font-bold text-red-800 mb-2">Immediate Attention Required?</h3>
                    <p class="text-red-700 leading-relaxed">If you are experiencing severe pain, uncontrolled bleeding, or dental trauma, please contact us immediately or walk into our clinic. We reserve priority slots specifically for urgent cases.</p>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 relative">
            <!-- Text Content -->
            <div class="lg:col-span-7 space-y-10">
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold text-slate-900 mb-4 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-primary text-sm shrink-0"><span class="material-symbols-outlined text-[20px]">info</span></span>
                        What is this treatment?
                    </h2>
                    <p class="text-slate-600 leading-relaxed text-lg"><?= htmlspecialchars($overviewText) ?></p>
                </div>
                
                <div class="w-16 h-1 bg-slate-200 rounded-full"></div>
                
                <div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-4">Why It's Important</h3>
                    <p class="text-slate-600 leading-relaxed"><?= htmlspecialchars($whyImportantText) ?></p>
                </div>
                
                <div class="w-16 h-1 bg-slate-200 rounded-full"></div>
                
                <div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-4">Ideal Candidates</h3>
                    <p class="text-slate-600 leading-relaxed"><?= htmlspecialchars($idealCandidatesText) ?></p>
                </div>
                
                <?php if (!empty($beforeVisitText)): ?>
                <div class="bg-surface-container rounded-2xl p-6 border border-slate-200/60 shadow-sm mt-8">
                    <h4 class="font-bold text-slate-900 mb-2 flex items-center gap-2"><span class="material-symbols-outlined text-primary text-[20px]">fact_check</span> Before Your Visit</h4>
                    <p class="text-slate-600 text-sm leading-relaxed"><?= htmlspecialchars($beforeVisitText) ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($pricingText)): ?>
                <div class="bg-surface-container rounded-2xl p-6 border border-slate-200/60 shadow-sm mt-6">
                    <h4 class="font-bold text-slate-900 mb-2 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px]" aria-hidden="true">payments</span>
                        Estimated Pricing
                    </h4>
                    <p class="text-slate-600 text-sm leading-relaxed"><?= htmlspecialchars($pricingText) ?></p>
                    <p class="text-slate-500 text-xs leading-relaxed mt-3 italic">Estimate only — final cost is confirmed during your visit based on your specific treatment needs.</p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- What's Included Sticky Card -->
            <div class="lg:col-span-5">
                <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-md sticky top-32">
                    <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-2 pb-4 border-b border-slate-100">
                        <span class="material-symbols-outlined text-primary text-[28px]">verified</span> What's Included
                    </h3>
                    <ul class="space-y-6">
                        <?php foreach($includedFeatures as $feature): ?>
                        <li class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100 shadow-sm">
                                <span class="material-symbols-outlined text-[20px]">check</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-base mb-1"><?= htmlspecialchars($feature['title']) ?></h4>
                                <p class="text-sm text-slate-500 leading-relaxed"><?= htmlspecialchars($feature['desc']) ?></p>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>