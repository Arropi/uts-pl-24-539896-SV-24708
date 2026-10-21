<?php

namespace Database\Seeders;

use App\Models\CoralColony;
use Illuminate\Database\Seeder;

class CoralColonySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colonies = [
            [
                'kode_fragmen' => 'CRL-2026-001',
                'spesies_ilmiah' => 'Acropora cervicornis',
                'zona_terumbu' => 'Sektor Utara - Karimunjawa',
                'kedalaman_meter' => 8.5,
                'persentase_hidup' => 95,
                'status_kesehatan' => 'Sehat',
                'catatan_observasi' => 'Pertumbuhan cabang baru terlihat sangat aktif dan pigmentasi normal.',
            ],
            [
                'kode_fragmen' => 'CRL-2026-002',
                'spesies_ilmiah' => 'Porites lutea',
                'zona_terumbu' => 'Sektor Timur - Karimunjawa',
                'kedalaman_meter' => 12.0,
                'persentase_hidup' => 60,
                'status_kesehatan' => 'Pemulihan',
                'catatan_observasi' => 'Paska kenaikan suhu air laut, jaringan mulai pulih dan zooxanthellae kembali.',
            ],
            [
                'kode_fragmen' => 'CRL-2026-003',
                'spesies_ilmiah' => 'Pocillopora damicornis',
                'zona_terumbu' => 'Zona Inti Terumbu - Bunaken',
                'kedalaman_meter' => 6.2,
                'persentase_hidup' => 35,
                'status_kesehatan' => 'Pemutihan/Bleaching',
                'catatan_observasi' => 'Terjadi pemutihan parsial akibat sedimentasi dan suhu panas permukaan.',
            ],
            [
                'kode_fragmen' => 'CRL-2026-004',
                'spesies_ilmiah' => 'Acropora palmata',
                'zona_terumbu' => 'Zona Restorasi Barat - Raja Ampat',
                'kedalaman_meter' => 5.0,
                'persentase_hidup' => 90,
                'status_kesehatan' => 'Sehat',
                'catatan_observasi' => 'Fragmen terfiksasi kuat pada rak substrat buatan.',
            ],
            [
                'kode_fragmen' => 'CRL-2026-005',
                'spesies_ilmiah' => 'Montipora capitata',
                'zona_terumbu' => 'Sektor Selatan - Karimunjawa',
                'kedalaman_meter' => 15.3,
                'persentase_hidup' => 80,
                'status_kesehatan' => 'Sehat',
                'catatan_observasi' => 'Koloni bertambah lebar 3 cm dibanding kuartal sebelumnya.',
            ],
        ];

        foreach ($colonies as $colony) {
            CoralColony::updateOrCreate(
                ['kode_fragmen' => $colony['kode_fragmen']],
                $colony
            );
        }
    }
}
