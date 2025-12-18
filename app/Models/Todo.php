<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    protected $fillable = [
        'title',
        'completed',
        'user_id',
        'attachment_path'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAttachmentUrlAttribute()
    {
        if (! $this->attachment_path) {
            return null;
        }
        return asset('storage/' . $this->attachment_path);
    }
}