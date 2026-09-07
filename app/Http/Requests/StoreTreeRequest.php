<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTreeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // la autorización de "puede plantar" es a nivel de usuario autenticado (middleware auth:sanctum)
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            // Únicos dos campos que el cliente puede definir. status, level, health, planted_at, last_care_at, etc. 
            // NUNCA aparecen aquí: si el cliente los envía, se ignoran silenciosamente porque no están en $validated().
            'tree_type_id' => ['required', 'integer', 'exists:tree_types,id'],
            'nickname' => ['required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'tree_type_id.exists' => 'La especie de árbol seleccionada no existe.',
            'nickname.max' => 'El apodo no puede superar los 50 caracteres.',
        ];
    }
}
