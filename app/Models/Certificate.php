<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Certificate extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_REPLACED = 'replaced';

    public const STATUSES = [
        self::STATUS_ACTIVE => 'Activo',
        self::STATUS_INACTIVE => 'Inactivo',
        self::STATUS_REPLACED => 'Reemplazado',
    ];

    protected $fillable = [
        'certificate_holder_id',
        'course_id',
        'series_number',
        'issue_date',
        'expiry_date',
        'certificate_file_path',
        'card_file_path',
        'acta_file_path',
        'paquete_file_path',
        'status',
        'status_reason',
        'issued_by'
    ];

    protected $casts = [
        'issue_date' => 'datetime',
        'expiry_date' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_ACTIVE,
        'status_reason' => null,
    ];

    public function holder()
    {
        return $this->belongsTo(CertificateHolder::class, 'certificate_holder_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expiry_date', '<', now());
    }

    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where('expiry_date', '>=', now());
    }

    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>=', now());
    }

    public function scopeBySeries(Builder $query, string $seriesNumber): Builder
    {
        return $query->where('series_number', $seriesNumber);
    }

    public function scopeByDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('issue_date', [$startDate, $endDate]);
    }

    public function scopeWithRelations(Builder $query): Builder
    {
        return $query->with(['holder', 'course', 'issuer']);
    }

    public static function canIssueNew(int $holderId, int $courseId): array
    {
        $lastCert = self::where('certificate_holder_id', $holderId)
            ->where('course_id', $courseId)
            ->latest('issue_date')
            ->first();

        if (!$lastCert) {
            return ['allowed' => true, 'reason' => 'new'];
        }

        if ($lastCert->isExpired()) {
            return ['allowed' => true, 'reason' => 'expired', 'certificate' => $lastCert];
        }

        if ($lastCert->status === self::STATUS_INACTIVE && $lastCert->status_reason !== 'expired') {
            return ['allowed' => false, 'reason' => 'inactive', 'certificate' => $lastCert];
        }

        if ($lastCert->expiresSoon(15)) {
            return ['allowed' => true, 'reason' => 'renewal', 'certificate' => $lastCert];
        }

        return ['allowed' => false, 'reason' => 'active', 'certificate' => $lastCert];
    }

    public static function searchPublic(string $searchType, string $searchValue, ?string $additionalValue = null): Builder
    {
        $query = self::query()
            ->with(['holder', 'course'])
            ->active();

        return match ($searchType) {
            'identification' => $query->whereHas(
                'holder',
                fn($q) =>
                $q->where('identification_number', $searchValue)
            ),
            'name' => $query->whereHas(
                'holder',
                fn($q) =>
                $q->where('first_names', 'LIKE', "%{$searchValue}%")
                    ->where('last_names', 'LIKE', "%{$additionalValue}%")
            ),
        };
    }

    public function getCertificateUrlAttribute(): ?string
    {
        return $this->certificate_file_path
            ? Storage::url($this->certificate_file_path)
            : null;
    }

    public function getCardUrlAttribute(): ?string
    {
        return $this->card_file_path
            ? Storage::url($this->card_file_path)
            : null;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? 'Desconocido';
    }

    public function setSeriesNumberAttribute(?string $value): void
    {
        $this->attributes['series_number'] = $value ? strtoupper(trim($value)) : null;
    }

    public function setStatusAttribute(string $value): void
    {
        $this->attributes['status'] = in_array($value, array_keys(self::STATUSES))
            ? $value
            : self::STATUS_ACTIVE;
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isValid(): bool
    {
        return $this->status === self::STATUS_ACTIVE && !$this->isExpired();
    }

    public function daysUntilExpiry(): int
    {
        if (!$this->expiry_date || $this->isExpired()) {
            return 0;
        }

        return now()->diffInDays($this->expiry_date);
    }

    public function expiresSoon(int $days = 30): bool
    {
        return $this->daysUntilExpiry() <= $days && !$this->isExpired();
    }

    public function activate(): bool
    {
        return $this->update(['status' => self::STATUS_ACTIVE]);
    }

    public function deactivate(): bool
    {
        return $this->update(['status' => self::STATUS_INACTIVE]);
    }

    public function hasFiles(): bool
    {
        return !empty($this->certificate_file_path) && !empty($this->card_file_path);
    }

    public function syncStatus(): bool
    {
        if ($this->isExpired() && $this->status === self::STATUS_ACTIVE) {
            return $this->update([
                'status' => self::STATUS_INACTIVE,
                'status_reason' => 'expired',
            ]);
        }

        return true;
    }

    public function isInactiveDueToExpiration(): bool
    {
        return $this->status === self::STATUS_INACTIVE && $this->status_reason === 'expired';
    }

    public function isInactiveManual(): bool
    {
        return $this->status === self::STATUS_INACTIVE && $this->status_reason !== 'expired';
    }

    public function getDisplayInfo(): array
    {
        $this->loadMissing(['holder', 'course']);

        return [
            'holder_name' => $this->holder->full_name ?? 'N/A',
            'holder_identification' => $this->holder->full_identification ?? 'N/A',
            'course_name' => $this->course->name ?? 'N/A',
            'course_hours' => $this->course->duration_hours ?? 0,
            'series_number' => $this->series_number,
            'issue_date' => $this->issue_date?->format('d/m/Y'),
            'expiry_date' => $this->expiry_date?->format('d/m/Y'),
            'status' => $this->status_label,
            'is_expired' => $this->isExpired(),
            'days_until_expiry' => $this->daysUntilExpiry(),
            'certificate_url' => $this->certificate_url,
            'card_url' => $this->card_url,
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (self $certificate) {
            $certificate->deleteFiles();
        });

        static::updating(function (self $certificate) {
            $original = $certificate->getOriginal();

            if ($certificate->isDirty('certificate_file_path') && $original['certificate_file_path']) {
                Storage::delete($original['certificate_file_path']);
            }

            if ($certificate->isDirty('card_file_path') && $original['card_file_path']) {
                Storage::delete($original['card_file_path']);
            }
        });
    }

    private function deleteFiles(): void
    {
        if ($this->certificate_file_path) {
            Storage::delete($this->certificate_file_path);
        }

        if ($this->card_file_path) {
            Storage::delete($this->card_file_path);
        }

        if ($this->acta_file_path) {
            Storage::delete($this->acta_file_path);
        }

        if ($this->paquete_file_path) {
            Storage::delete($this->paquete_file_path);
        }
    }

    public static function getExpiringSoonWithRelations(int $days = 30)
    {
        return self::query()
            ->withRelations()
            ->expiringSoon($days)
            ->active()
            ->orderBy('expiry_date')
            ->get();
    }

    public static function getActiveByHolder(int $holderId)
    {
        return self::query()
            ->where('certificate_holder_id', $holderId)
            ->active()
            ->with('course')
            ->orderBy('issue_date', 'desc')
            ->get();
    }
}
