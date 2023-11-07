<?php

namespace Tests\Feature;

use App\Models\Guest;
use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServerListUiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unauthenticated_users_cant_see_the_server_list()
    {
        $response = $this->get(route('home'));

        $response->assertRedirect(route('auth.login'));
    }

    /** @test */
    public function we_can_see_the_main_page_with_the_livewire_server_list_component()
    {
        $user = User::factory()->create();
        $server1 = Server::factory()->create(['name' => 'Server 1']);
        $server2 = Server::factory()->create(['name' => 'Server 2']);
        $guest1 = Guest::factory()->create(['name' => 'Guest 1', 'server_id' => $server1->id]);
        $guest2 = Guest::factory()->create(['name' => 'Guest 2', 'server_id' => $server2->id]);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk();
        $response->assertSeeLivewire('vm-list');
        $response->assertSee('Server 1');
        $response->assertSee('Server 2');
        $response->assertSee('Guest 1');
        $response->assertSee('Guest 2');
    }

    /** @test */
    public function we_can_filter_the_list_of_vms_and_servers()
    {
        $server1 = Server::factory()->create(['name' => 'Server 1']);
        $server2 = Server::factory()->create(['name' => 'Server 2']);
        $guest1 = Guest::factory()->create(['name' => 'Guest 1', 'server_id' => $server1->id]);
        $guest2 = Guest::factory()->create(['name' => 'Guest 2', 'server_id' => $server2->id]);
        $user = User::factory()->create();

        Livewire::actingAs($user)->test('vm-list')
            ->assertSee('Server 1')
            ->assertSee('Server 2')
            ->assertSee('Guest 1')
            ->assertSee('Guest 2')
            // we don't filter on server names
            ->set('filter', 'Server 1')
            ->assertSee('Server 1')
            ->assertSee('Server 2')
            ->assertDontSee('Guest 1')
            ->assertDontSee('Guest 2')
            // filter on vm name still shows the server it is running on
            ->set('filter', 'Guest 1')
            ->assertSee('Server 1')
            ->assertSee('Guest 1')
            ->assertSee('Server 2')
            ->assertDontSee('Guest 2')
            ->set('filter', 'Guest 2')
            ->assertSee('Server 1')
            ->assertDontSee('Guest 1')
            ->assertSee('Server 2')
            ->assertSee('Guest 2');
    }
}
