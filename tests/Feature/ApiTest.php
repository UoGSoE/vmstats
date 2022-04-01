<?php

namespace Tests\Feature;

use App\Models\Guest;
use Tests\TestCase;
use App\Models\Server;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function we_can_store_an_incoming_vm_guest_request()
    {
        $response = $this->postJson(route('api.vm.store'), [
            'server' => 'Test Server',
            'guest' => 'Test Guest',
        ]);

        $response->assertOk();
        $this->assertEquals(1, Server::count());
        $this->assertEquals(1, Guest::count());
        $server = Server::first();
        $guest = Guest::first();
        $this->assertEquals('Test Server', $server->name);
        $this->assertEquals('Test Guest', $guest->name);
        $this->assertEquals($server->id, $guest->server_id);
        $this->assertTrue($server->guests()->first()->is($guest));
    }

    /** @test */
    public function we_can_store_an_incoming_vm_guest_request_with_optional_notes_for_the_guest_and_server()
    {
        $response = $this->postJson(route('api.vm.store'), [
            'server' => 'Test Server',
            'server_notes' => 'Blah blah blah',
            'guest' => 'Test Guest',
            'guest_notes' => 'Tum te tum',
        ]);

        $response->assertOk();
        $this->assertEquals(1, Server::count());
        $this->assertEquals(1, Guest::count());
        $server = Server::first();
        $guest = Guest::first();
        $this->assertEquals('Test Server', $server->name);
        $this->assertEquals('Blah blah blah', $server->notes);
        $this->assertEquals('Test Guest', $guest->name);
        $this->assertEquals('Tum te tum', $guest->notes);
        $this->assertEquals($server->id, $guest->server_id);
        $this->assertTrue($server->guests()->first()->is($guest));
    }

    /** @test */
    public function we_can_update_just_the_notes_for_a_server_or_guest()
    {
        $server = Server::factory()->create(['notes' => 'Test Server']);
        $guest = Guest::factory()->create(['notes' => 'Test Guest']);

        $response = $this->postJson(route('api.guest.notes'), [
            'name' => $guest->name,
            'notes' => 'Tum te tum',
        ]);

        $response->assertOk();
        $this->assertEquals('Tum te tum', $guest->fresh()->notes);

        $response = $this->postJson(route('api.server.notes'), [
            'name' => $server->name,
            'notes' => 'La de dah',
        ]);

        $response->assertOk();
        $this->assertEquals('La de dah', $server->fresh()->notes);
    }

    /** @test */
    public function storing_the_same_request_twice_doesnt_duplicate_the_data()
    {
        $this->postJson(route('api.vm.store'), [
            'server' => 'Test Server',
            'guest' => 'Test Guest',
        ]);

        $this->postJson(route('api.vm.store'), [
            'server' => 'Test Server',
            'guest' => 'Test Guest',
        ]);

        $this->assertEquals(1, Server::count());
        $this->assertEquals(1, Guest::count());
    }

    /** @test */
    public function changing_the_vm_guests_server_correctly_updates_the_records()
    {
        $server = Server::factory()->create(['name' => 'Original Server']);
        $guest = Guest::factory()->create(['name' => 'Test Guest', 'server_id' => $server->id]);

        $this->postJson(route('api.vm.store'), [
            'server' => 'New Server',
            'guest' => 'Test Guest',
        ]);

        $this->assertEquals(2, Server::count());
        $this->assertEquals(1, Guest::count());
        $this->assertEquals(0, $server->guests()->count());
        $newServer = Server::where('name', '=', 'New Server')->first();
        $this->assertEquals(1, $newServer->guests()->count());
        $this->assertTrue($newServer->guests()->first()->is($guest));
    }

    /** @test */
    public function we_can_delete_a_vm_server()
    {
        $server = Server::factory()->create(['name' => 'Test Server 1']);
        $otherServer = Server::factory()->create(['name' => 'Test Server 2']);
        $guest = Guest::factory()->create(['server_id' => $server->id]);
        $otherGuest = Guest::factory()->create(['server_id' => $otherServer->id]);

        $this->assertEquals(2, Server::count());
        $this->assertEquals(2, Guest::count());

        $this->postJson(route('api.server.delete'), [
            'name' => 'Test Server 1',
        ]);

        $this->assertEquals(1, Server::count());
        $this->assertEquals(1, Guest::count());
        $this->assertDatabaseHas('servers', ['name' => $otherServer->name]);
        $this->assertDatabaseHas('guests', ['name' => $otherGuest->name]);
    }

    /** @test */
    public function we_can_delete_a_vm_guest()
    {
        $server = Server::factory()->create();
        $otherServer = Server::factory()->create();
        $guest = Guest::factory()->create(['server_id' => $server->id]);
        $otherGuest = Guest::factory()->create(['server_id' => $server->id]);

        $this->assertEquals(2, Server::count());
        $this->assertEquals(2, Guest::count());

        $this->postJson(route('api.guest.delete'), [
            'name' => $guest->name,
        ]);

        $this->assertEquals(2, Server::count());
        $this->assertEquals(1, Guest::count());
        $this->assertEquals(1, $server->guests()->count());
        $this->assertTrue($server->guests()->first()->is($otherGuest));
    }

    /** @test */
    public function we_can_get_a_list_of_servers_and_their_guests()
    {
        $server = Server::factory()->create();
        $otherServer = Server::factory()->create();
        $guest = Guest::factory()->create(['server_id' => $server->id]);
        $otherGuest = Guest::factory()->create(['server_id' => $server->id]);

        $response = $this->getJson(route('api.server.index'));

        $response->assertOk();
        $response->assertJsonCount(2, 'data');
        $response->assertJson([
            'data' => [
                [
                    'name' => $server->name,
                    'guests' => [
                        [
                            'name' => $guest->name,
                        ],
                        [
                            'name' => $otherGuest->name,
                        ],
                    ],
                ],
                [
                    'name' => $otherServer->name,
                    'guests' => [],
                ],
            ],
        ]);
    }
}
