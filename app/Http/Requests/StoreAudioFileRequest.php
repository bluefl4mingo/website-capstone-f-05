<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAudioFileRequest extends FormRequest
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
            'item_id'            => ['required','exists:items,id'],
            'nama_file'          => ['required','string','max:255'],
            'file'               => ['required','file','mimetypes:audio/mpeg,audio/wav,audio/mp4,audio/x-m4a','max:25600'],
        ];
    }
}
