<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private User   $user;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $tenant = Tenant::create([
            'id'    => 'test-tenant',
            'name'  => 'Test Tenant',
            'email' => 'test@tenant.com',
        ]);
        $tenant->domains()->create(['domain' => 'test-tenant.localhost']);

        $tenant->run(function () {
            \Illuminate\Support\Facades\Artisan::call('tenants:migrate', [
                '--tenants' => ['test-tenant'],
            ]);
        });

        $tenant->run(function () {
            $this->user   = User::factory()->create();
            $this->client = Client::factory()->create(['user_id' => $this->user->id]);
        });

    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    public function test_guest_cannot_access_invoices(): void
    {
        $this->get('http://test-tenant.localhost/invoices')
            ->assertRedirect(route('login'));
    }

    public function test_user_can_view_invoices_index(): void
    {
        $this->actingAs($this->user)
            ->get('http://test-tenant.localhost/invoices')
            ->assertOk()
            ->assertViewIs('invoices.index');
    }

    public function test_user_can_create_invoice(): void
    {
        $this->actingAs($this->user)
            ->post('http://test-tenant.localhost/invoices', [
                'client_id'  => $this->client->id,
                'issue_date' => '2025-01-01',
                'due_date'   => '2025-01-31',
                'tax_rate'   => 7,
                'items'      => [
                    [
                        'description' => 'Web Development',
                        'quantity'    => 1,
                        'unit_price'  => 10000,
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('invoices', [
            'client_id' => $this->client->id,
            'user_id'   => $this->user->id,
            'subtotal'  => 10000,
            'tax_amount'=> 700,
            'total'     => 10700,
        ]);
    }

    public function test_invoice_number_is_auto_generated(): void
    {
        $this->actingAs($this->user)
            ->post('http://test-tenant.localhost/invoices', [
                'client_id'  => $this->client->id,
                'issue_date' => '2025-01-01',
                'due_date'   => '2025-01-31',
                'items'      => [
                    [
                        'description' => 'Design',
                        'quantity'    => 1,
                        'unit_price'  => 5000,
                    ],
                ],
            ]);

        $invoice = Invoice::first();
        $this->assertStringStartsWith('INV-', $invoice->invoice_number);
    }

    public function test_user_cannot_create_invoice_without_items(): void
    {
        $this->actingAs($this->user)
            ->post('http://test-tenant.localhost/invoices', [
                'client_id'  => $this->client->id,
                'issue_date' => '2025-01-01',
                'due_date'   => '2025-01-31',
                'items'      => [],
            ])
            ->assertSessionHasErrors('items');
    }

    public function test_user_can_delete_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'user_id'   => $this->user->id,
            'client_id' => $this->client->id,
        ]);

        $this->actingAs($this->user)
            ->delete('http://test-tenant.localhost/invoices/' . $invoice->id)
            ->assertRedirect('http://test-tenant.localhost/invoices/');

        $this->assertDatabaseMissing('invoices', ['id' => $invoice->id]);
    }

    public function test_user_cannot_view_other_users_invoice(): void
    {
        $otherUser    = User::factory()->create();
        $otherClient  = Client::factory()->create(['user_id' => $otherUser->id]);
        $invoice      = Invoice::factory()->create([
            'user_id'   => $otherUser->id,
            'client_id' => $otherClient->id,
        ]);

        $this->actingAs($this->user)
            ->get('http://test-tenant.localhost/invoices/' . $invoice->id)
            ->assertForbidden();
    }
}
