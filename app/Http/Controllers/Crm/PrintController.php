<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\Quotation;

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
}
