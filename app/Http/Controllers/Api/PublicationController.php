<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Publication\UpdatePublicationRequest;
use App\Models\Publication;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PublicationController extends Controller
{
    const DEFAULT_PAGINATION_COUNT = 15;

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'publications' => Publication::where('status', Publication::ACTIVE_STATUS)
                ->orderBy('created_at')
                ->with('publisher')
                ->paginate(self::DEFAULT_PAGINATION_COUNT),
        ]);
    }

    public function show(Request $request, Publication $publication): JsonResponse
    {
        if ($publication->status != Publication::ACTIVE_STATUS && $publication->user_id != $request->user()->id) {
            return response()->json(['message' => __('responses.not_found')], 404);
        }
        
        return response()->json(['publication' => $publication]);
    }

    public function store(UpdatePublicationRequest $request): JsonResponse
    {
        $validatedFields = $request->validated();
        echo $request->input('max_publications');
        $publication = Publication::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'user_id' => $request->user()->id,
            'status' => Publication::DRAFT_STATUS,
        ]);

        $publication->save();
        $publication->refresh();

        return response()->json(['publication' => $publication]);
    }

    public function publish(Request $request, Publication $publication): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isSubscribed()) {
            return response()->json(
                ['message' => __('responses.publications.no_active_subscriptions')],
                Response::HTTP_BAD_REQUEST
            );
        }


        if ($user->getRemainingPublicationsCount() <= 0) {
            return response()->json(
                ['message' => __('responses.publications.no_publishes_remaining')],
                Response::HTTP_BAD_REQUEST
            );
        }

        $publication->status = Publication::ACTIVE_STATUS;
        $publication->save();

        return response()->json([
            'publication' => $publication,
            'remaining_publications_count' => $user->getRemainingPublicationsCount(),
        ]);
    }

    public function archive(Request $request, Publication $publication): JsonResponse
    {
        $publication->status = Publication::ARCHIVED_STATUS;
        $publication->save();

        return response()->json([
            'publication' => $publication,
            'remaining_publications_count' => $request->user()
                ->getRemainingPublicationsCount(),
        ]);
    }

    public function update(UpdatePublicationRequest $request, Publication $publication): JsonResponse
    {
        $publication->update([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'status' => $request->input('status', Publication::DRAFT_STATUS)
        ]);
        $publication->save();

        return response()->json(['publication' => $publication]);
    }

    public function destroy(Publication $publication): JsonResponse
    {
        $publication->delete();

        return response()->json(['message' => __('responses.publications.deleted')]);
    }

    public function gerOwnPublications(Request $request): JsonResponse
    {
        return response()->json(['publications' => Publication::where('user_id', $request->user()->id)->get()]);
    }
}
