<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePerguntaRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * TICKET #001: Implemente aqui as regras de validação estritas.
     * Requisitos:
     * - texto: obrigatório, string, mínimo de 10 caracteres, máximo de 255.
     * - evento_id: obrigatório, deve existir na tabela eventos.
     */
    public function rules(): array
    {
        return [
            'texto'     => ['required', 'string', 'min:10', 'max:255'],
            'evento_id' => ['required', 'exists:eventos,id'],
        ];
    }

    /**
     * Mescla o parâmetro {id} da rota como evento_id
     * para que a regra exists:eventos,id seja aplicada.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'evento_id' => $this->route('id'),
        ]);
    }

    /**
     * Mensagens de erro amigáveis em português.
     */
    public function messages(): array
    {
        return [
            'texto.required'     => 'O texto da pergunta é obrigatório.',
            'texto.min'          => 'A pergunta deve ter no mínimo :min caracteres.',
            'texto.max'          => 'A pergunta deve ter no máximo :max caracteres.',
            'evento_id.required' => 'O evento é obrigatório.',
            'evento_id.exists'   => 'O evento informado não existe.',
        ];
    }
}
