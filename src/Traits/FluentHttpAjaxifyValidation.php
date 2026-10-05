<?php

namespace Simtabi\Laranail\FluentHttpAjaxify\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Enhanced 422 validation formatting for FluentHttpAjaxify protocol.
 *
 * Use in Form Requests:
 *   class StoreUserRequest extends FormRequest {
 *       use FluentHttpAjaxifyValidation;
 *   }
 *
 * This overrides the default failedValidation to return the protocol-compatible
 * JSON format with flash messages and structured errors.
 */
trait FluentHttpAjaxifyValidation
{
    /**
     * Handle a failed validation attempt.
     * Returns protocol-compatible JSON for AJAX requests.
     */
    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson() || $this->ajax()) {
            $errors = $validator->errors()->toArray();
            $firstMessage = $validator->errors()->first();

            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => $firstMessage ?: 'Validation failed',
                    'errors'  => $errors,
                    'flash'   => [
                        [
                            'type'    => 'error',
                            'message' => $firstMessage ?: 'Please fix the errors below.',
                        ],
                    ],
                ], 422),
            );
        }

        parent::failedValidation($validator);
    }
}
