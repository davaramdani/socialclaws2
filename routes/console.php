<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('studio:admin {username}', function () {
    $username = trim($this->argument('username'));
    if (!preg_match('/^[a-zA-Z0-9_.-]{3,30}$/', $username)) {
        $this->error('Username harus 3–30 karakter: huruf, angka, titik, garis bawah, atau tanda hubung.');
        return 1;
    }
    if (\App\Models\Admin::where('username', $username)->exists()) {
        $this->error('Username sudah terdaftar. Akun yang ada tidak diubah.');
        return 1;
    }
    $password = $this->secret('Password admin (8–72 karakter)');
    if (strlen((string) $password) < 8 || strlen($password) > 72) {
        $this->error('Password harus 8–72 karakter.');
        return 1;
    }
    if ($password !== $this->secret('Ulangi password admin')) {
        $this->error('Konfirmasi password tidak sesuai.');
        return 1;
    }
    \App\Models\Admin::create(['username' => $username, 'password' => \Illuminate\Support\Facades\Hash::make($password)]);
    $this->info('Admin berhasil dibuat. Masuk melalui /admin/login.');
    return 0;
})->purpose('Membuat admin studio tanpa mengubah akun yang sudah ada');
