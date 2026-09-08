<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

// Serve storage files when symlink doesn't work (e.g. shared hosting / cPanel)
Route::match(['get', 'head'], 'storage/{path}', function () {
    $requestPath = ltrim(request()->path(), '/');
    if (!str_starts_with($requestPath, 'storage/')) {
        abort(404);
    }
    $path = substr($requestPath, 8); // strip 'storage/'
    if (empty($path) || str_contains($path, '..')) {
        abort(404);
    }

    $fullPath = storage_path('app/public/' . $path);
    $realPath = realpath($fullPath);
    $storageRoot = realpath(storage_path('app/public'));

    if (!$realPath || !is_file($realPath)) {
        abort(404);
    }
    if ($storageRoot && !Str::startsWith($realPath, $storageRoot)) {
        abort(404);
    }

    return response()->file($realPath);
})->where('path', '.*')->name('storage.serve');

Route::get('/', function () {
    if (auth()->check() && auth()->user()->canAccessCrm()) {
        return redirect()->route('crm.dashboard');
    }

    return redirect()->route('login');
})->name('home');

// --- CRM authentication -----------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Crm\AuthController::class, 'show'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Crm\AuthController::class, 'login']);
    Route::get('/register', [\App\Http\Controllers\Crm\RegisterController::class, 'show'])->name('register');
    Route::post('/register', [\App\Http\Controllers\Crm\RegisterController::class, 'register']);
});
Route::post('/logout', [\App\Http\Controllers\Crm\AuthController::class, 'logout'])->name('logout')->middleware('auth');

// --- CRM application ---------------------------------------------------------
Route::middleware(['auth', 'crm'])->group(function () {
    Route::get('/dashboard', \App\Http\Livewire\Crm\Dashboard::class)->name('crm.dashboard');

    Route::get('/leads', \App\Http\Livewire\Crm\Leads\Index::class)->name('crm.leads');

    Route::get('/queries', \App\Http\Livewire\Crm\Queries\Board::class)->name('crm.queries');
    Route::get('/queries/{record}', \App\Http\Livewire\Crm\Queries\Detail::class)->name('crm.queries.show');

    Route::get('/tasks', \App\Http\Livewire\Crm\Tasks\Index::class)->name('crm.tasks');

    Route::get('/activities', \App\Http\Livewire\Crm\Activities\Index::class)->name('crm.activities');

    Route::get('/deals', \App\Http\Livewire\Crm\Deals\Index::class)->name('crm.deals');
    Route::get('/deals/create', \App\Http\Livewire\Crm\Deals\Create::class)->name('crm.deals.create');
    Route::get('/deals/{record}', \App\Http\Livewire\Crm\Deals\Show::class)->name('crm.deals.show');
    Route::get('/deals/{record}/edit', \App\Http\Livewire\Crm\Deals\Edit::class)->name('crm.deals.edit');

    Route::get('/quotations', \App\Http\Livewire\Crm\Quotations\Index::class)->name('crm.quotations');
    Route::get('/quotations/create', \App\Http\Livewire\Crm\Quotations\Formulate::class)->name('crm.quotations.create');
    Route::get('/quotations/{record}', \App\Http\Livewire\Crm\Quotations\Show::class)->name('crm.quotations.show');
    Route::get('/quotations/{record}/edit', \App\Http\Livewire\Crm\Quotations\Formulate::class)->name('crm.quotations.edit');
    Route::get('/quotations/{record}/print', [\App\Http\Controllers\Crm\PrintController::class, 'quotation'])->name('print.quotation');

    Route::get('/invoices', \App\Http\Livewire\Crm\Invoices\Index::class)->name('crm.invoices');
    Route::get('/invoices/create', \App\Http\Livewire\Crm\Invoices\Create::class)->name('crm.invoices.create');
    Route::get('/invoices/{record}', \App\Http\Livewire\Crm\Invoices\Show::class)->name('crm.invoices.show');
    Route::get('/invoices/{record}/print', [\App\Http\Controllers\Crm\PrintController::class, 'invoice'])->name('print.invoice');

    Route::get('/deliveries', \App\Http\Livewire\Crm\Deliveries\Index::class)->name('crm.deliveries');
    Route::get('/deliveries/create', \App\Http\Livewire\Crm\Deliveries\Create::class)->name('crm.deliveries.create');
    Route::get('/deliveries/{record}', \App\Http\Livewire\Crm\Deliveries\Show::class)->name('crm.deliveries.show');
    Route::get('/deliveries/{record}/print', [\App\Http\Controllers\Crm\PrintController::class, 'delivery'])->name('print.delivery');

    Route::get('/returns', \App\Http\Livewire\Crm\Returns\Index::class)->name('crm.returns');
    Route::get('/returns/create', \App\Http\Livewire\Crm\Returns\Create::class)->name('crm.returns.create');

    Route::get('/receipts', \App\Http\Livewire\Crm\Receipts\Index::class)->name('crm.receipts');

    Route::get('/products', \App\Http\Livewire\Crm\Products\Index::class)->name('crm.products');
    Route::get('/products/pricing', \App\Http\Livewire\Crm\Products\Pricing::class)->name('crm.products.pricing');
    Route::get('/prices', \App\Http\Livewire\Crm\Prices\Index::class)->name('crm.prices');
    Route::get('/vendors', \App\Http\Livewire\Crm\Vendors\Index::class)->name('crm.vendors');

    // --- Shop Catalog support system (separate database — see App\Models\ShopCatalog) ---
    Route::get('/shop-catalog/shops', \App\Http\Livewire\ShopCatalog\Shops\Index::class)->name('shop-catalog.shops');
    Route::get('/shop-catalog/products', \App\Http\Livewire\ShopCatalog\Products\Index::class)->name('shop-catalog.products');
    Route::get('/shop-catalog/prices', \App\Http\Livewire\ShopCatalog\Prices\Index::class)->name('shop-catalog.prices');

    Route::get('/targets', \App\Http\Livewire\Crm\Targets\Index::class)->name('crm.targets');
    Route::get('/tools/price-calculator', \App\Http\Livewire\Crm\PriceCalculator::class)->name('crm.price-calculator');
    Route::get('/settings', \App\Http\Livewire\Crm\Settings\Index::class)->name('crm.settings');
});
