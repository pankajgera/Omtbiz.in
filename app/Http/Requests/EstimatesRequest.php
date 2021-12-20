<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EstimatesRequest extends FormRequest
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
        $rules = [
            'estimate_date' => 'required',
            'expiry_date' => 'nullable',
            'user_id' => 'required',
            'discount' => 'required',
            'discount_val' => 'required',
            'sub_total' => 'required',
            'total' => 'required',
            'tax' => 'required',
            'estimate_template_id' => 'required',
            'inventories' => 'required|array',
            'inventories.*.description' => 'max:255',
            'inventories.*' => 'required|max:255',
            'inventories.*.name' => 'required',
            'inventories.*.quantity' => 'required',
            'inventories.*.price' => 'required',
            'notes' => 'nullable|string',
            'user_id' => 'required|integer',
        ];

        return $rules;
    }
}
