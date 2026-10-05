<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Tests\Unit;

use ReflectionClass;
use Illuminate\Http\Request;
use Simtabi\Laranail\FluentHttpAjaxify\Tests\TestCase;
use Simtabi\Laranail\FluentHttpAjaxify\FluentHttpAjaxify;

class ResponseProtocolTest extends TestCase
{
    public function test_uniform_sections_use_flat_format_with_top_level_draw_mode(): void
    {
        $ajax = $this->createAjax();
        $ajax->appendView('list');

        // Manually set sections to simulate view rendering
        $reflection = new ReflectionClass($ajax);
        $sectionsProperty = $reflection->getProperty('sections');
        $sectionsProperty->setAccessible(true);
        $sectionsProperty->setValue($ajax, [
            '#list'    => ['html' => '<li>Item 1</li>', 'mode' => 'append'],
            '#sidebar' => ['html' => '<div>Sidebar</div>', 'mode' => 'append'],
        ]);

        $response = $ajax->jsonResponse();
        $data = $response->getData(true);

        // Uniform mode → flat HTML strings
        $this->assertIsString($data['sections']['#list']);
        $this->assertIsString($data['sections']['#sidebar']);
        $this->assertEquals('append', $data['drawMode']);
    }

    public function test_mixed_sections_use_per_section_draw_mode(): void
    {
        $ajax = $this->createAjax();

        $reflection = new ReflectionClass($ajax);
        $sectionsProperty = $reflection->getProperty('sections');
        $sectionsProperty->setAccessible(true);
        $sectionsProperty->setValue($ajax, [
            '#list'    => ['html' => '<li>Item 1</li>', 'mode' => 'redraw'],
            '#sidebar' => ['html' => '<div>Sidebar</div>', 'mode' => 'append'],
        ]);

        $response = $ajax->jsonResponse();
        $data = $response->getData(true);

        // Mixed modes → per-section {html, mode} objects
        $this->assertIsArray($data['sections']['#list']);
        $this->assertEquals('redraw', $data['sections']['#list']['mode']);
        $this->assertEquals('<li>Item 1</li>', $data['sections']['#list']['html']);

        $this->assertIsArray($data['sections']['#sidebar']);
        $this->assertEquals('append', $data['sections']['#sidebar']['mode']);

        // No top-level drawMode for mixed
        $this->assertArrayNotHasKey('drawMode', $data);
    }

    public function test_redraw_sections_omit_top_level_draw_mode(): void
    {
        $ajax = $this->createAjax();

        $reflection = new ReflectionClass($ajax);
        $sectionsProperty = $reflection->getProperty('sections');
        $sectionsProperty->setAccessible(true);
        $sectionsProperty->setValue($ajax, [
            '#list' => ['html' => '<ul>List</ul>', 'mode' => 'redraw'],
        ]);

        $response = $ajax->jsonResponse();
        $data = $response->getData(true);

        // Default 'redraw' is omitted from top-level
        $this->assertArrayNotHasKey('drawMode', $data);
        $this->assertIsString($data['sections']['#list']);
    }

    public function test_flash_deduplication_with_auto_flash(): void
    {
        // Create a request with a session
        $request = Request::create('/test', 'GET', [], [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);
        $request->setLaravelSession(app('session.store'));
        $request->session()->flash('success', 'Saved!');

        $ajax = new FluentHttpAjaxify($request);

        // Add an explicit flash with same message
        $ajax->success('Saved!');

        config()->set('laranail.fluent-http-ajaxify.auto_flash', true);
        config()->set('laranail.fluent-http-ajaxify.flash_keys', ['success' => 'success']);

        $response = $ajax->jsonResponse();
        $data = $response->getData(true);

        // Should be deduplicated — only one flash entry
        $successFlashes = array_filter($data['flash'], function ($f) {
            return $f['type'] === 'success' && $f['message'] === 'Saved!';
        });
        $this->assertCount(1, $successFlashes);
    }

    public function test_collect_flashes_from_request_static_method(): void
    {
        $request = Request::create('/test', 'GET');
        $request->setLaravelSession(app('session.store'));
        $request->session()->flash('success', 'Done!');

        config()->set('laranail.fluent-http-ajaxify.flash_keys', ['success' => 'success']);

        $flashes = FluentHttpAjaxify::collectFlashesFromRequest($request);

        $this->assertCount(1, $flashes);
        $this->assertEquals('success', $flashes[0]['type']);
        $this->assertEquals('Done!', $flashes[0]['message']);
    }

    protected function createAjax(): FluentHttpAjaxify
    {
        return new FluentHttpAjaxify(Request::create('/test', 'GET', [], [], [], [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]));
    }
}
