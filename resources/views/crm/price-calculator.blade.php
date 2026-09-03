<div x-data="priceCalculator()" class="max-w-4xl">
    <h1 class="text-2xl font-bold text-white mb-1">Quick Price Calculator</h1>
    <p class="text-sm text-zinc-500 mb-6">Standalone tool — works instantly, no data dependency.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="rounded-xl bg-zinc-800 border border-white/10 p-5 space-y-4">
            <h2 class="font-bold text-white mb-2">Inputs</h2>
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Cost</label>
                <input type="number" step="0.01" x-model.number="cost" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
            </div>
            <div>
                <label class="block text-xs text-zinc-400 mb-1">Markup %</label>
                <input type="number" step="0.01" x-model.number="markup" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-zinc-400 mb-1">Discount Type</label>
                    <select x-model="discountType" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm">
                        <option value="percent">Percentage</option>
                        <option value="flat">Flat</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-zinc-400 mb-1">Discount Value</label>
                    <input type="number" step="0.01" x-model.number="discountValue" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
                </div>
            </div>
            <div>
                <label class="block text-xs text-zinc-400 mb-1">GST Rate %</label>
                <input type="number" step="0.01" x-model.number="gst" class="w-full rounded-lg bg-zinc-700 border-white/10 text-white text-sm" />
            </div>
        </div>

        <div class="rounded-xl bg-zinc-800 border border-white/10 p-5 space-y-2 text-sm">
            <h2 class="font-bold text-white mb-2">Results</h2>
            <div class="flex justify-between text-zinc-400"><span>Cost Price</span><span class="text-white" x-text="fmt(cost)"></span></div>
            <div class="flex justify-between text-zinc-400"><span>Markup Amount</span><span class="text-white" x-text="fmt(markupAmount())"></span></div>
            <div class="flex justify-between text-zinc-400"><span>Selling Price (w/o GST)</span><span class="text-white" x-text="fmt(sellingPrice())"></span></div>
            <div class="flex justify-between text-zinc-400"><span>Discount Amount</span><span class="text-red-400" x-text="'- ' + fmt(discountAmount())"></span></div>
            <div class="flex justify-between text-zinc-400"><span>Price after Discount</span><span class="text-white" x-text="fmt(priceAfterDiscount())"></span></div>
            <div class="flex justify-between text-zinc-400"><span>GST Amount</span><span class="text-white" x-text="fmt(gstAmount())"></span></div>
            <div class="flex justify-between text-white font-bold text-base border-t border-white/10 pt-2">
                <span>Final Selling Price (w/ GST)</span><span x-text="fmt(finalPrice())"></span>
            </div>
            <div class="flex justify-between text-zinc-400 pt-2"><span>Profit</span><span class="text-green-400" x-text="fmt(profit())"></span></div>
            <div class="flex justify-between text-zinc-400"><span>Profit Margin</span><span class="text-green-400" x-text="margin() + '%'"></span></div>
        </div>
    </div>
</div>

<script>
    function priceCalculator() {
        return {
            cost: 100,
            markup: 15,
            discountType: 'percent',
            discountValue: 0,
            gst: 8,
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
