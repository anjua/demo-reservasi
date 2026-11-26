<?php

// Pesan validasi Bahasa Indonesia (hanya aturan yang dipakai di aplikasi ini).
return [
    'after' => ':Attribute harus setelah :date.',
    'after_or_equal' => ':Attribute minimal hari ini atau setelahnya.',
    'array' => ':Attribute harus berupa daftar.',
    'boolean' => ':Attribute harus bernilai ya atau tidak.',
    'between' => [
        'numeric' => ':Attribute harus antara :min dan :max.',
    ],
    'date' => ':Attribute bukan tanggal yang valid.',
    'date_format' => 'Format :attribute tidak valid.',
    'email' => ':Attribute harus berupa alamat email yang valid.',
    'exists' => ':Attribute yang dipilih tidak ditemukan.',
    'in' => ':Attribute yang dipilih tidak valid.',
    'integer' => ':Attribute harus berupa angka bulat.',
    'max' => [
        'numeric' => ':Attribute maksimal :max.',
        'string' => ':Attribute maksimal :max karakter.',
    ],
    'min' => [
        'numeric' => ':Attribute minimal :min.',
        'string' => ':Attribute minimal :min karakter.',
    ],
    'regex' => 'Format :attribute tidak valid.',
    'required' => ':Attribute wajib diisi.',
    'size' => [
        'array' => ':Attribute harus berisi :size item.',
    ],
    'string' => ':Attribute harus berupa teks.',
    'unique' => ':Attribute sudah digunakan.',

    'attributes' => [
        'email' => 'email',
        'password' => 'kata sandi',
        'code' => 'kode booking',
        'phone' => 'nomor WhatsApp',
    ],
];
