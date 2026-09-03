<?php

namespace Database\Seeders;

use App\Models\Atoll;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Island;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class CrmDemoSeeder extends Seeder
{
    /** A small, realistic starter dataset so every module has something to show on first run. */
    public function run(): void
    {
        if (Vendor::count() > 0) {
            return;
        }

        $staff = User::updateOrCreate(
            ['email' => 'sales@favala.mv'],
            ['name' => 'Sales Staff', 'password' => 'password', 'role' => User::ROLE_MEMBER]
        );

        $vendors = collect(['Male Hardware Supplies', 'Reef Building Materials', 'Coral Traders Pvt Ltd'])
            ->map(fn ($name) => Vendor::create([
                'company_name' => $name,
                'contact_person' => 'Contact Person',
                'phone' => '+960 7'.random_int(100000, 999999),
                'location' => "Male', Maldives",
            ]));

        $products = collect([
            ['code' => 'FAVD-000001', 'description' => 'Portland Cement 50kg', 'category' => 'Cement', 'brand' => 'Ramco'],
            ['code' => 'FAVD-000002', 'description' => 'Deformed Steel Bar 12mm', 'category' => 'Steel', 'brand' => 'Tata'],
            ['code' => 'FAVD-000003', 'description' => 'Marine Plywood 18mm', 'category' => 'Wood', 'brand' => 'Century'],
            ['code' => 'FAVD-000004', 'description' => 'PVC Pipe 4" x 3m', 'category' => 'Plumbing', 'brand' => 'Ashirwad'],
            ['code' => 'FAVD-000005', 'description' => 'Emulsion Paint 20L', 'category' => 'Paint', 'brand' => 'Asian Paints'],
        ])->map(function ($data) use ($vendors) {
            $product = Product::create($data);
            foreach ($vendors as $vendor) {
                $product->prices()->create([
                    'vendor_id' => $vendor->id,
                    'price' => random_int(2000, 9000) / 100,
                ]);
            }

            return $product;
        });

        $kaafu = Atoll::where('name', 'Kaafu')->first();
        $male = $kaafu?->islands()->where('name', "Male'")->first();

        $customer = Customer::create([
            'company_name' => 'Islander Construction Pvt Ltd',
            'contact_person' => 'Ahmed Shifau',
            'phone' => '+960 7123456',
            'tin' => 'TIN12345678',
            'atoll_id' => $kaafu?->id,
            'island_id' => $male?->id,
            'address' => "Boduthakurufaanu Magu, Male'",
            'customer_type' => 'Contractor',
            'lead_source' => 'WhatsApp',
            'assigned_staff_id' => $staff->id,
            'status' => Customer::STATUS_CUSTOMER,
            'added_by' => $staff->id,
        ]);

        $deal = Deal::create([
            'customer_id' => $customer->id,
            'deal_date' => now()->toDateString(),
            'request_source' => 'WhatsApp',
            'assigned_staff_id' => $staff->id,
            'stage' => Deal::STAGE_HOT,
            'additional_details' => 'Site materials for a 3-storey extension.',
        ]);

        $deal->products()->create(['product_id' => $products[0]->id, 'qty' => 50]);
        $deal->products()->create(['product_id' => $products[1]->id, 'qty' => 100]);
        $deal->spawnQuery();
    }
}
