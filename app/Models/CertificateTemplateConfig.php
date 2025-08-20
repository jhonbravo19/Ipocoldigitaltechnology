<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class CertificateTemplateConfig extends Model
{
    use HasFactory;

    protected $table = 'certificate_template_config';

    private const CACHE_KEY = 'certificate_template_config';
    private const CACHE_TTL = 3600;

    private const DEFAULT_CONFIG = [
        'certificate_title' => 'Certificado de Participación',
        'intro_text' => 'Por medio del presente certificamos que',
        'signature_1_name' => 'Director General',
        'signature_1_position' => 'Director',
        'signature_2_name' => 'Coordinador Académico',
        'signature_2_position' => 'Coordinador',
        'additional_text' => 'Se otorga el presente certificado en reconocimiento a la participación exitosa.',
    ];

    private const IMAGE_FIELDS = [
        'company_logo',
        'signature_1_image',
        'signature_2_image',
        'background_image',
        'carnet_background_image',
    ];

    private const TEXT_FIELDS = [
        'certificate_title',
        'intro_text',
        'signature_1_name',
        'signature_1_position',
        'signature_2_name',
        'signature_2_position',
        'additional_text',
    ];

    protected $fillable = [
        'company_logo',
        'certificate_title',
        'intro_text',
        'signature_1_image',
        'signature_1_name',
        'signature_1_position',
        'signature_2_image',
        'signature_2_name',
        'signature_2_position',
        'background_image',
        'carnet_background_image',
        'additional_text',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function getCompanyLogoUrlAttribute(): ?string
    {
        return $this->company_logo ? Storage::url($this->company_logo) : null;
    }

    public function getBackgroundImageUrlAttribute(): ?string
    {
        return $this->background_image ? Storage::url($this->background_image) : null;
    }

    public function getCarnetBackgroundImageUrlAttribute(): ?string
    {
        return $this->carnet_background_image
            ? Storage::url($this->carnet_background_image) : null;
    }


    public function getSignature1ImageUrlAttribute(): ?string
    {
        return $this->signature_1_image ? Storage::url($this->signature_1_image) : null;
    }

    public function getSignature2ImageUrlAttribute(): ?string
    {
        return $this->signature_2_image ? Storage::url($this->signature_2_image) : null;
    }


    public function setCertificateTitleAttribute(?string $value): void
    {
        $this->attributes['certificate_title'] = $value ? trim($value) : null;
    }

    public function setIntroTextAttribute(?string $value): void
    {
        $this->attributes['intro_text'] = $value ? trim($value) : null;
    }

    public function setSignature1NameAttribute(?string $value): void
    {
        $this->attributes['signature_1_name'] = $value ? trim($value) : null;
    }

    public function setSignature1PositionAttribute(?string $value): void
    {
        $this->attributes['signature_1_position'] = $value ? trim($value) : null;
    }

    public function setSignature2NameAttribute(?string $value): void
    {
        $this->attributes['signature_2_name'] = $value ? trim($value) : null;
    }

    public function setSignature2PositionAttribute(?string $value): void
    {
        $this->attributes['signature_2_position'] = $value ? trim($value) : null;
    }

    public function setAdditionalTextAttribute(?string $value): void
    {
        $this->attributes['additional_text'] = $value ? trim($value) : null;
    }


    public static function getActiveConfig(): self
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $config = self::first();

            if (!$config) {
                $config = self::create(self::DEFAULT_CONFIG);
                self::clearCache();
            }

            return $config;
        });
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function refreshCache(): self
    {
        self::clearCache();
        return self::getActiveConfig();
    }


    public function hasAllImages(): bool
    {
        foreach (self::IMAGE_FIELDS as $field) {
            if (empty($this->$field)) {
                return false;
            }
        }
        return true;
    }

    public function hasRequiredImages(): bool
    {
        $requiredImages = ['company_logo', 'signature_1_image', 'signature_2_image'];

        foreach ($requiredImages as $field) {
            if (empty($this->$field)) {
                return false;
            }
        }
        return true;
    }

    public function getMissingImages(): array
    {
        $missing = [];

        foreach (self::IMAGE_FIELDS as $field) {
            if (empty($this->$field)) {
                $missing[] = $field;
            }
        }

        return $missing;
    }

    public function getCustomizableFields(): array
    {
        $fields = [];

        foreach (self::TEXT_FIELDS as $field) {
            $fields[$field] = $this->$field;
        }

        return $fields;
    }

    public function getImageFields(): array
    {
        return collect(self::IMAGE_FIELDS)->mapWithKeys(function ($field) {
            $urlField = str_replace(['_image', '_logo'], ['_image_url', '_logo_url'], $field);
            return [
                $field => [
                    'path' => $this->$field,
                    'url' => $this->$urlField,
                    'exists' => !empty($this->$field),
                ]
            ];
        })->toArray();
    }

    public function getCompleteConfig(): array
    {
        return [
            'id' => $this->id,
            'text_fields' => $this->getCustomizableFields(),
            'image_fields' => $this->getImageFields(),
            'has_all_images' => $this->hasAllImages(),
            'has_required_images' => $this->hasRequiredImages(),
            'missing_images' => $this->getMissingImages(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }


    public function isConfigurationComplete(): bool
    {
        $requiredTextFields = [
            'certificate_title',
            'intro_text',
            'signature_1_name',
            'signature_1_position'
        ];

        foreach ($requiredTextFields as $field) {
            if (empty($this->$field)) {
                return false;
            }
        }

        return $this->hasRequiredImages();
    }

    public function getValidationErrors(): array
    {
        $errors = [];

        if (empty($this->certificate_title)) {
            $errors[] = 'El título del certificado es requerido';
        }

        if (empty($this->intro_text)) {
            $errors[] = 'El texto de introducción es requerido';
        }

        if (empty($this->signature_1_name)) {
            $errors[] = 'El nombre de la primera firma es requerido';
        }

        if (empty($this->signature_1_position)) {
            $errors[] = 'El cargo de la primera firma es requerido';
        }

        if (empty($this->company_logo)) {
            $errors[] = 'El logo de la empresa es requerido';
        }

        if (empty($this->signature_1_image)) {
            $errors[] = 'La imagen de la primera firma es requerida';
        }

        if (empty($this->signature_2_image)) {
            $errors[] = 'La imagen de la segunda firma es requerida';
        }

        return $errors;
    }

    public function updateTextFields(array $textData): bool
    {
        $filteredData = array_intersect_key($textData, array_flip(self::TEXT_FIELDS));

        if (empty($filteredData)) {
            return false;
        }

        $this->update($filteredData);
        self::clearCache();

        return true;
    }

    public function updateImageField(string $field, ?string $path): bool
    {
        if (!in_array($field, self::IMAGE_FIELDS)) {
            return false;
        }

        if ($this->$field && $this->$field !== $path) {
            Storage::disk('public')->delete($this->$field);
        }

        $this->update([$field => $path]);
        self::clearCache();

        return true;
    }

    public function resetToDefaults(): bool
    {
        foreach (self::IMAGE_FIELDS as $field) {
            if ($this->$field) {
                Storage::disk('public')->delete($this->$field);
            }
        }

        $defaultData = array_merge(
            self::DEFAULT_CONFIG,
            array_fill_keys(self::IMAGE_FIELDS, null)
        );

        $this->update($defaultData);
        self::clearCache();

        return true;
    }

    protected static function booted(): void
    {
        static::saved(function (self $config) {
            self::clearCache();
        });

        static::deleting(function (self $config) {
            $config->deleteAllImages();
        });

        static::updating(function (self $config) {
            foreach (self::IMAGE_FIELDS as $field) {
                if ($config->isDirty($field)) {
                    $originalPath = $config->getOriginal($field);
                    if ($originalPath && $originalPath !== $config->$field) {
                        Storage::disk('public')->delete($originalPath);
                    }
                }
            }
        });
    }

    private function deleteAllImages(): void
    {
        foreach (self::IMAGE_FIELDS as $field) {
            if ($this->$field) {
                Storage::delete($this->$field);
            }
        }
    }

    public function getConfigurationStatus(): array
    {
        $totalFields = count(self::TEXT_FIELDS) + count(self::IMAGE_FIELDS);
        $completedFields = 0;

        foreach (self::TEXT_FIELDS as $field) {
            if (!empty($this->$field)) {
                $completedFields++;
            }
        }

        foreach (self::IMAGE_FIELDS as $field) {
            if (!empty($this->$field)) {
                $completedFields++;
            }
        }

        $completionPercentage = round(($completedFields / $totalFields) * 100, 2);

        return [
            'total_fields' => $totalFields,
            'completed_fields' => $completedFields,
            'completion_percentage' => $completionPercentage,
            'is_complete' => $this->isConfigurationComplete(),
            'validation_errors' => $this->getValidationErrors(),
        ];
    }

    public static function getDefaultConfiguration(): array
    {
        return array_merge(
            self::DEFAULT_CONFIG,
            array_fill_keys(self::IMAGE_FIELDS, null)
        );
    }
}
