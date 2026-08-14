<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * Custom 422 validation exception that formats errors in the FluentHttpAjaxify protocol format.
 * Includes flash messages for automatic toast display on the client.
 */
class FluentHttpAjaxifyValidationException extends ValidationException
{
    /**
     * Render the exception as a JSON response.
     */
    public function render($request): JsonResponse
    {
        $errors = $this->validator->errors()->toArray();
        $firstMessage = $this->validator->errors()->first();

        return response()->json([
            'success' => false,
            'message' => $this->message ?: ($firstMessage ?: 'Validation failed'),
            'errors'  => $errors,
            'flash'   => [
                [
                    'type'    => 'error',
                    'message' => $firstMessage ?: 'Please fix the errors below.',
                ],
            ],
        ], $this->status);
    }
}
