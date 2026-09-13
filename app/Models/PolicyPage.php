<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PolicyPage extends Model
{
    protected $fillable = ['key', 'title', 'eyebrow', 'intro', 'sections', 'effective_date', 'is_visible'];

    protected function casts(): array
    {
        return ['sections' => 'array', 'effective_date' => 'date', 'is_visible' => 'boolean'];
    }
}
