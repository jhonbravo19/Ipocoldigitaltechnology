<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'duration_hours',
        'serial_prefix',
        'serial_counter',
    ];

    protected $casts = [
        'duration_hours' => 'integer',
        'serial_counter' => 'integer',
    ];

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function scopeActive($query)
    {
        return $query;
    }

    public function scopeWithCertificateCount($query)
    {
        return $query->withCount('certificates');
    }

    public function getNextSerialNumber()
    {
        $this->increment('serial_counter');
        return $this->serial_prefix . '-' . str_pad($this->serial_counter, 4, '0', STR_PAD_LEFT);
    }

    public function getLastSerialNumber()
    {
        return $this->serial_prefix . '-' . str_pad($this->serial_counter, 4, '0', STR_PAD_LEFT);
    }

    public static function isPrefixUnique($prefix, $excludeId = null)
    {
        $query = self::where('serial_prefix', $prefix);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return $query->count() === 0;
    }

    public function getFormattedDurationAttribute()
    {
        return $this->duration_hours . ' hora' . ($this->duration_hours > 1 ? 's' : '');
    }

    public function getActiveCertificatesCountAttribute()
    {
        return $this->certificates()->active()->count();
    }
}
