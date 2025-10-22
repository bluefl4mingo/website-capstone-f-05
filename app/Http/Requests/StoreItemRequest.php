<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
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
            'nama_item'        => ['required','string','max:255'],
            'deskripsi'        => ['nullable','string'],
            'kategori'         => ['nullable','string','max:100'],
            'lokasi_pameran'   => ['nullable','string','max:100'],
            'tanggal_penambahan' => ['nullable','date'],
        ];
    }
}
