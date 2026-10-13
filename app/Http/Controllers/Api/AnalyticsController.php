<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AnalyticsController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'visitor_id' => ['required', 'uuid'],
            'session_id' => ['required', 'uuid'],
            'event_name' => [
                'required',
                'string',
                Rule::in(['page_view']),
            ],
            'page_path' => [
                'required',
                'string',
                'max:2048',
                'regex:/^\/(?!\/)/',
            ],
            'referrer' => ['nullable', 'url', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid analytics event.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        AnalyticsEvent::create([
            'visitor_id' => $data['visitor_id'],
            'session_id' => $data['session_id'],
            'event_name' => $data['event_name'],
            'page_path' => $data['page_path'],
            'referrer' => $data['referrer'] ?? null,
            'created_at' => now(),
        ]);

        return response()->json([
            'message' => 'Analytics event recorded.',
        ], 201);
    }
}