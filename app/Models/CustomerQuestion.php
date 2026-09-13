<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerQuestion extends Model
{
    protected $fillable = ['user_id', 'name', 'email', 'question', 'status', 'admin_response', 'responded_by', 'responded_at'];

    protected function casts(): array
    {
        return ['responded_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responded_by')->withTrashed();
    }
}
