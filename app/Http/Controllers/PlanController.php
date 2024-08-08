<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    protected string $model = Plan::class;

    public function index()
    {
        $data = Plan::all();
        return response()->json($data);
    }

    public function createSubscription(Plan $plan)
    {
        $front_app_url = config('services.stripe.redirect_url');

        $success_url = $front_app_url . '/success';
        $cancel_url = $front_app_url . '/cancel';

        /**
         * @var User
         */
        $user = auth()->user();

        if (!$user->defaultPaymentMethod()) {
            $data = $user->newSubscription('default', $plan->stripe_price_id)
                ->checkout([
                    'success_url' => $success_url,
                    'cancel_url' => $cancel_url,
                ]);

            return response()->json(['payment_url' => $data->url]);
        }

        $subscription = $user->newSubscription($plan->name, $plan->stripe_price_id)->create()->save();

        return $subscription;
    }

    public function getUserBillingPortal(Request $request)
    {
        /**
         * @var User
         */
        $user = $request->user();

        $user->updateOrCreateStripeCustomer();

        return $request->user()->billingPortalUrl(config('app.frontend_url'));
    }
}
