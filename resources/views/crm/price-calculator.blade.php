<div x-data="priceCalculator({{ (float) config('crm.default_markup_percent') }}, {{ (float) config('crm.gst_percent') }})"
    x-on:product-cost-picked.window="cost = $event.detail.cost; productLabel = $event.detail.label">
    <h1 class="text-2xl font-bold text-white mb-6">Quick Price Calculator</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                <h2 class="font-bold text-white">Product Lookup</h2>
                <p class="text-sm text-zinc-500 mt-1 mb-4">Optional — search a product to fill Cost from its current vendor price.</p>
                <x-product-search :index="0" :label="null"
                    :open-row="$productSearchRow" :search-term="$productSearch" :results="$this->productSearchResults" />
                <template x-if="productLabel">
                    <p class="text-xs text-zinc-500 mt-1.5">Using cost for <span class="text-zinc-300" x-text="productLabel"></span></p>
                </template>
            </div>

            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                <h2 class="font-bold text-white">Calculator</h2>
                <p class="text-sm text-zinc-500 mt-1 mb-4">Enter cost and markup to see the calculations.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-zinc-200 mb-1.5">Cost</label>
                        <input type="number" step="0.01" x-model.number="cost" class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-zinc-200 mb-1.5">Markup (%)</label>
                        <input type="number" step="0.01" x-model.number="markup" class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm" />
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                <h2 class="font-bold text-white">Discount</h2>
                <p class="text-sm text-zinc-500 mt-1 mb-4">Apply a discount as a percentage or a flat amount.</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-zinc-200 mb-1.5">Discount Type</label>
                        <div class="flex items-center gap-5 h-9">
                            <label class="flex items-center gap-2 text-sm text-zinc-300 cursor-pointer">
                                <input type="radio" value="percent" x-model="discountType" class="rounded-full" />
                                Percentage (%)
                            </label>
                            <label class="flex items-center gap-2 text-sm text-zinc-300 cursor-pointer">
                                <input type="radio" value="flat" x-model="discountType" class="rounded-full" />
                                Flat Amount
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-zinc-200 mb-1.5">Discount Value</label>
                        <input type="number" step="0.01" x-model.number="discountValue" class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm" />
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                <h2 class="font-bold text-white mb-4">Tax</h2>
                <div class="max-w-xs">
                    <label class="block text-sm font-semibold text-zinc-200 mb-1.5">GST Rate (%)</label>
                    <input type="number" step="0.01" x-model.number="gst" class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm" />
                </div>
            </div>
        </div>

        <div>
            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                <h2 class="font-bold text-white mb-4">Results</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between items-center px-1"><span class="text-zinc-400">Cost Price</span><span class="text-white font-medium" x-text="fmt(cost)"></span></div>
                    <div class="flex justify-between items-center px-1"><span class="text-zinc-400">Markup Amount</span><span class="text-white font-medium" x-text="fmt(markupAmount())"></span></div>
                    <div class="flex justify-between items-center bg-zinc-900 rounded-lg px-3 py-2 mt-3">
                        <span class="text-white font-bold">Selling Price (w/o GST)</span>
                        <span class="text-white font-bold" x-text="fmt(sellingPrice())"></span>
                    </div>
                    <div class="flex justify-between items-center px-1 pt-3"><span class="text-zinc-400">Discount Amount</span><span class="text-red-400 font-medium" x-text="'-' + fmt(discountAmount())"></span></div>
                    <div class="flex justify-between items-center px-1"><span class="text-zinc-400">Price after Discount</span><span class="text-white font-medium" x-text="fmt(priceAfterDiscount())"></span></div>
                    <div class="flex justify-between items-center px-1"><span class="text-zinc-400">GST Amount</span><span class="text-white font-medium" x-text="fmt(gstAmount())"></span></div>
                    <div class="flex justify-between items-center bg-zinc-900 rounded-lg px-3 py-2 mt-3">
                        <span class="text-white font-bold">Final Selling Price (w/ GST)</span>
                        <span class="text-white font-bold text-lg" x-text="fmt(finalPrice())"></span>
                    </div>
                    <div class="flex justify-between items-center px-1 pt-3"><span class="text-zinc-400">Profit</span><span class="text-accent font-medium" x-text="fmt(profit())"></span></div>
                    <div class="flex justify-between items-center px-1"><span class="text-zinc-400">Profit Margin</span><span class="text-accent font-medium" x-text="margin() + '%'"></span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function priceCalculator(defaultMarkup, defaultGst) {
        return {
            cost: 0,
            markup: defaultMarkup,
            discountType: 'percent',
            discountValue: 0,
            gst: defaultGst,
            productLabel: null,
            sellingPrice() { return this.cost * (1 + this.markup / 100); },
            markupAmount() { return this.sellingPrice() - this.cost; },
            discountAmount() {
                const amt = this.discountType === 'percent' ? this.sellingPrice() * (this.discountValue / 100) : this.discountValue;
                return Math.min(amt, this.sellingPrice());
            },
            priceAfterDiscount() { return this.sellingPrice() - this.discountAmount(); },
            gstAmount() { return this.priceAfterDiscount() * (this.gst / 100); },
            finalPrice() { return this.priceAfterDiscount() + this.gstAmount(); },
            profit() { return this.priceAfterDiscount() - this.cost; },
            margin() {
                const p = this.priceAfterDiscount();
                return p > 0 ? ((this.profit() / p) * 100).toFixed(2) : '0.00';
            },
            fmt(v) { return 'MVR ' + (isNaN(v) ? '0.00' : v.toFixed(2)); },
        };
    }
</script>
