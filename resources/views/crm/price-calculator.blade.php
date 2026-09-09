<div x-data="priceCalculator({{ (float) config('crm.default_markup_percent') }}, {{ (float) config('crm.gst_percent') }})"
    x-on:product-picked.window="addRow($event.detail.cost, $event.detail.label)">
    <h1 class="text-2xl font-bold text-white mb-6">Quick Price Calculator</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                <h2 class="font-bold text-white">Products</h2>
                <p class="text-sm text-zinc-500 mt-1 mb-4">Search a product to add it — its cost fills in automatically. Search again to add another.</p>

                <div class="mb-4">
                    <x-product-search :index="0" :label="null"
                        :open-row="$productSearchRow" :search-term="$productSearch" :results="$this->productSearchResults" />
                </div>

                <template x-if="rows.length === 0">
                    <p class="text-sm text-zinc-500 text-center py-4 border border-dashed border-white/10 rounded-lg">No products added yet — search above to add one.</p>
                </template>

                <template x-for="(row, idx) in rows" :key="idx">
                    <div class="rounded-lg border border-white/10 bg-zinc-900/40 p-4 mb-3">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold uppercase tracking-wide text-zinc-500" x-text="row.productLabel ? row.productLabel : 'Product ' + (idx + 1)"></span>
                            <button type="button" @click="removeRow(idx)" title="Remove product"
                                class="h-7 w-7 flex items-center justify-center rounded-lg text-zinc-500 hover:text-red-400 hover:bg-red-500/10 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-zinc-200 mb-1.5">Cost</label>
                                <input type="number" step="0.01" x-model.number="row.cost" class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-zinc-200 mb-1.5">Markup (%)</label>
                                <input type="number" step="0.01" x-model.number="row.markup" class="w-full rounded-lg bg-zinc-900 border-white/10 text-white text-sm" />
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5">
                <h2 class="font-bold text-white">Discount</h2>
                <p class="text-sm text-zinc-500 mt-1 mb-4">Apply a discount as a percentage or a flat amount to the combined total.</p>
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
            <div class="rounded-xl bg-zinc-800 border border-white/10 p-5 lg:sticky lg:top-6">
                <h2 class="font-bold text-white mb-1">Results</h2>
                <p class="text-xs text-zinc-500 mb-4" x-show="rows.length > 1" x-text="rows.length + ' products combined'"></p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between items-center px-1"><span class="text-zinc-400">Cost Price</span><span class="text-white font-medium" x-text="fmt(totalCost())"></span></div>
                    <div class="flex justify-between items-center px-1"><span class="text-zinc-400">Markup Amount</span><span class="text-white font-medium" x-text="fmt(markupAmount())"></span></div>
                    <div class="flex justify-between items-center bg-zinc-900 rounded-lg px-3 py-2 mt-3">
                        <span class="text-white font-bold">Selling Price (w/o GST)</span>
                        <span class="text-white font-bold" x-text="fmt(subtotal())"></span>
                    </div>
                    <div class="flex justify-between items-center px-1 pt-3"><span class="text-zinc-400">Discount Amount</span><span class="text-red-400 font-medium" x-text="'-' + fmt(discountAmount())"></span></div>
                    <div class="flex justify-between items-center px-1"><span class="text-zinc-400">Price after Discount</span><span class="text-white font-medium" x-text="fmt(priceAfterDiscount())"></span></div>
                    <div class="flex justify-between items-center px-1"><span class="text-zinc-400">GST Amount</span><span class="text-white font-medium" x-text="fmt(gstAmount())"></span></div>
                    <div class="flex justify-between items-center bg-zinc-900 rounded-lg px-3 py-2 mt-3">
                        <span class="text-white font-bold">Final Selling Price (w/ GST)</span>
                        <span class="text-white font-bold text-lg" x-text="fmt(finalPrice())"></span>
                    </div>
                    <div class="flex justify-between items-center px-1 pt-3"><span class="text-zinc-400">Profit</span><span class="text-accent font-medium" x-text="fmt(totalProfit())"></span></div>
                    <div class="flex justify-between items-center px-1"><span class="text-zinc-400">Profit Margin</span><span class="text-accent font-medium" x-text="margin() + '%'"></span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function priceCalculator(defaultMarkup, defaultGst) {
        return {
            rows: [],
            discountType: 'percent',
            discountValue: 0,
            gst: defaultGst,
            defaultMarkup: defaultMarkup,
            addRow(cost, label) {
                this.rows.push({ cost: Number(cost) || 0, markup: this.defaultMarkup, productLabel: label || null });
            },
            removeRow(index) {
                this.rows.splice(index, 1);
            },
            sellingPrice(row) { return Number(row.cost || 0) * (1 + Number(row.markup || 0) / 100); },
            subtotal() { return this.rows.reduce((sum, row) => sum + this.sellingPrice(row), 0); },
            totalCost() { return this.rows.reduce((sum, row) => sum + Number(row.cost || 0), 0); },
            markupAmount() { return this.subtotal() - this.totalCost(); },
            discountAmount() {
                const amt = this.discountType === 'percent' ? this.subtotal() * (this.discountValue / 100) : Number(this.discountValue || 0);
                return Math.min(amt, this.subtotal());
            },
            priceAfterDiscount() { return this.subtotal() - this.discountAmount(); },
            gstAmount() { return this.priceAfterDiscount() * (this.gst / 100); },
            finalPrice() { return this.priceAfterDiscount() + this.gstAmount(); },
            totalProfit() { return this.subtotal() - this.totalCost(); },
            margin() {
                const s = this.subtotal();
                return s > 0 ? ((this.totalProfit() / s) * 100).toFixed(2) : '0.00';
            },
            fmt(v) { return 'MVR ' + (isNaN(v) ? '0.00' : v.toFixed(2)); },
        };
    }
</script>
