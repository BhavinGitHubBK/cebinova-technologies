<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

class ProofMedia
{
    public static function rules(): array
    {
        return ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:4096'];
    }

    public static function store(UploadedFile $file): string
    {
        return 'storage/'.$file->store('proof', 'public');
    }
}
