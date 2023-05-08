<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Plan\CreatePlanRequest;
use App\Http\Requests\Api\Plan\UpdatePlanRequest;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\PayPalService;
use Symfony\Component\HttpFoundation\Response;

class PlanController extends Controller
{

    public function index(): JsonResponse
    {
        $plans = Plan::all();

        return response()->json(['plans' => $plans]);
    }

    public function available(): JsonResponse
    {
        $plans = Plan::where('active', true)->get();

        return response()->json(['plans' => $plans]);
    }

    public function store(CreatePlanRequest $request): JsonResponse
    {
        $plan = Plan::create([
            'name' => $request->input('name'),
            'price' => $request->input('price'),
            'max_publications' => $request->input('max_publications'),
            'active' => $request->input('is_active') ?? false,
        ]);

        return response()->json(['data' => $plan], 201);
    }

    public function show(Plan $plan): JsonResponse
    {
        return response()->json(['data' => $plan]);
    }

    public function update(UpdatePlanRequest $request, Plan $plan): JsonResponse
    {
        $plan->update([
            'name' => $request->input('name') ?? $plan->name,
            'price' => $request->input('price') ?? $plan->price,
            'max_publications' => $request->input('max_publications') ?? $plan->max_publications,
            'active' => $request->input('active') ?? $plan->active,
        ]);
        $plan->save();

        return response()->json(['data' => $plan]);
    }

    public function destroy(Plan $plan): JsonResponse
    {
        $plan->delete();

        return response()->json(__('responses.plan.deleted'));
    }
}
