<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LegalContactRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'topic',
        'order_reference',
        'message',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }
}
