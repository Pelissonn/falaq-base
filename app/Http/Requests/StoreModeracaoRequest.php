<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreModeracaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'motivo' => ['required', 'min:10', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'motivo.required' => 'Descreva o motivo da denúncia.',
            'motivo.min' => 'O motivo precisa ter pelo menos :min caracteres.',
            'motivo.max' => 'O motivo pode ter no máximo :max caracteres.',
        ];
    }
}
