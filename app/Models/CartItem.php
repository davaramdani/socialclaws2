<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $table = 'cart';
    public $timestamps = false;
    protected $fillable = ['user_id', 'item_id', 'item_type', 'quantity'];
    protected function casts(): array { return ['quantity' => 'integer', 'item_id' => 'integer', 'user_id' => 'integer']; }
    public function user() { return $this->belongsTo(User::class); }
    public function service() { return $this->belongsTo(Service::class, 'item_id'); }
    public function galleryItem() { return $this->belongsTo(Gallery::class, 'item_id'); }
    public function getItemAttribute(): ?Service { return $this->item_type === 'service' ? $this->service : $this->galleryItem; }
    public function getSubtotalAttribute(): int { return ($this->item?->price ?? 0) * $this->quantity; }
}
