<?php
namespace App\Http\Requests;

use Auth;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ('admin' === Auth::user()->role) {
            return true;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch ($this->getMethod()) {
            case 'POST':
                return [
                    'name' => 'required',
                    'email' => 'email|unique:users,email',
                ];
                break;
            case 'PUT':
                return [
                    'name' => 'required',
                ];
                break;
            default:
                break;
        }
    }
}
