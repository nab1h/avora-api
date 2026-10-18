<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Tests\TestCase;

class PostmanCollectionTest extends TestCase
{
    public function test_every_postman_request_matches_an_implemented_route(): void
    {
        $collection = json_decode(file_get_contents(base_path('docs/postman/product-catalog.postman_collection.json')), true, 512, JSON_THROW_ON_ERROR);
        $count = 0;
        foreach ($collection['item'] as $folder) {
            foreach ($folder['item'] as $item) {
                $definition = $item['request'];
                $url = str_replace('{{base_url}}', 'http://localhost', $definition['url']);
                $url = preg_replace('/\{\{[a-z_]+\}\}/', '1', $url);
                $route = app('router')->getRoutes()->match(Request::create($url, $definition['method']));
                [$controller, $method] = explode('@', $route->getActionName());
                $this->assertTrue(method_exists($controller, $method), $item['name']);
                if ($controller !== AuthController::class
                    && ($definition['method'] !== 'GET' || str_contains($route->uri(), 'admin/'))) {
                    foreach (['auth:sanctum', 'active', 'permission:manage-settings'] as $middleware) {
                        $this->assertContains($middleware, $route->gatherMiddleware(), $item['name']);
                    }
                }
                $this->assertNotEmpty($item['event'][0]['script']['exec'], $item['name']);
                if (isset($definition['body']['raw'])) {
                    $this->assertIsArray(json_decode($definition['body']['raw'], true, 512, JSON_THROW_ON_ERROR));
                }
                $count++;
            }
        }
        $this->assertSame(36, $count);
        $environment = json_decode(file_get_contents(base_path('docs/postman/product-catalog.postman_environment.example.json')), true, 512, JSON_THROW_ON_ERROR);
        foreach ($environment['values'] as $value) {
            if (in_array($value['key'], ['token', 'admin_email', 'admin_password'], true)) {
                $this->assertSame('', $value['value']);
            }
        }
    }
}
