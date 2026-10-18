<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/** Standardize only the new catalog endpoints, including binding/auth errors. */
class CatalogResponses
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is(
            'api/categories', 'api/categories/*', 'api/products', 'api/products/*',
            'api/admin/categories', 'api/admin/categories/*',
            'api/admin/products', 'api/admin/products/*',
            'api/admin/galleries', 'api/admin/galleries/*'
        )) {
            return $next($request);
        }

        // These routes always return JSON, even without an Accept header.
        $request->headers->set('Accept', 'application/json');

        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            $handler = app(ExceptionHandler::class);
            $handler->report($exception);
            $response = $handler->render($request, $exception);
        }

        if ($response->getStatusCode() < 400) {
            return $response;
        }

        $body = json_decode($response->getContent(), true) ?? [];
        $status = $response->getStatusCode();
        $message = match (true) {
            $status >= 500 => __('catalog.server_error'),
            $status === 404 => __('catalog.not_found'),
            default => $body['message'] ?? Response::$statusTexts[$status] ?? __('catalog.request_failed'),
        };

        $response->setContent(json_encode([
            'success' => false,
            'message' => $message,
            'errors' => $status < 500 ? ($body['errors'] ?? null) : null,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
