<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrdersRequest extends FormRequest
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
            'order_date' => 'required',
            'expiry_date' => 'nullable',
            'user_id' => 'required|integer',
            'sub_total' => 'nullable',
            'total' => 'nullable',
            'order_items' => 'required|array',
            'order_items.*.description' => 'max:255',
            'order_items.*' => 'required|max:255',
            'order_items.*.name' => 'required',
            'order_items.*.quantity' => 'required',
            'order_items.*.price' => 'nullable',
            'order_items.*.sale_price' => 'nullable',
            'notes' => 'nullable|string',
        ];

        return $rules;
    }
}
