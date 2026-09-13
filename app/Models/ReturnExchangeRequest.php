<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnExchangeRequest extends Model
{
    protected $fillable = ['order_id', 'order_item_id', 'user_id', 'type', 'reason', 'status', 'admin_note', 'reviewed_by', 'reviewed_at'];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by')->withTrashed();
    }
}
