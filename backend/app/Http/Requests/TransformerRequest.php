<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Str;
use Symfony\Component\HttpFoundation\Response;

class TransformerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'target'  => 'required|file|mimes:pdf|max:10240', // 10MB
                'pages' => 'required|string', // 1,12,123 Only digits and commas
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        $this->validateRequest($validator);
    }
    public function validateRequest(Validator $validator): ?array
    {
        $errors = [];
        $fails  = (new ValidationException($validator))->errors();
        foreach ($fails as $key => $error) {
            $errors[$key] = $error[0];
        }

        if (count($errors) > 0) {
            $logName = explode('\\', get_class($this));

            // log the errors for debug
            Log::error(Str::snake(end($logName)), $errors);

            // sends back an error message
            throw new HttpResponseException(
                response([
                    'status' => false,
                    'message' => 'validation failed',
                    'errors' => $errors
                ], Response::HTTP_BAD_REQUEST)
            );
        }
    }
}
