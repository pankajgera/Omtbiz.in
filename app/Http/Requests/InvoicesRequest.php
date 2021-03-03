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
            'invoice_date' => 'required',
            //'due_date' => 'required',
            'user_id' => 'required',
            'discount' => 'required',
            'discount_val' => 'required',
            'sub_total' => 'required',
            'total' => 'required',
            'tax' => 'required',
            'invoice_template_id' => 'required',
            'inventory' => 'required|array',
            'inventory.*' => 'required|max:255',
            'inventory.*.name' => 'required',
            'inventory.*.quantity' => 'required',
            'inventory.*.price' => 'required'
        ];

        return $rules;
    }
}
