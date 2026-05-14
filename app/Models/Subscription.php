<?php

namespace App\Models;

use Laravel\Cashier\Subscription as CashierSubscription;

class Subscription extends CashierSubscription
{
    protected $keyType = 'string';
    public $incrementing = false;

    public function items()
    {
        return $this->hasMany(SubscriptionItem::class, 'subscription_id', 'id');
    }
}
