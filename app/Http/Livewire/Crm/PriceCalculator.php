<?php

namespace App\Http\Livewire\Crm;

use Livewire\Component;

class PriceCalculator extends Component
{
    protected $layout = 'layouts.crm';

    public function render()
    {
        return view('crm.price-calculator')->layout('layouts.crm');
    }
}
