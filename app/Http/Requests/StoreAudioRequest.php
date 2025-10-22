<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAudioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
        'title'       => ['required','string','max:255'],
        'description' => ['nullable','string'],
        'category_id' => ['nullable','exists:categories,id'],
        'file'        => ['required','file','mimetypes:audio/mpeg,audio/mp4,audio/wav,audio/x-wav,audio/x-ms-wma','max:25600'], // 25 MB
    ];
    }
}
