<?php

namespace App\Http\Requests;

use App\Enums\StudyType;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactMessageRequest extends FormRequest
{
    /**
     * Minimum number of seconds a real visitor takes between the page
     * loading and the form being submitted. A bot posting straight to
     * this endpoint has no page-load delay to respect.
     */
    private const int MINIMUM_SECONDS_TO_FILL = 3;

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

    /**
     * Detect a spam submission via two signals invisible to a real
     * visitor: a honeypot field (resources/views/welcome.blade.php)
     * that only a bot filling every input would fill, and an encrypted
     * render timestamp proving the page was loaded and took a
     * plausible amount of time to fill in.
     *
     * Deliberately not part of rules() — a failed check should look
     * like success to the caller (see ContactController::store()),
     * not surface as a validation error a bot could learn to avoid.
     */
    public function looksLikeSpam(): bool
    {
        if (filled($this->input('website'))) {
            return true;
        }

        if (! $this->filled('rendered_at')) {
            return true;
        }

        try {
            $renderedAt = (int) decrypt((string) $this->input('rendered_at'));
        } catch (DecryptException) {
            return true;
        }

        return (int) now()->timestamp - $renderedAt < self::MINIMUM_SECONDS_TO_FILL;
    }
}
