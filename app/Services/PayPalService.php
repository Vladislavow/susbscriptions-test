<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Models\Plan;

class PayPalService
{
    private PayPalClient $payPalClient;

    public function __construct()
    {
        $this->payPalClient = new PayPalClient;

        $this->payPalClient = \PayPal::setProvider();
    }

    public function makePaymentLink(Plan $plan, User $user)
    {
        try {
            $this->payPalClient->getAccessToken();

            $response = $this->payPalClient
                ->addProduct('Publishing', $plan->max_publications . " publications", 'SERVICE', 'SOFTWARE')
                ->addMonthlyPlan($plan->id, $plan->name, $plan->price)
                ->setReturnAndCancelUrl(
                    route('subscriptions.success'),
                    route('subscriptions.canceled')
                )
                ->setupSubscription($user->name, $user->email, now()->addDay());

            return $response['links'][0]['href'];
        } catch (\Exception) {
            return null;
        }
    }

    public function saveSubscription($subscriptionId): array
    {
        try {
            $this->payPalClient->getAccessToken();

            $subscriptionDetails = $this->payPalClient->showSubscriptionDetails($subscriptionId);

            if ($subscriptionDetails['status'] != 'ACTIVE') {
                return [
                    'message' => 'Subscription inactive',
                ];
            }

            return [
                'email' => $subscriptionDetails['subscriber']['email_address'],
                'plan_id' => $this->payPalClient->showPlanDetails($subscriptionDetails['plan_id'])['name'],
            ];
        } catch (\Throwable $e) {
            return [
                'message' => $e->getMessage(),
            ];
        }
    }

    public function cancelSubscription($paypalSubscriptionId, $reason): bool
    {
        try {
            $this->payPalClient->cancelSubscription($paypalSubscriptionId, $reason || 'Default reason');
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
