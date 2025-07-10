<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLocationRequest extends FormRequest
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
            'kode_lokasi' => 'required|string|max:255|unique:locations,kode_lokasi',
            'nama_lokasi' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kode_lokasi.required' => 'Kode lokasi wajib diisi.',
            'kode_lokasi.unique' => 'Kode lokasi sudah ada.',
            'kode_lokasi.max' => 'Kode lokasi maksimal 255 karakter.',
            'nama_lokasi.required' => 'Nama lokasi wajib diisi.',
            'nama_lokasi.max' => 'Nama lokasi maksimal 255 karakter.',
            'deskripsi.max' => 'Deskripsi maksimal 1000 karakter.',
        ];
    }
}
