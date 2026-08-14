<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Contracts;

use Illuminate\Http\JsonResponse;

interface FluentHttpAjaxifyInterface
{
    public function is(): bool;

    public function instance(): static;

    public function redirect(string $to, int $status = 302, array $headers = [], ?bool $secure = null): JsonResponse|\Illuminate\Http\RedirectResponse;

    public function redirectBack(int $status = 302, array $headers = []): JsonResponse|\Illuminate\Http\RedirectResponse;

    public function redirectWithErrors(string $url, $provider, int $status = 422): JsonResponse|\Illuminate\Http\RedirectResponse;

    public function redirectRoute(string $name, array $params = [], int $status = 302): JsonResponse|\Illuminate\Http\RedirectResponse;

    public function redirectAction(string $controller, array $params = []): JsonResponse|\Illuminate\Http\RedirectResponse;

    public function view(string $view, array $data = [], array $mergeData = []): JsonResponse|\Illuminate\Contracts\View\View;

    public function redrawView(string $htmlId): static;

    public function appendView(string $htmlId): static;

    public function prependView(string $htmlId): static;

    public function redrawSection(string $name): static;

    public function redrawSections(array $names): static;

    public function flash(string $type, string $message, ?string $title = null): static;

    public function flashFromSession(): static;

    public function success(string $message, ?string $title = null): static;

    public function error(string $message, ?string $title = null): static;

    public function warning(string $message, ?string $title = null): static;

    public function info(string $message, ?string $title = null): static;

    public function alert(string $message): static;

    public function scrollTo(string $htmlId): static;

    public function dump($data = true): static;

    public function runJavascript(string $code): static;

    public function withMeta(array $meta): static;

    public function confirm(string $message): static;

    public function closeModal($value = true): static;

    public function resetForm($value = true): static;

    public function emit(string $event, $data = null): static;

    public function setJson(array $data): static;

    public function mergeJson(array $data): static;

    public function jsonResponse(int $status = 200): JsonResponse;
}
