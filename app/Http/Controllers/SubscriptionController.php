<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Cashier\Exceptions\IncompletePayment;

class SubscriptionController extends Controller
{
    public function plans()
    {
        $tenant = tenant();
        return view('subscription.plans', [
            'isSubscribed' => $tenant->subscribed('default'),
            'onTrial'      => $tenant->onTrial(),
            'plan'         => $tenant->subscription('default')?->stripe_price,
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'price_id' => 'required|string',
        ]);

        $tenant = tenant();

        return $tenant->newSubscription('default', $request->price_id)
            ->trialDays(14)
            ->checkout([
                'success_url' => route('subscription.success'),
                'cancel_url'  => route('subscription.plans'),
            ]);
    }

    public function success()
    {
        return view('subscription.success');
    }

    public function cancel(Request $request)
    {
        $tenant = tenant();
        $tenant->subscription('default')->cancel();

        return redirect()->route('subscription.plans')
            ->with('success', 'ยกเลิก subscription เรียบร้อยแล้ว');
    }

    public function billing(Request $request)
    {
        $tenant = tenant();
        return $tenant->redirectToBillingPortal(route('subscription.plans'));
    }
}
