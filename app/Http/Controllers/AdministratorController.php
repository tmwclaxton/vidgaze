<?php

namespace App\Http\Controllers;

use App\Enums\Responses;
use App\Models\CreatorModels\Creator;
use App\Models\ModeratorAction;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AdministratorController extends Controller
{


    public function changeUserRole(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'creator_slug' => 'required|exists:creators,slug',
            'role' => 'required|in:user,moderator',
        ]);
        try {
            $creator = Creator::where('slug', $validated['creator_slug'])->firstOrFail();
            $user = User::findOrFail($creator->user_id);
            $user->role = $validated['role'];
            $user->save();
            return response()->json(['message' => Responses::ROLE_UPDATED]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'errors' => ['general' => [Responses::ROLE_UPDATE_FAILED]]
            ], 500);
        }
    }

    // list moderators
    public function listModerators(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $moderators = User::where('role', 'moderator')->get();
            return response()->json($moderators);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'errors' => ['general' => [Responses::MODERATOR_LIST_FAILED]]
            ], 500);
        }
    }

    // paginate mod actions
    public function paginateModActions(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $modActions = ModeratorAction::paginate(10);
            return response()->json($modActions);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'errors' => ['general' => [Responses::MODERATOR_LIST_FAILED]]
            ], 500);
        }
    }

}
