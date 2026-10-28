<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id', 'customer_name', 'customer_address', 'booking_details', 'total_price', 'appointment_date', 'appointment_time', 'phone', 'status'];
    protected function casts(): array { return ['booking_time' => 'datetime', 'appointment_date' => 'date', 'total_price' => 'integer', 'user_id' => 'integer']; }
    public function user() { return $this->belongsTo(User::class); }
    public function getReferenceAttribute(): string { return 'SC-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT); }
}
