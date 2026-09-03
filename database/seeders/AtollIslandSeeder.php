<?php

namespace Database\Seeders;

use App\Models\Atoll;
use Illuminate\Database\Seeder;

class AtollIslandSeeder extends Seeder
{
    /** The 20 administrative atolls of the Maldives, each with a handful of key islands/resorts. */
    public function run(): void
    {
        $atolls = [
            'Haa Alif' => ['Dhidhdhoo', 'Thakandhoo', 'Hoarafushi'],
            'Haa Dhaalu' => ['Kulhudhuffushi', 'Nolhivaram', 'Hanimaadhoo'],
            'Shaviyani' => ['Funadhoo', 'Milandhoo', 'Kanditheemu'],
            'Noonu' => ['Manadhoo', 'Velidhoo', 'Holhudhoo'],
            'Raa' => ['Ungoofaaru', 'Dhuvaafaru', 'Alifushi'],
            'Baa' => ['Eydhafushi', 'Goidhoo', 'Kendhoo'],
            'Lhaviyani' => ['Naifaru', 'Hinnavaru', 'Kurendhoo'],
            'Kaafu' => ["Male'", 'Hulhumale', 'Thulusdhoo', 'Guraidhoo'],
            'Alifu Alifu' => ['Rasdhoo', 'Thoddoo', 'Ukulhas'],
            'Alifu Dhaalu' => ['Mahibadhoo', 'Dhangethi', 'Maamigili'],
            'Vaavu' => ['Felidhoo', 'Keyodhoo', 'Thinadhoo'],
            'Meemu' => ['Muli', 'Naalaafushi', 'Kolhufushi'],
            'Faafu' => ['Nilandhoo', 'Magoodhoo', 'Bilehdhoo'],
            'Dhaalu' => ['Kudahuvadhoo', 'Meedhoo', 'Rinbudhoo'],
            'Thaa' => ['Veymandoo', 'Thimarafushi', 'Guraidhoo'],
            'Laamu' => ['Fonadhoo', 'Gan', 'Maabaidhoo'],
            'Gaafu Alifu' => ['Viligili', 'Kolamaafushi', 'Nilandhoo'],
            'Gaafu Dhaalu' => ['Thinadhoo', 'Madaveli', 'Fiyoari'],
            'Gnaviyani' => ['Fuvahmulah'],
            'Seenu' => ['Hithadhoo', 'Maradhoo', 'Feydhoo', 'Gan'],
        ];

        foreach ($atolls as $name => $islands) {
            $atoll = Atoll::firstOrCreate(['name' => $name]);
            foreach ($islands as $island) {
                $atoll->islands()->firstOrCreate(['name' => $island]);
            }
        }
    }
}
