<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Simtabi\Laranail\FluentHttpAjaxify\Facade\Ajaxify;

/**
 * Controller trait providing shorthand methods for FluentHttpAjaxify responses.
 *
 * Usage:
 *   class UserController extends Controller {
 *       use HasFluentHttpAjaxify;
 *
 *       public function store(Request $request) {
 *           $user = User::create($request->validated());
 *           return $this->ajaxSuccess('User created', ['user' => $user]);
 *       }
 *   }
 */
trait HasFluentHttpAjaxify
{
    /**
     * Return a success response with optional data and flash message.
     */
    protected function ajaxSuccess(string $message = 'Success', array $data = [], int $status = 200): JsonResponse
    {
        $ajax = app(\Simtabi\Laranail\FluentHttpAjaxify\Contracts\FluentHttpAjaxifyInterface::class);

        if (!empty($data)) {
            $ajax->setJson($data);
        }

        $ajax->success($message);

        return $ajax->jsonResponse($status);
    }

    /**
     * Return an error response with flash message.
     */
    protected function ajaxError(string $message = 'Error', int $status = 400, array $errors = []): JsonResponse
    {
        $ajax = app(\Simtabi\Laranail\FluentHttpAjaxify\Contracts\FluentHttpAjaxifyInterface::class);
        $ajax->error($message);
        $ajax->mergeJson(['success' => false, 'message' => $message]);

        if (!empty($errors)) {
            $ajax->mergeJson(['errors' => $errors]);
        }

        return $ajax->jsonResponse($status);
    }

    /**
     * Return a redirect response (dual-mode: JSON for AJAX, redirect for HTTP).
     */
    protected function ajaxRedirect(string $to, string $message = null, int $status = 302): JsonResponse|RedirectResponse
    {
        $ajax = app(\Simtabi\Laranail\FluentHttpAjaxify\Contracts\FluentHttpAjaxifyInterface::class);

        if ($message) {
            $ajax->success($message);
        }

        return $ajax->redirect($to, $status);
    }

    /**
     * Return a validation error response (422).
     */
    protected function ajaxValidationError(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        $ajax = app(\Simtabi\Laranail\FluentHttpAjaxify\Contracts\FluentHttpAjaxifyInterface::class);
        $ajax->error($message);
        $ajax->mergeJson(['success' => false, 'message' => $message, 'errors' => $errors]);

        return $ajax->jsonResponse(422);
    }

    /**
     * Return a response with section updates.
     */
    protected function ajaxSections(array $sections, string $message = null): JsonResponse
    {
        $ajax = app(\Simtabi\Laranail\FluentHttpAjaxify\Contracts\FluentHttpAjaxifyInterface::class);

        if ($message) {
            $ajax->success($message);
        }

        // Pass pre-rendered HTML sections through the service
        foreach ($sections as $selector => $html) {
            $normalizedSelector = '#' . ltrim($selector, '#');
            $ajax->redrawSection($normalizedSelector);
        }

        // Manually set the sections with their HTML content via mergeJson
        $ajax->mergeJson(['sections' => $sections]);

        return $ajax->jsonResponse();
    }
}
