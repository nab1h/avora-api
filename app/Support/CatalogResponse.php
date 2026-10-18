<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogResponse
{
    public static function resource(JsonResource $resource, string $message, int $status = 200): JsonResponse
    {
        return $resource->additional(['success' => true, 'message' => __($message)])
            ->response()->setStatusCode($status);
    }

    public static function deleted(string $message): JsonResponse
    {
        return response()->json(['success' => true, 'message' => __($message), 'data' => null]);
    }
}
