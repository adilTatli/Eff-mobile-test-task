<?php

namespace Database\Seeders\Statuses;

use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rows = [
            ['title' => 'Новая',      'description' => 'Задача только создана'],
            ['title' => 'В процессе', 'description' => 'Задача в работе'],
            ['title' => 'Готово',     'description' => 'Задача выполнена'],
            ['title' => 'Отменено',   'description' => 'Задача отменена'],
        ];

        Status::upsert($rows, ['title'], ['description']);
    }
}
