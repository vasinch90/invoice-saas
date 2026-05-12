<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_guest_cannot_access_clients(): void
    {
        $this->get(route('clients.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_view_clients_index(): void
    {
        $this->actingAs($this->user)
            ->get(route('clients.index'))
            ->assertOk()
            ->assertViewIs('clients.index');
    }

    public function test_user_can_create_client(): void
    {
        $this->actingAs($this->user)
            ->post(route('clients.store'), [
                'name'  => 'บริษัท ทดสอบ จำกัด',
                'email' => 'test@example.com',
                'phone' => '0812345678',
            ])
            ->assertRedirect(route('clients.index'));

        $this->assertDatabaseHas('clients', [
            'name'    => 'บริษัท ทดสอบ จำกัด',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_user_cannot_create_client_without_name(): void
    {
        $this->actingAs($this->user)
            ->post(route('clients.store'), [
                'email' => 'test@example.com',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_user_can_update_client(): void
    {
        $client = Client::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->put(route('clients.update', $client), [
                'name'  => 'ชื่อใหม่',
                'email' => 'new@example.com',
            ])
            ->assertRedirect(route('clients.index'));

        $this->assertDatabaseHas('clients', ['name' => 'ชื่อใหม่']);
    }

    public function test_user_cannot_update_other_users_client(): void
    {
        $otherUser = User::factory()->create();
        $client    = Client::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($this->user)
            ->put(route('clients.update', $client), ['name' => 'hack'])
            ->assertForbidden();
    }

    public function test_user_can_delete_client(): void
    {
        $client = Client::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->delete(route('clients.destroy', $client))
            ->assertRedirect(route('clients.index'));

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    public function test_user_cannot_delete_other_users_client(): void
    {
        $otherUser = User::factory()->create();
        $client    = Client::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($this->user)
            ->delete(route('clients.destroy', $client))
            ->assertForbidden();
    }

    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
