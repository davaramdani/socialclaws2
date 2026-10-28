<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    public $timestamps = false;
    protected $fillable = ['full_name', 'email', 'address', 'password'];
    protected $hidden = ['password', 'remember_token'];
    protected function casts(): array { return ['created_at' => 'datetime', 'password' => 'hashed']; }
    public function getNameAttribute(): string { return $this->full_name; }
    public function cartItems() { return $this->hasMany(CartItem::class); }
    public function bookings() { return $this->hasMany(Booking::class); }
    public function reviews() { return $this->hasMany(Review::class); }
}
