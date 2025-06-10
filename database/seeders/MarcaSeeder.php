<?php

namespace Database\Seeders;

// database/seeders/MarcaSeeder.php

use App\Models\Marca;
use Illuminate\Database\Seeder;

class MarcaSeeder extends Seeder
{
    public function run()
    {
        $marcas = [
            ['nombre' => 'Sony'],
            ['nombre' => 'HP'],
            ['nombre' => 'DELL'],
            ['nombre' => '3M'],
            ['nombre' => 'Bosch'],
        ];

        foreach ($marcas as $marca) {
            Marca::create($marca);
        }
    }
}