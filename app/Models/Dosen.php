<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;//
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Dosen extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'dosen';

    protected $fillable = [
        'nidn',
        'username',
        'nama',
        'email',
        'password',
        'foto',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Caster untuk kolom 'role' jika perlu penanganan enum sebagai string.
     */
    protected $casts = [
        'role' => 'string',
    ];

    public function getRoleAttribute($value)
    {
        return $value === 'admin' ? 'admin' : ($value === 'dosen pembimbing' ? 'dosen pembimbing' : 'unknown');
    }
    public function rekomendasiLombas()
    {
        return $this->hasMany(DosenPembimbingRekomendasi::class, 'dosen_id');
    }
}
