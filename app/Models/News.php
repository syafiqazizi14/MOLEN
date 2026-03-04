<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'image',
        'created_by',
    ];

    /**
     * Relasi ke User sebagai pembuat berita
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
