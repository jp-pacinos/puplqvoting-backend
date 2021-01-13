<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->positions() as $position) {
            Position::create($position);
        }
    }

    public function positions()
    {
        return [
            [
                'name' => 'President', // PRESIDENT
                'order' => 0,
                'per_party_count' => 1,
                'choose_max_count' => 1,
            ],
            [
                'name' => 'Vice President',
                'order' => 1,
                'per_party_count' => 1,
                'choose_max_count' => 1,
            ],
            [
                'name' => 'Secretary', // SECRETARY
                'order' => 2,
                'per_party_count' => 1,
                'choose_max_count' => 1,
            ],
            [
                'name' => 'Treasurer', // TREASURER
                'order' => 3,
                'per_party_count' => 1,
                'choose_max_count' => 1,
            ],
            [
                'name' => 'Auditor', // AUDITOR
                'order' => 4,
                'per_party_count' => 1,
                'choose_max_count' => 1,
            ],
            [
                'name' => 'Councilor', // COUNCILOR
                'order' => 5,
                'per_party_count' => 12,
                'choose_max_count' => 12,
            ],
        ];
    }
}
