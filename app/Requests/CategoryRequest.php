<?php

namespace App\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Organization\Entities\Organization;
use Modules\Participant\Entities\Participant;
use Modules\Referral\Entities\Referral;

class CategoryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|min:2|max:256',
        ];
    }

    protected function prepareForValidation()
    {
    }

    public function messages()
    {
        return [
            'name.name' => 'Category name is required.',
            'name.min' => 'Category name length should be at least 2 characters.',
            'name.max' => 'Category name max length should not exceed 256 character.'
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
