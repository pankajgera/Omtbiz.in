<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoicesRequest extends FormRequest
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
     * Get the validation rules that apply to the request.s
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'debtors' => 'required',
            'debtors.id' => 'required|integer',
            'debtors.name' => 'required|string',
            'discount' => 'integer|min:0',
            'discount_type' => 'required',
            'discount_val' => 'integer|min:0',
            'sub_total' => 'required',
            'total' => 'required',
            'invoice_date' => 'required',
            'invoice_template_id' => 'required',
            'inventories' => 'required|array',
            'inventories.*' => 'required|max:255',
            'inventories.*.name' => 'required',
            'inventories.*.quantity' => 'required',
            'inventories.*.price' => 'required',
            'inventories.*.sale_price' => 'required',
            'notes' => 'nullable|string',
            'user_id' => 'required|integer',
        ];

        return $rules;
    }
}
