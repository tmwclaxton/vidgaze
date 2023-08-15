<?php

namespace App\Http\Controllers\ApiControllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class CronApiController extends Controller
{

    /** Prun telescope entries
     * @param Request $request
     * @return JsonResponse
     */
    public function telescope(Request $request): JsonResponse
    {
        // queue the command
        Artisan::queue('telescope:prune --hours=48')->onQueue('commands');
        return response()->json([
            'message' => 'telescope pruned'
        ], 200);
    }

    /** Prune sanctum tokens
     * @param Request $request
     * @return JsonResponse
     */
    public function sanctumTokens(Request $request): JsonResponse
    {
        Artisan::queue('sanctum:prune-expired --hours=48')->onQueue('commands');
        return response()->json([
            'message' => 'sanctum tokens pruned'
        ], 200);
    }


    /** Refresh one twitch category
     * @param Request $request
     * @return JsonResponse
     */
    public function refreshOneTwitchCategory(Request $request): JsonResponse
    {
        Artisan::queue('refresh:one_twitch_category')->onQueue('commands');
        return response()->json([
            'message' => 'one twitch category refreshed'
        ], 200);
    }


    /** Refresh twitch category info
     * @param Request $request
     * @return JsonResponse
     */
    public function refreshTwitchCategoryInfo(Request $request): JsonResponse
    {
        Artisan::queue('refresh:twitch-category-info')->onQueue('commands');
        return response()->json([
            'message' => 'twitch category info refreshed'
        ], 200);
    }


    /** Refresh streams
     * @param Request $request
     * @return JsonResponse
     */
    public function refreshStreams(Request $request): JsonResponse
    {
        Artisan::queue('refresh:streams')->onQueue('commands');
        return response()->json([
            'message' => 'streams refreshed'
        ], 200);
    }


    /** Delete old live viewers
     * @param Request $request
     * @return JsonResponse
     */
    public function liveViewersPrune(Request $request): JsonResponse
    {
        Artisan::queue('delete:old_live_viewers')->onQueue('commands');
        return response()->json([
            'message' => 'old live viewers deleted'
        ], 200);
    }


    /** Refresh subscriptions
     * @param Request $request
     * @return JsonResponse
     */
    public function refreshSubscriptions(Request $request): JsonResponse
    {
        Artisan::queue('refresh:subscriptions')->onQueue('commands');
        return response()->json([
            'message' => 'subscriptions refreshed'
        ], 200);
    }


    /** Backup logs
     * @param Request $request
     * @return JsonResponse
     */
    public function backupLogs(Request $request): JsonResponse
    {
        Artisan::queue('backup:logs')->onQueue('commands');

        return response()->json([
            'message' => 'logs backed up'
        ], 200);
    }

    /** Prune batches
     * @param Request $request
     * @return JsonResponse
     */
    public function pruneBatches(Request $request): JsonResponse
    {
        Artisan::queue('queue:prune-batches')->onQueue('commands');

        return response()->json([
            'message' => 'batch entries pruned'
        ], 200);
    }

}
