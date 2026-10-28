<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'description', 'price', 'image', 'duration_minutes', 'category'];
    protected function casts(): array { return ['price' => 'integer', 'duration_minutes' => 'integer']; }
    public function getImageUrlAttribute(): string
    {
        $image = $this->image ?: 'images/nail-art.jpg';
        return preg_match('~^https?://~i', $image) ? $image : asset(ltrim($image, '/'));
    }
}
