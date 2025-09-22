<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UserPreferenceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'sources' => 'nullable|array',
            'sources.*' => 'exists:sources,id',
            'categories' => 'nullable|array',
            'categories.*' => 'string|max:50',
            'authors' => 'nullable|array',
            'authors.*' => 'string|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'sources.*.exists' => 'One or more selected sources do not exist.',
            'categories.*.max' => 'Category names cannot exceed 50 characters.',
            'authors.*.max' => 'Author names cannot exceed 100 characters.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // No custom validation needed for categories as we accept any string values
        });
    }
}
