<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\SalesReturn;

class PrintController extends Controller
{
    public function quotation(Quotation $record)
    {
        $record->load(['items.product', 'items.vendor', 'customer']);

        return view('crm.print.quotation', ['record' => $record]);
    }

    public function invoice(Invoice $record)
    {
        $record->load(['items.product', 'customer', 'payments']);

        return view('crm.print.invoice', ['record' => $record]);
    }

    public function delivery(Delivery $record)
    {
        $record->load(['items.product', 'customer', 'invoice', 'createdBy']);

        return view('crm.print.delivery', ['record' => $record]);
    }

    public function creditNote(SalesReturn $record)
    {
        abort_unless($record->status === SalesReturn::STATUS_REFUNDED, 404);

        $record->load(['items.product', 'customer', 'invoice', 'createdBy']);

        return view('crm.print.credit-note', ['record' => $record]);
    }
}
