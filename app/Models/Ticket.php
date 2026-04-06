<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'user_id',
        'subject',
        'description',
        'location',
        'category',
        'priority',
        'status',
        'technician_id',
        'tindak_lanjut',
        'keterangan_it',
        'kategori_perubahan',
        'kategori_alat',
        'taken_at',
        'closed_at' // <--- TAMBAHKAN DUA INI
    ];

    protected $casts = [
        'taken_at' => 'datetime',
        'closed_at' => 'datetime',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }
}
