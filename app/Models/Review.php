<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    public $timestamps = false;
    protected $fillable = ['user_id', 'user_name', 'rating', 'review_text'];
    protected function casts(): array { return ['created_at' => 'datetime', 'rating' => 'integer', 'user_id' => 'integer']; }
    public function user() { return $this->belongsTo(User::class); }
}
