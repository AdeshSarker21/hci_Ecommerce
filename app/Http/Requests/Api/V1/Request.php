<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

abstract class Request extends FormRequest
{
    abstract public function rules(): array;

    public function authorize(): bool
    {
        return true;
    }
}
