<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Laravel\Cashier\Billable;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains, Billable;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    public static function getCustomColumns(): array
    {
        return ['id', 'name', 'email', 'stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at'];
    }

    public function subscriptions()
    {
        return $this->hasMany(\Laravel\Cashier\Subscription::class, 'user_id', 'id');
    }

    public function subscription($type = 'default')
    {
        return $this->subscriptions->where('type', $type)->first();
    }
}