<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'equipment_id' => ['required', 'exists:equipment,id'],
            'member_name' => ['required', 'string', 'max:120'],
            'contact' => ['required', 'string', 'max:160'],
            'reserved_date' => ['required', 'date', 'after_or_equal:today'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $contact = (string) $this->input('contact');
                $isEmail = filter_var($contact, FILTER_VALIDATE_EMAIL);
                $isPhone = strlen(preg_replace('/\D+/', '', $contact)) >= 10;

                if (! $isEmail && ! $isPhone) {
                    $validator->errors()->add('contact', 'Use an email address or a phone number.');
                }
            },
        ];
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        if (is_array($validated) && isset($validated['contact'])) {
            $validated['contact'] = trim($validated['contact']);
        }

        return $validated;
    }
}
