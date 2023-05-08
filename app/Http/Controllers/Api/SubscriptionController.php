<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Subscriptions\CreateSubscriptionRequest;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\PayPalService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends Controller
{
    const DEFAULT_PAGINATION_COUNT = 15;

    public function __construct(private readonly PayPalService $payPalService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(['subscriptions' => Subscription::paginate(self::DEFAULT_PAGINATION_COUNT)]);
    }

    public function show(Request $request, Subscription $subscription): JsonResponse
    {
        return response()->json(['subscription' => $subscription]);
    }

    public function store(CreateSubscriptionRequest $request): JsonResponse
    {
        /* @var User $user */
        $user = Auth::user();
        $plan = Plan::findOrFail($request->input('plan_id'));

        if ($user->isSubscribed()) {
            return response()->json(
                ['message' => __('responses.subscription.already_subscribed')],
                Response::HTTP_BAD_REQUEST
            );
        }

        if ($plan->isInactive()) {
            return response()->json(
                ['message' => __('responses.subscription.plan_not_active')],
                Response::HTTP_BAD_REQUEST);
        }

        $paymentLink = $this->payPalService->makePaymentLink($plan, $user);

        if (!$paymentLink) {
            return response()->json(
                ['message' => __('responses.subscription.payment_unavailable')],
                Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json(['payment_link' => $paymentLink]);
    }

    public function cancel(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isSubscribed()) {
            return response()->json(
                ['message' => __('responses.publications.no_active_subscriptions')],
                Response::HTTP_BAD_REQUEST);
        }

        $subscription = $user->subscription;

        if (!$this->payPalService->cancelSubscription(
            $subscription->paypal_subscription_id,
            $request->input('reason')
        )) {
            return response()->json(['message' => __('responses.something_went_wrong')], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $subscription->delete();

        return response()->json(['message' => __('responses.subscription.canceled')]);
    }

    public function handleSuccessPayment(Request $request): JsonResponse
    {
        $subscriptionId = $request->input('subscription_id');
        $subscriptionData = $this->payPalService->saveSubscription($subscriptionId);

        if (array_key_exists('message', $subscriptionData)) {
            return response()->json(['message' => $subscriptionData['message']]);
        }

        extract($subscriptionData);

        $user = User::where('email', $email)->first();

        $subscription = new Subscription([
            'user_id' => $user->id,
            'plan_id' => $plan_id,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonth(),
            'paypal_subscription_id' => $subscriptionId,
        ]);
        $subscription->save();
        $subscription->refresh();

        return response()->json(['subscription' => $subscription]);
    }
}
