<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Tests\Unit;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Simtabi\Laranail\FluentHttpAjaxify\FluentHttpAjaxify;
use Simtabi\Laranail\FluentHttpAjaxify\Tests\TestCase;

class FluentHttpAjaxifyTest extends TestCase
{
    protected FluentHttpAjaxify $ajax;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ajax = new FluentHttpAjaxify(Request::create('/test', 'GET', [], [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]));
    }

    public function test_is_detects_ajax_request(): void
    {
        $this->assertTrue($this->ajax->is());
    }

    public function test_is_returns_false_for_normal_request(): void
    {
        $ajax = new FluentHttpAjaxify(Request::create('/test', 'GET'));
        $this->assertFalse($ajax->is());
    }

    public function test_redirect_returns_json_for_ajax(): void
    {
        $response = $this->ajax->redirect('/dashboard');
        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals('/dashboard', $data['redirect']);
    }

    public function test_redirect_returns_redirect_for_non_ajax(): void
    {
        $ajax = new FluentHttpAjaxify(Request::create('/test', 'GET'));
        $response = $ajax->redirect('/dashboard');
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class, $response);
    }

    public function test_flash_adds_toast_messages(): void
    {
        $response = $this->ajax
            ->success('Saved!')
            ->error('Oops')
            ->jsonResponse();

        $data = $response->getData(true);
        $this->assertCount(2, $data['flash']);
        $this->assertEquals('success', $data['flash'][0]['type']);
        $this->assertEquals('Saved!', $data['flash'][0]['message']);
        $this->assertEquals('error', $data['flash'][1]['type']);
    }

    public function test_flash_with_title(): void
    {
        $response = $this->ajax
            ->flash('info', 'Details here', 'Note')
            ->jsonResponse();

        $data = $response->getData(true);
        $this->assertEquals('Note', $data['flash'][0]['title']);
    }

    public function test_scroll_to(): void
    {
        $response = $this->ajax->scrollTo('user-list')->jsonResponse();
        $data = $response->getData(true);
        $this->assertEquals('#user-list', $data['scrollTo']);
    }

    public function test_dump(): void
    {
        $response = $this->ajax->dump(['key' => 'value'])->jsonResponse();
        $data = $response->getData(true);
        $this->assertEquals(['key' => 'value'], $data['dump']);
    }

    public function test_run_javascript_blocked_by_default(): void
    {
        config()->set('laranail.fluent-http-ajaxify.allow_js_eval', false);
        $response = $this->ajax->runJavascript('alert(1)')->jsonResponse();
        $data = $response->getData(true);
        $this->assertArrayNotHasKey('runJavascript', $data);
    }

    public function test_run_javascript_allowed_when_configured(): void
    {
        config()->set('laranail.fluent-http-ajaxify.allow_js_eval', true);
        $response = $this->ajax->runJavascript('console.log("ok")')->jsonResponse();
        $data = $response->getData(true);
        $this->assertEquals('console.log("ok")', $data['runJavascript']);
    }

    public function test_set_json_and_merge_json(): void
    {
        $response = $this->ajax
            ->setJson(['name' => 'John'])
            ->mergeJson(['age' => 30])
            ->jsonResponse();

        $data = $response->getData(true);
        $this->assertEquals('John', $data['data']['name']);
        $this->assertEquals(30, $data['data']['age']);
    }

    public function test_json_response_sets_success_based_on_status(): void
    {
        $success = $this->ajax->jsonResponse(200);
        $this->assertTrue($success->getData(true)['success']);

        $ajax2 = new FluentHttpAjaxify(Request::create('/test', 'GET', [], [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]));
        $error = $ajax2->jsonResponse(500);
        $this->assertFalse($error->getData(true)['success']);
    }

    public function test_redirect_with_errors(): void
    {
        $response = $this->ajax->redirectWithErrors('/form', [
            'email' => ['Email is required'],
            'name' => ['Name is too short'],
        ]);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertArrayHasKey('email', $data['errors']);
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_redraw_section(): void
    {
        $response = $this->ajax
            ->redrawSection('user-list')
            ->jsonResponse();

        $data = $response->getData(true);
        $this->assertArrayHasKey('sections', $data);
        $this->assertArrayHasKey('#user-list', $data['sections']);
    }

    public function test_state_resets_after_json_response(): void
    {
        $this->ajax->success('First')->jsonResponse();

        // Second call should not contain first call's flash
        $response = $this->ajax->info('Second')->jsonResponse();
        $data = $response->getData(true);
        $this->assertCount(1, $data['flash']);
        $this->assertEquals('Second', $data['flash'][0]['message']);
    }
}
