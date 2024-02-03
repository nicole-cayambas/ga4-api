<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class GoogleAnalyticsQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [];
    }

    public function requestRules(): array
    {
        return [
            // 'startDate' => 'date'
        ];
    }

    public function validateRequest()
    {
        $validator = Validator::make($this->all(), $this->requestRules());
        if($validator->fails()) {
            return [
                'success' => false,
                'error' => response()->json([
                    'code' => Response::HTTP_BAD_REQUEST,
                    'errors' => $validator->errors()
                ])
            ];
        }
        return [ 'success' => true ];
    }
}
