<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class CertificateHolder extends Model
{
    use HasFactory;

    public const ID_TYPE_CC = 'CC';
    public const ID_TYPE_CE = 'CE';
    public const ID_TYPE_PA = 'PA';

    public const ID_TYPES = [
        self::ID_TYPE_CC => 'Cédula de Ciudadanía',
        self::ID_TYPE_CE => 'Cédula de Extranjería',
        self::ID_TYPE_PA => 'Pasaporte',
    ];

    public const BLOOD_TYPES = [
        'O+',
        'O-',
        'A+',
        'A-',
        'B+',
        'B-',
        'AB+',
        'AB-'
    ];

    protected $fillable = [
        'first_names',
        'last_names',
        'identification_type',
        'identification_number',
        'identification_place',
        'blood_type',
        'photo_path',
        'has_drivers_license',
        'drivers_license_category',
    ];

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function scopeByIdentification(Builder $query, string $type, string $number): Builder
    {
        return $query->where('identification_type', $type)
            ->where('identification_number', $number);
    }

    public function scopeByName(Builder $query, string $firstName, string $lastName): Builder
    {
        return $query->where('first_names', 'LIKE', "%{$firstName}%")
            ->where('last_names', 'LIKE', "%{$lastName}%");
    }

    public function scopeWithActiveCertificates(Builder $query): Builder
    {
        return $query->with([
            'certificates' => function ($q) {
                $q->active()->with('course');
            }
        ]);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('first_names', 'LIKE', "%{$term}%")
                ->orWhere('last_names', 'LIKE', "%{$term}%")
                ->orWhere('identification_number', 'LIKE', "%{$term}%");
        });
    }

    public static function findByIdentification(string $type, string $number): ?self
    {
        return self::byIdentification($type, $number)->first();
    }

    public static function searchByName(string $firstName, string $lastName): Collection
    {
        return self::byName($firstName, $lastName)->get();
    }

    public static function createOrUpdateByIdentification(array $data): self
    {
        return self::updateOrCreate(
            [
                'identification_type' => $data['identification_type'],
                'identification_number' => $data['identification_number']
            ],
            $data
        );
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_names ?? '') . ' ' . ($this->last_names ?? ''));
    }

    public function getFullIdentificationAttribute(): string
    {
        $typeLabel = self::ID_TYPES[$this->identification_type] ?? $this->identification_type;
        return $typeLabel . ' ' . $this->identification_number;
    }

    public function getShortIdentificationAttribute(): string
    {
        return $this->identification_type . ' ' . $this->identification_number;
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? Storage::url($this->photo_path) : null;
    }

    public function getInitialsAttribute(): string
    {
        $firstInitial = $this->first_names ? substr($this->first_names, 0, 1) : '';
        $lastInitial = $this->last_names ? substr($this->last_names, 0, 1) : '';
        return strtoupper($firstInitial . $lastInitial);
    }

    public function getIdentificationTypeLabelAttribute(): string
    {
        return self::ID_TYPES[$this->identification_type] ?? $this->identification_type;
    }

    public function setFirstNamesAttribute(?string $value): void
    {
        $this->attributes['first_names'] = $value ? $this->normalizeText($value) : null;
    }

    public function setLastNamesAttribute(?string $value): void
    {
        $this->attributes['last_names'] = $value ? $this->normalizeText($value) : null;
    }

    public function setIdentificationTypeAttribute(?string $value): void
    {
        $this->attributes['identification_type'] = $value ? strtoupper(trim($value)) : null;
    }

    public function setIdentificationNumberAttribute(?string $value): void
    {
        $this->attributes['identification_number'] = $value ? trim($value) : null;
    }

    public function setIdentificationPlaceAttribute(?string $value): void
    {
        $this->attributes['identification_place'] = $value ? $this->normalizeText($value) : null;
    }

    public function setBloodTypeAttribute(?string $value): void
    {
        $normalizedValue = $value ? strtoupper(trim($value)) : null;
        $this->attributes['blood_type'] = in_array($normalizedValue, self::BLOOD_TYPES)
            ? $normalizedValue
            : null;
    }

    public function hasPhoto(): bool
    {
        return !empty($this->photo_path);
    }

    public function getActiveCertificates(): Collection
    {
        return $this->certificates()
            ->active()
            ->with('course')
            ->orderBy('issue_date', 'desc')
            ->get();
    }

    public function getActiveCertificatesCount(): int
    {
        return $this->certificates()->active()->count();
    }

    public function hasCertificateForCourse(int $courseId): bool
    {
        return $this->certificates()
            ->where('course_id', $courseId)
            ->active()
            ->exists();
    }

    public function getLatestCertificate(): ?Certificate
    {
        return $this->certificates()
            ->active()
            ->with('course')
            ->latest('issue_date')
            ->first();
    }

    public function setHasDriversLicenseAttribute(?string $value): void
    {
        $this->attributes['has_drivers_license'] = $value ? strtoupper(trim($value)) : 'NO';
    }

    public function setDriversLicenseCategoryAttribute(?string $value): void
    {
        $this->attributes['drivers_license_category'] = $value ? strtoupper(trim($value)) : null;
    }

    public function getValidCertificates(): Collection
    {
        return $this->certificates()
            ->active()
            ->notExpired()
            ->with('course')
            ->orderBy('issue_date', 'desc')
            ->get();
    }

    public function hasValidCertificates(): bool
    {
        return $this->certificates()
            ->active()
            ->notExpired()
            ->exists();
    }

    public function getExpiredCertificates(): Collection
    {
        return $this->certificates()
            ->expired()
            ->with('course')
            ->orderBy('expiry_date', 'desc')
            ->get();
    }

    private function normalizeText(string $text): string
    {
        return mb_strtoupper(trim($text), 'UTF-8');
    }

    protected static function booted(): void
    {
        static::deleting(function (self $holder) {
            $holder->deletePhoto();
        });

        static::updating(function (self $holder) {
            if ($holder->isDirty('photo_path')) {
                $original = $holder->getOriginal('photo_path');
                if ($original) {
                    Storage::delete($original);
                }
            }
        });
    }

    private function deletePhoto(): void
    {
        if ($this->photo_path) {
            Storage::delete($this->photo_path);
        }
    }

    public function getCompleteInfo(): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'first_names' => $this->first_names,
            'last_names' => $this->last_names,
            'identification_type' => $this->identification_type,
            'identification_type_label' => $this->identification_type_label,
            'identification_number' => $this->identification_number,
            'full_identification' => $this->full_identification,
            'short_identification' => $this->short_identification,
            'identification_place' => $this->identification_place,
            'blood_type' => $this->blood_type,
            'photo_url' => $this->photo_url,
            'initials' => $this->initials,
            'has_photo' => $this->hasPhoto(),
            'active_certificates_count' => $this->getActiveCertificatesCount(),
            'has_valid_certificates' => $this->hasValidCertificates(),
        ];
    }

    public function isValidIdentificationType(): bool
    {
        return array_key_exists($this->identification_type, self::ID_TYPES);
    }

    public function isValidBloodType(): bool
    {
        return in_array($this->blood_type, self::BLOOD_TYPES);
    }

}
