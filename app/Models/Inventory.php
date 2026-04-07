<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    // Daftarkan kolom yang boleh diisi
    protected $fillable = [
        'asset_code',
        'name',
        'brand',
        'category',
        'specification',
        'room',
        'status',
    ];
}