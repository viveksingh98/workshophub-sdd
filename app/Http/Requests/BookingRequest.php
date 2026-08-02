<?php

namespace App\Http\Requests;

use App\Models\WorkshopClass;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class BookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'workshop_class_id' => ['required', 'exists:workshop_classes,id'],
            'visitor_name' => ['required', 'string', 'max:120'],
            'contact' => ['required', 'string', 'max:160'],
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'seats' => ['required', 'integer', 'min:1', 'max:3'],
            'note' => ['nullable', 'string', 'max:500'],
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

                $class = WorkshopClass::find($this->input('workshop_class_id'));
                if (! $class) {
                    return;
                }

                if ((int) $this->input('seats') > $class->seatsLeft((string) $this->input('scheduled_date'))) {
                    $validator->errors()->add('seats', 'Not enough seats remain for this class and date.');
                }
            },
        ];
    }
}
