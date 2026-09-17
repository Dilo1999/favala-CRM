<?php

namespace Tests\Feature;

use App\Http\Livewire\Crm\Quotations\Index as QuotationsIndex;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use App\Models\Vendor;
use App\Services\CrmTestProductsClient;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class QuotationDeleteFixTest extends TestCase
{
    use DatabaseTransactions;

    public function test_deleting_a_quotation_with_a_pending_invoice_removes_the_invoice_and_restores_stock(): void
    {
        $staff = User::findOrFail(2);
        $customer = Customer::findOrFail(1);
        $product = Product::findOrFail(1);
        $vendorId = 2;
        $qty = 2;

        $client = app(CrmTestProductsClient::class);
        $before = $client->vendorQuantities($product->id)->get($vendorId)['quantity'];

        $quotation = Quotation::create([
            'customer_id' => $customer->id,
            'staff_id' => $staff->id,
            'quotation_date' => now(),
            'expiry_date' => now()->addDays(7),
            'bill_to_name' => $customer->company_name,
        ]);

        QuotationItem::create([
            'quotation_id' => $quotation->id,
            'product_id' => $product->id,
            'vendor_id' => $vendorId,
            'qty' => $qty,
            'cost' => 100,
            'markup_percent' => 15,
        ]);

        $quotation->recalculateTotals();
        $quotation->refresh();

        $invoice = $quotation->convertToInvoice($staff);

        $afterConvert = $client->vendorQuantities($product->id)->get($vendorId)['quantity'];
        $this->assertSame($before - $qty, $afterConvert, 'Stock should be decremented on invoice creation.');
        $this->assertSame(Invoice::STATUS_PENDING, $invoice->payment_status);

        $this->actingAs($staff);
        (new QuotationsIndex)->delete($quotation->id);
        $this->assertSame('Quotation deleted.', session('status'));

        $this->assertDatabaseMissing('quotations', ['id' => $quotation->id]);
        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);

        $afterDelete = $client->vendorQuantities($product->id)->get($vendorId)['quantity'];
        $this->assertSame($before, $afterDelete, 'Stock should be restored back to the pre-conversion level.');
    }

    public function test_deleting_a_quotation_with_a_paid_invoice_is_blocked_and_nothing_is_removed(): void
    {
        $staff = User::findOrFail(2);
        $customer = Customer::findOrFail(1);

        $quotation = Quotation::create([
            'customer_id' => $customer->id,
            'staff_id' => $staff->id,
            'quotation_date' => now(),
            'expiry_date' => now()->addDays(7),
            'bill_to_name' => $customer->company_name,
            'status' => Quotation::STATUS_CONVERTED,
            'grand_total' => 500,
        ]);

        $invoice = Invoice::create([
            'quotation_id' => $quotation->id,
            'customer_id' => $customer->id,
            'staff_id' => $staff->id,
            'invoice_date' => now(),
            'expiry_date' => now()->addDays(7),
            'bill_to_name' => $customer->company_name,
            'grand_total' => 500,
            'amount_paid' => 500,
            'balance_due' => 0,
            'payment_status' => Invoice::STATUS_PAID,
        ]);

        $this->actingAs($staff);
        (new QuotationsIndex)->delete($quotation->id);
        $this->assertNotEmpty(session('error'));

        $this->assertDatabaseHas('quotations', ['id' => $quotation->id]);
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'payment_status' => Invoice::STATUS_PAID]);
    }

    public function test_deleting_a_quotation_with_a_partially_paid_invoice_is_also_blocked(): void
    {
        $staff = User::findOrFail(2);
        $customer = Customer::findOrFail(1);

        $quotation = Quotation::create([
            'customer_id' => $customer->id,
            'staff_id' => $staff->id,
            'quotation_date' => now(),
            'expiry_date' => now()->addDays(7),
            'bill_to_name' => $customer->company_name,
            'status' => Quotation::STATUS_CONVERTED,
            'grand_total' => 500,
        ]);

        $invoice = Invoice::create([
            'quotation_id' => $quotation->id,
            'customer_id' => $customer->id,
            'staff_id' => $staff->id,
            'invoice_date' => now(),
            'expiry_date' => now()->addDays(7),
            'bill_to_name' => $customer->company_name,
            'grand_total' => 500,
            'amount_paid' => 200,
            'balance_due' => 300,
            'payment_status' => Invoice::STATUS_PARTIAL,
        ]);

        $this->actingAs($staff);
        (new QuotationsIndex)->delete($quotation->id);
        $this->assertNotEmpty(session('error'));

        $this->assertDatabaseHas('quotations', ['id' => $quotation->id]);
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id]);
    }
}
