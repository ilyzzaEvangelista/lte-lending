<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;

class AdminActivityLogController extends Controller
{
    public function index(): JsonResponse
    {
        $rows = ActivityLog::query()
            ->orderByDesc('logged_at')
            ->limit(200)
            ->get();

        return response()->json(['data' => $rows]);
    }
}
