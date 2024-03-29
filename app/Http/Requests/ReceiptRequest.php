<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReceiptRequest extends FormRequest
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
            'receipt_date' => 'required',
            'receipt_mode' => 'required',
            'amount' => 'required',
            'list' => 'required',
            'list.id' => 'required|integer',
            'list.name' => 'required|string',
            'receipt_number' => 'required',
            'user_id' => 'required',
        ];

        return $rules;
    }
}
