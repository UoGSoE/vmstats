<?php

namespace Database\Seeders;

use App\Models\Guest;
use App\Models\Server;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::factory()->create(['username' => 'admin', 'password' => bcrypt('secret')]);
        $servers = Server::factory()->times(10)->create();
        $servers->each(function ($server) {
            foreach (range(1, 10) as $i) {
                Guest::factory()->create([
                    'server_id' => $server->id,
                    'updated_at' => now()->subDays(rand(1, 10)),
                ]);
            }
        });
    }
}
