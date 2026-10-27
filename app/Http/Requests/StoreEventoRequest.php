<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'O título do evento é obrigatório.',
            'titulo.max' => 'O título pode ter no máximo :max caracteres.',
            'descricao.required' => 'A descrição do evento é obrigatória.',
            'descricao.max' => 'A descrição pode ter no máximo :max caracteres.',
        ];
    }
}
