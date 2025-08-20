<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseSeeder extends Seeder
{
    public function run()
    {
        DB::table('courses')->insert([
            [
                'name' => 'Curso de Desarrollo Web',
                'description' => 'Curso introductorio a la programación y desarrollo web.',
                'duration_hours' => 40,
                'serial_prefix' => 'DW',
                'serial_counter' => 0,
            ],
            [
                'name' => 'Curso de Diseño Gráfico',
                'description' => 'Aprende a usar herramientas de diseño gráfico como Photoshop e Illustrator.',
                'duration_hours' => 50,
                'serial_prefix' => 'DG',
                'serial_counter' => 0,
            ],
            [
                'name' => 'Curso de Marketing Digital',
                'description' => 'Conceptos clave del marketing digital, SEO, SEM, redes sociales y más.',
                'duration_hours' => 30,
                'serial_prefix' => 'MD',
                'serial_counter' => 0,
            ],
        ]);
    }
}
