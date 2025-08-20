<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'identification_type',
        'identification_number',
        'phone',
        'address',
        'role'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function issuedCertificates()
    {
        return $this->hasMany(Certificate::class, 'issued_by');
    }

    public function certificates()
    {
        return Certificate::whereHas('holder', function ($query) {
            $query->where('identification_number', $this->identification_number)
                ->where('identification_type', $this->identification_type);
        })->with(['holder', 'course'])->active();
    }

    public function hasCertificates()
    {
        return $this->certificates()->count() > 0;
    }

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getFullIdentificationAttribute()
    {
        return $this->identification_type . ' ' . $this->identification_number;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isUser()
    {
        return $this->role === 'user';
    }

    public function getAvatarAttribute()
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }

    public function getNameAttribute(): string
    {
        return $this->getFullNameAttribute();
    }

    public static function findByIdentification($type, $number)
    {
        return static::where('identification_type', $type)
            ->where('identification_number', $number)
            ->first();
    }

    public static function identificationExists($type, $number, $excludeId = null)
    {
        $query = static::where('identification_type', $type)
            ->where('identification_number', $number);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeRegularUsers($query)
    {
        return $query->where('role', 'user');
    }
}
