<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    public $timestamps = false;
    protected $fillable = ['username', 'password'];
    protected $hidden = ['password'];
    protected function casts(): array { return ['password' => 'hashed']; }
    public function getNameAttribute(): string { return $this->username; }
}
