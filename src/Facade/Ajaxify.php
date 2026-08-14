<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Facade;

use Illuminate\Support\Facades\Facade;
use Simtabi\Laranail\FluentHttpAjaxify\Contracts\FluentHttpAjaxifyInterface;

/**
 * @method static bool is()
 * @method static static instance()
 * @method static \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse redirect(string $to, int $status = 302, array $headers = [], ?bool $secure = null)
 * @method static \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse redirectBack(int $status = 302, array $headers = [])
 * @method static \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse redirectWithErrors(string $url, $provider, int $status = 422)
 * @method static \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse redirectRoute(string $name, array $params = [], int $status = 302)
 * @method static \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse redirectAction(string $controller, array $params = [])
 * @method static \Illuminate\Http\JsonResponse|\Illuminate\Contracts\View\View view(string $view, array $data = [], array $mergeData = [])
 * @method static static redrawView(string $htmlId)
 * @method static static appendView(string $htmlId)
 * @method static static prependView(string $htmlId)
 * @method static static redrawSection(string $name)
 * @method static static redrawSections(array $names)
 * @method static static flash(string $type, string $message, ?string $title = null)
 * @method static static flashFromSession()
 * @method static static success(string $message, ?string $title = null)
 * @method static static error(string $message, ?string $title = null)
 * @method static static warning(string $message, ?string $title = null)
 * @method static static info(string $message, ?string $title = null)
 * @method static static alert(string $message)
 * @method static static scrollTo(string $htmlId)
 * @method static static dump($data = true)
 * @method static static runJavascript(string $code)
 * @method static static withMeta(array $meta)
 * @method static static confirm(string $message)
 * @method static static closeModal($value = true)
 * @method static static resetForm($value = true)
 * @method static static emit(string $event, $data = null)
 * @method static static setJson(array $data)
 * @method static static mergeJson(array $data)
 * @method static \Illuminate\Http\JsonResponse jsonResponse(int $status = 200)
 *
 * @see \Simtabi\Laranail\FluentHttpAjaxify\FluentHttpAjaxify
 */
class Ajaxify extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FluentHttpAjaxifyInterface::class;
    }
}
