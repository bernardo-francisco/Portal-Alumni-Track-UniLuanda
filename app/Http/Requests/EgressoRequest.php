<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EgressoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nome_completo' => 'required|string|max:200',
            'genero' => 'required|in:M,F,O',
            'data_nascimento' => 'nullable|date|before:today',
            'telefone' => 'nullable|string|max:25',
            'curso_id' => 'nullable|exists:cursos,id',
            'ano_formatura' => 'nullable|integer|min:1990|max:' . (date('Y') + 5),
            'nota_final' => 'nullable|numeric|min:0|max:20',
            'status' => 'required|in:active,inactive,lost_contact',
            'observacoes' => 'nullable|string|max:500',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'nome_completo.required' => 'O nome completo é obrigatório.',
            'nome_completo.max' => 'O nome não pode ter mais de 200 caracteres.',
            'genero.required' => 'O género é obrigatório.',
            'genero.in' => 'O género deve ser M, F ou O.',
            'data_nascimento.date' => 'A data de nascimento deve ser uma data válida.',
            'data_nascimento.before' => 'A data de nascimento deve ser anterior a hoje.',
            'telefone.max' => 'O telefone não pode ter mais de 25 caracteres.',
            'curso_id.exists' => 'O curso selecionado não existe.',
            'ano_formatura.integer' => 'O ano de formatura deve ser um número inteiro.',
            'ano_formatura.min' => 'O ano de formatura deve ser maior ou igual a 1990.',
            'ano_formatura.max' => 'O ano de formatura não pode ser superior a ' . (date('Y') + 5) . '.',
            'nota_final.numeric' => 'A nota final deve ser um número.',
            'nota_final.min' => 'A nota final deve ser no mínimo 0.',
            'nota_final.max' => 'A nota final deve ser no máximo 20.',
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status deve ser active, inactive ou lost_contact.',
            'foto.image' => 'O arquivo deve ser uma imagem.',
            'foto.mimes' => 'A imagem deve ser do tipo JPG, JPEG ou PNG.',
            'foto.max' => 'A imagem não pode ter mais de 2MB.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitizar campos
        $this->merge([
            'nome_completo' => trim($this->nome_completo),
            'telefone' => preg_replace('/[^0-9+]/', '', $this->telefone ?? ''),
            'observacoes' => strip_tags($this->observacoes ?? ''),
        ]);
    }
}