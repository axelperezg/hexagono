<?php

namespace Database\Seeders;

use App\Models\Sector;
use Illuminate\Database\Seeder;

class SectorSeeder extends Seeder
{
    /**
     * Seed the default sectors. Safe to re-run.
     */
    public function run(): void
    {
        $sectors = [
            'SEMAR',
            'SEDENA',
            'SICT',
            'SEMARNAT',
            'SADER',
            'SHCP',
            'SEGOB',
            'CULTURA',
            'SEP',
            'CONAHCYT',
            'SENER',
            'SS',
            'SE',
            'CEAV',
            'STPS',
            'SEDATU',
            'MEJOREDU',
            'BIENESTAR',
            'FGR',
            'SECTUR',
            'SSPC',
            'ISSSTE',
            'IMER',
            'IMSS',
            'INEGI',
            'INMUJERES',
            'INPI',
            'INAI',
            'INEE',
            'PRESIDENCIA',
            'PRODECON',
            'SABG',
            'SRE',
        ];

        foreach ($sectors as $name) {
            Sector::updateOrCreate(['name' => $name]);
        }
    }
}
