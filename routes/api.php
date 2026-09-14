<?php

use App\Http\Controllers\Api\CrmTestProductsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Temporary sandbox: serves "Source: CRM" products mirrored into the
// separate crm_test database, for CrmTestProductsClient to fetch via HTTP.
Route::get('/crm-test/products', [CrmTestProductsController::class, 'index']);
