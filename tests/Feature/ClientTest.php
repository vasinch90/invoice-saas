<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $tenant = Tenant::create([
            'id'    => 'test-tenant',
            'name'  => 'Test Tenant',
            'email' => 'test@tenant.com',
        ]);
        $tenant->domains()->create(['domain' => 'test-tenant.localhost']);

        // รัน tenant migrations
        $tenant->run(function () {
            \Illuminate\Support\Facades\Artisan::call('tenants:migrate', [
                '--tenants' => ['test-tenant'],
            ]);
        });

        // สร้าง user ใน tenant context
        $tenant->run(function () {
            $this->user = User::factory()->create();
        });
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    public function test_guest_cannot_access_clients(): void
    {
        $this->get(route('clients.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_view_clients_index(): void
    {
        $this->actingAs($this->user)
            ->get('http://test-tenant.localhost/clients')
            ->assertOk()
            ->assertViewIs('clients.index');
    }

    public function test_user_can_create_client(): void
    {

        $this->actingAs($this->user)
            ->post('http://test-tenant.localhost/clients', [
                'name'  => 'บริษัท ทดสอบ จำกัด',
                'email' => 'test@example.com',
                'phone' => '0812345678',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('clients', [
            'name' => 'บริษัท ทดสอบ จำกัด',
        ]);
    }

    public function test_user_cannot_create_client_without_name(): void
    {
        $this->actingAs($this->user)
            ->post('http://test-tenant.localhost/clients', [
                'email' => 'test@example.com',
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_user_can_update_client(): void
    {
        tenancy()->initialize(Tenant::find('test-tenant'));
        $client = Client::factory()->create(['user_id' => $this->user->id]);
        tenancy()->end();

        $this->actingAs($this->user)
            ->put('http://test-tenant.localhost/clients/' . $client->id, [
                'name'  => 'ชื่อใหม่',
                'email' => 'new@example.com',
            ])
            ->assertRedirect();
    }

    public function test_user_cannot_update_other_users_client(): void
    {
        $otherUser = User::factory()->create();
        tenancy()->initialize(Tenant::find('test-tenant'));
        $client = Client::factory()->create(['user_id' => $otherUser->id]);
        tenancy()->end();

        $this->actingAs($this->user)
            ->put('http://test-tenant.localhost/clients/' . $client->id, ['name' => 'hack'])
            ->assertForbidden();
    }

    public function test_user_can_delete_client(): void
    {
        tenancy()->initialize(Tenant::find('test-tenant'));
        $client = Client::factory()->create(['user_id' => $this->user->id]);
        tenancy()->end();

        $this->actingAs($this->user)
            ->delete('http://test-tenant.localhost/clients/' . $client->id)
            ->assertRedirect();

        tenancy()->initialize(Tenant::find('test-tenant'));
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
        tenancy()->end();
    }

    public function test_user_cannot_delete_other_users_client(): void
    {
        $otherUser = User::factory()->create();
        tenancy()->initialize(Tenant::find('test-tenant'));
        $client = Client::factory()->create(['user_id' => $otherUser->id]);
        tenancy()->end();

        $this->actingAs($this->user)
            ->delete('http://test-tenant.localhost/clients/' . $client->id)
            ->assertForbidden();
    }

    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
