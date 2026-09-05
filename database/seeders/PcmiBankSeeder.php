<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PcmiBank;
use App\Models\Bank;

class PcmiBankSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Clean existing data
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        PcmiBank::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Ensure Master Banks exist
        $masterBanks = [
            ['bank_code' => '014', 'bank_name' => 'Bank Central Asia', 'bank_brand' => 'BCA'],
            ['bank_code' => '008', 'bank_name' => 'Bank Mandiri', 'bank_brand' => 'Mandiri'],
            ['bank_code' => '002', 'bank_name' => 'Bank Rakyat Indonesia', 'bank_brand' => 'BRI'],
            ['bank_code' => '200', 'bank_name' => 'Bank Tabungan Negara', 'bank_brand' => 'BTN'],
            ['bank_code' => '490', 'bank_name' => 'Bank Raya Indonesia', 'bank_brand' => 'Bank Raya'],
        ];

        foreach ($masterBanks as $mb) {
            Bank::updateOrCreate(['bank_code' => $mb['bank_code']], $mb);
        }

        $bca = Bank::where('bank_brand', 'BCA')->first();
        $mandiri = Bank::where('bank_brand', 'Mandiri')->first();
        $bri = Bank::where('bank_brand', 'BRI')->first();
        $btn = Bank::where('bank_brand', 'BTN')->first();
        $rayan = Bank::where('bank_brand', 'Bank Raya')->first();

        // 3. Seed PCMI Banks from image
        $data = [
            [
                'bank_id' => $bca->id,
                'account_no' => '5750 489 666',
                'branch' => 'Sahardjo',
                'holder_name' => 'RD Hidianitje',
                'type' => 'Bank'
            ],
            [
                'bank_id' => $mandiri->id,
                'account_no' => '122 000 487 5566',
                'branch' => 'Mid Plaza',
                'holder_name' => 'PT Pan Convince Mitra International',
                'type' => 'Bank'
            ],
            [
                'bank_id' => $bca->id,
                'account_no' => '5350 285 999',
                'branch' => 'Juanda',
                'holder_name' => 'PT Pan Convince Mitra International',
                'type' => 'Bank'
            ],
            [
                'bank_id' => $bri->id,
                'account_no' => '1125 0100 0255 301',
                'branch' => 'Sahardjo',
                'holder_name' => 'PT Pan Convince Mitra International',
                'type' => 'Bank'
            ],
            [
                'bank_id' => $btn->id,
                'account_no' => '0000 1013 0001 2935',
                'branch' => 'Kuningan',
                'holder_name' => 'PT Pan Convince Mitra International',
                'type' => 'Bank'
            ],
            [
                'bank_id' => $rayan->id,
                'account_no' => '0010 0100 1907 409',
                'branch' => 'GatSu',
                'holder_name' => 'PT Pan Convince Mitra International',
                'type' => 'Bank'
            ],
        ];

        foreach ($data as $item) {
            PcmiBank::create($item);
        }
    }
}
