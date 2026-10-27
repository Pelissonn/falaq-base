<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePerguntaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** O evento vem da URL; copiamos para o payload para validar com `exists`. */
    protected function prepareForValidation(): void
    {
        $this->merge(['evento_id' => $this->route('id')]);
    }

    public function rules(): array
    {
        return [
            'texto' => ['required', 'string', 'min:10', 'max:255'],
            'evento_id' => ['required', 'exists:eventos,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'texto.required' => 'Escreva sua pergunta.',
            'texto.string' => 'A pergunta precisa ser um texto.',
            'texto.min' => 'A pergunta precisa ter pelo menos :min caracteres.',
            'texto.max' => 'A pergunta pode ter no máximo :max caracteres.',
            'evento_id.required' => 'O evento é obrigatório.',
            'evento_id.exists' => 'Este evento não existe.',
        ];
    }
}
