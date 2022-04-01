<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Guest;
use App\Models\Server;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ServerListUiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function we_can_see_the_main_page_with_the_livewire_server_list_component()
    {
        $server1 = Server::factory()->create(['name' => 'Server 1']);
        $server2 = Server::factory()->create(['name' => 'Server 2']);
        $guest1 = Guest::factory()->create(['name' => 'Guest 1', 'server_id' => $server1->id]);
        $guest2 = Guest::factory()->create(['name' => 'Guest 2', 'server_id' => $server2->id]);

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSeeLivewire('vm-list');
        $response->assertSee('Server 1');
        $response->assertSee('Server 2');
        $response->assertSee('Guest 1');
        $response->assertSee('Guest 2');
    }
}
