<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoriaUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('categoria')->id ?? null;

        return [
            'nombre'               => ['required','string','max:100',"unique:categorias,nombre,{$id}"],
            'descripcion'          => ['nullable','string','max:2000'],
            'capacidad_pasajeros'  => ['required','integer','min:0','max:255'],
            'imagen'               => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'activo'               => ['nullable','boolean'],
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'activo' => $this->has('activo') ? $this->boolean('activo') : null,
        ]);
    }
}
