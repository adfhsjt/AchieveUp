<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Mahasiswa extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'mahasiswa';

    protected $fillable = [
        'nim',
        'nama',
        'username',
        'email',
        'password',
        'foto',
        'program_studi_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Relasi ke tabel program_studi (many-to-one).
     */
    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'program_studi_id');
    }
    public function prestasi()
    {
        return $this->belongsToMany(Prestasi::class, 'prestasi_mahasiswa', 'mahasiswa_id', 'prestasi_id');
    }
    public function profil()
    {
        return $this->hasOne(ProfilMahasiswa::class, 'mahasiswa_id');
    }
    public function rekomendasiLombas()
    {
        return $this->hasMany(MahasiswaRekomendasi::class, 'mahasiswa_id');
    }
    public function prestasiNotes()
    {
        return $this->hasMany(MahasiswaPrestasiNote::class);
    }
}
