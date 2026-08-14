<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Tests\Unit;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Simtabi\Laranail\FluentHttpAjaxify\Http\Middleware\FluentHttpAjaxifyMiddleware;
use Simtabi\Laranail\FluentHttpAjaxify\Tests\TestCase;

class MiddlewareTest extends TestCase
{
    protected FluentHttpAjaxifyMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new FluentHttpAjaxifyMiddleware();
    }

    protected function createAjaxRequest(string $uri = '/test'): Request
    {
        return Request::create($uri, 'GET', [], [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);
    }

    public function test_redirect_conversion_includes_message_field(): void
    {
        $request = $this->createAjaxRequest();
        $redirect = new RedirectResponse('/dashboard');

        $response = $this->middleware->handle($request, function () use ($redirect) {
            return $redirect;
        });

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Redirecting', $data['message']);
    }

    public function test_redirect_conversion_includes_success_field(): void
    {
        $request = $this->createAjaxRequest();
        $redirect = new RedirectResponse('/dashboard', 302);

        $response = $this->middleware->handle($request, function () use ($redirect) {
            return $redirect;
        });

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('/dashboard', $data['redirect']);
    }

    public function test_dangerous_redirect_url_is_rejected(): void
    {
        $request = $this->createAjaxRequest();
        $redirect = new RedirectResponse('javascript:alert(1)');

        $response = $this->middleware->handle($request, function () use ($redirect) {
            return $redirect;
        });

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('/', $data['redirect']);
    }

    public function test_data_url_scheme_is_rejected(): void
    {
        $request = $this->createAjaxRequest();
        $redirect = new RedirectResponse('data:text/html,<script>alert(1)</script>');

        $response = $this->middleware->handle($request, function () use ($redirect) {
            return $redirect;
        });

        $data = json_decode($response->getContent(), true);
        $this->assertEquals('/', $data['redirect']);
    }

    public function test_non_ajax_request_passes_through(): void
    {
        $request = Request::create('/test', 'GET');
        $redirect = new RedirectResponse('/dashboard');

        $response = $this->middleware->handle($request, function () use ($redirect) {
            return $redirect;
        });

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }
}
