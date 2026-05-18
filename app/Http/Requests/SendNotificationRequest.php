<?php

namespace App\Http\Requests;

use App\DTO\NotificationRequestDTO;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SendNotificationRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'channel'      => ['required', 'in:sms,email'],
            'message'      => ['required', 'string', 'max:1000'],
            'priority'     => ['required', 'in:transactional,informational,marketing'],
            'recipients'   => ['required', 'array', 'min:1', 'max:10000'],
            'recipients.*' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Ошибки валидации
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'channel.required'    => 'Не указан канал отправки (sms или email).',
            'channel.in'          => 'Канал должен быть sms или email.',

            'message.required'    => 'Текст сообщения обязателен.',
            'message.string'      => 'Текст сообщения должен быть строкой.',
            'message.max'         => 'Текст сообщения не может превышать 1000 символов.',

            'priority.required'   => 'Не указан приоритет сообщения.',
            'priority.in'         => 'Приоритет должен быть transactional, informational или marketing.',

            'recipients.required' => 'Список получателей обязателен.',
            'recipients.array'    => 'Список получателей должен быть массивом.',
            'recipients.min'      => 'Должен быть хотя бы один получатель.',
            'recipients.max'      => 'Максимальное количество получателей — 10 000.',

            'recipients.*.required' => 'ID получателя не может быть пустым.',
            'recipients.*.string'   => 'ID получателя должен быть строкой.',
            'recipients.*.max'      => 'ID получателя не может превышать 255 символов.',
        ];
    }

    /**
     * Формируем DTO
     *
     * @return NotificationRequestDTO
     */
    public function getDTO(): NotificationRequestDTO
    {
        return new NotificationRequestDTO(
            channel: $this->input('channel'),
            message: $this->input('message'),
            priority: $this->input('priority'),
            recipients: $this->input('recipients'),
        );
    }
}
