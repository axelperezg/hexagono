<?php

namespace App\Http\Requests;

use App\Enums\StudyType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * The contact form is public, so any visitor may submit it.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'institution' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'study_type' => ['required', Rule::enum(StudyType::class)],
            'message' => ['required', 'string', 'max:2000'],
        ];
    }

    /**
     * Get custom attribute names, used to build readable Spanish messages
     * from the default validation lines (e.g. "El campo :attribute...").
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre completo',
            'institution' => 'institución o dependencia',
            'email' => 'correo electrónico',
            'phone' => 'teléfono',
            'study_type' => 'tipo de estudio',
            'message' => 'mensaje',
        ];
    }

    /**
     * Get custom messages for validator errors, in Spanish to match the
     * public-facing site.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Indica tu nombre completo.',
            'email.required' => 'Indica un correo electrónico de contacto.',
            'email.email' => 'Indica un correo electrónico válido.',
            'study_type.required' => 'Selecciona el tipo de estudio de tu interés.',
            'study_type.enum' => 'Selecciona un tipo de estudio válido.',
            'message.required' => 'Escribe un breve mensaje describiendo tu solicitud.',
            'message.max' => 'El mensaje no puede exceder los :max caracteres.',
        ];
    }
}
