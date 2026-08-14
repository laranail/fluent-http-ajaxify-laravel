<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Tests\Unit;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Simtabi\Laranail\FluentHttpAjaxify\FluentHttpAjaxify;
use Simtabi\Laranail\FluentHttpAjaxify\Tests\TestCase;

class SecurityTest extends TestCase
{
    protected FluentHttpAjaxify $ajax;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ajax = new FluentHttpAjaxify(Request::create('/test', 'GET', [], [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]));
    }

    public function test_flash_message_is_html_escaped(): void
    {
        $response = $this->ajax
            ->success('<script>alert("xss")</script>')
            ->jsonResponse();

        $data = $response->getData(true);
        $this->assertStringNotContainsString('<script>', $data['flash'][0]['message']);
        $this->assertStringContainsString('&lt;script&gt;', $data['flash'][0]['message']);
    }

    public function test_flash_title_is_html_escaped(): void
    {
        $response = $this->ajax
            ->flash('info', 'msg', '<img onerror=alert(1)>')
            ->jsonResponse();

        $data = $response->getData(true);
        $this->assertStringNotContainsString('<img', $data['flash'][0]['title']);
        $this->assertStringContainsString('&lt;img', $data['flash'][0]['title']);
    }

    public function test_alert_message_is_html_escaped(): void
    {
        $response = $this->ajax
            ->alert('<script>alert("xss")</script>')
            ->jsonResponse();

        $data = $response->getData(true);
        $this->assertStringNotContainsString('<script>', $data['alert']);
        $this->assertStringContainsString('&lt;script&gt;', $data['alert']);
    }

    public function test_emit_validates_event_name(): void
    {
        // Valid event name
        $this->ajax->emit('user.created', ['id' => 1]);
        $response = $this->ajax->jsonResponse();
        $data = $response->getData(true);
        $this->assertArrayHasKey('user.created', $data['emit']);

        // Invalid event name — silently rejected
        $ajax2 = new FluentHttpAjaxify(Request::create('/test', 'GET', [], [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]));
        $ajax2->emit('<script>alert(1)</script>', 'data');
        $response2 = $ajax2->jsonResponse();
        $data2 = $response2->getData(true);
        $this->assertArrayNotHasKey('emit', $data2);
    }

    public function test_emit_allows_valid_patterns(): void
    {
        $validNames = ['user.created', 'form:submit', 'item-updated', 'step_1:done', 'MyEvent'];
        foreach ($validNames as $name) {
            $ajax = new FluentHttpAjaxify(Request::create('/test', 'GET', [], [], [], [
                'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            ]));
            $ajax->emit($name, null);
            $response = $ajax->jsonResponse();
            $data = $response->getData(true);
            $this->assertArrayHasKey($name, $data['emit'], "Event name '$name' should be valid");
        }
    }

    public function test_json_response_has_security_headers(): void
    {
        $response = $this->ajax->jsonResponse();
        $this->assertEquals('nosniff', $response->headers->get('X-Content-Type-Options'));

        // The directive, not the exact header string. Symfony normalises the
        // bag and adds `private` alongside `no-store` when nothing marks the
        // response public, so an equality assertion pinned this to one
        // framework version while testing nothing extra: `private` is a
        // stricter answer, not a different one.
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
    }
}
