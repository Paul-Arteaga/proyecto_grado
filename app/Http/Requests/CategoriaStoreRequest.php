<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoriaStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre'               => ['required','string','max:100','unique:categorias,nombre'],
            'descripcion'          => ['nullable','string','max:2000'],
            'capacidad_pasajeros'  => ['required','integer','min:0','max:255'], // byte
            // Acepta archivo de imagen (jpg, jpeg, png, webp), hasta ~2MB
            'imagen'               => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'activo'               => ['nullable','boolean'],
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'activo' => $this->boolean('activo'),
        ]);
    }
}
