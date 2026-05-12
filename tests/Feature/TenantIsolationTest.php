<?php

namespace Tests\Feature;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function createTenant(string $id, string $domain): Tenant
    {
        $tenant = Tenant::create([
            'id'    => $id,
            'name'  => $id,
            'email' => $id . '@example.com',
        ]);

        $tenant->domains()->create(['domain' => $domain]);

        return $tenant;
    }

    public function test_tenant_can_be_created_with_domain(): void
    {
        $tenant = $this->createTenant('acme', 'acme.localhost');

        $this->assertDatabaseHas('tenants', ['id' => 'acme']);
        $this->assertDatabaseHas('domains', ['domain' => 'acme.localhost']);
    }

    public function test_two_tenants_can_exist_independently(): void
    {
        $tenant1 = $this->createTenant('company-a', 'company-a.localhost');
        $tenant2 = $this->createTenant('company-b', 'company-b.localhost');

        $this->assertDatabaseHas('tenants', ['id' => 'company-a']);
        $this->assertDatabaseHas('tenants', ['id' => 'company-b']);
        $this->assertDatabaseHas('domains', ['domain' => 'company-a.localhost']);
        $this->assertDatabaseHas('domains', ['domain' => 'company-b.localhost']);
    }

    public function test_tenant_has_unique_domain(): void
    {
        $tenant = $this->createTenant('shop-x', 'shop-x.localhost');

        // domain ซ้ำต้องไม่ได้
        $this->expectException(\Exception::class);
        $tenant->domains()->create(['domain' => 'shop-x.localhost']);
    }

    public function test_domain_belongs_to_correct_tenant(): void
    {
        $tenant1 = $this->createTenant('brand-x', 'brand-x.localhost');
        $tenant2 = $this->createTenant('brand-y', 'brand-y.localhost');

        $domain1 = $tenant1->domains()->first();
        $domain2 = $tenant2->domains()->first();

        $this->assertEquals('brand-x', $domain1->tenant_id);
        $this->assertEquals('brand-y', $domain2->tenant_id);
        $this->assertNotEquals($domain1->tenant_id, $domain2->tenant_id);
    }

    public function test_tenant_cannot_access_another_tenants_domain(): void
    {
        $tenant1 = $this->createTenant('alpha', 'alpha.localhost');
        $tenant2 = $this->createTenant('beta', 'beta.localhost');

        $domain1 = $tenant1->domains()->first();
        $domain2 = $tenant2->domains()->first();

        // domain ของ tenant1 ต้องไม่อยู่ใน tenant2
        $this->assertNotEquals($domain1->tenant_id, $tenant2->id);
        $this->assertNotEquals($domain2->tenant_id, $tenant1->id);
    }

    public function test_deleting_tenant_removes_its_domains(): void
    {
        $tenant = $this->createTenant('temp-co', 'temp-co.localhost');

        $this->assertDatabaseHas('domains', ['tenant_id' => 'temp-co']);

        $tenant->delete();

        $this->assertDatabaseMissing('domains', ['tenant_id' => 'temp-co']);
        $this->assertDatabaseMissing('tenants', ['id' => 'temp-co']);
    }

    public function test_tenant_count_is_correct(): void
    {
        $this->createTenant('t1', 't1.localhost');
        $this->createTenant('t2', 't2.localhost');
        $this->createTenant('t3', 't3.localhost');

        $this->assertEquals(3, Tenant::count());
    }
}