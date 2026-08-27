<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LabTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'category',
        'normal_range_min',
        'normal_range_max',
        'unit',
        'is_frequent',
        'clinic_id',
        'is_active',
    ];

    protected $casts = [
        'normal_range_min' => 'decimal:2',
        'normal_range_max' => 'decimal:2',
        'is_frequent' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Lab test categories shown as checklist groups on the New Lab Request
     * form. The first six keys map 1:1 to the built-in CATALOG groups below;
     * the rest are kept for backward compatibility with older rows/records
     * that used the previous (finer-grained) category keys.
     */
    const CATEGORIES = [
        'blood' => 'Blood Tests',
        'urine_stool' => 'Urine & Stool Tests',
        'hormones' => 'Hormones Tests',
        'imaging' => 'Imaging Tests',
        'genetic_specialty' => 'Genetic & Specialty',
        'biopsy_pap' => 'Biopsy & Pap Smear',
        // Legacy keys, kept so older rows still resolve to a readable label.
        'urine' => 'Urine',
        'stool' => 'Stool',
        'biopsy' => 'Biopsy',
        'culture' => 'Culture',
        'genetic' => 'Genetic',
        'hormone' => 'Hormone',
        'cardiac' => 'Cardiac',
        'other' => 'Other',
    ];

    /**
     * Built-in checklist of common lab/imaging tests, grouped by category
     * key (see CATEGORIES above). This is a static, curated catalog (not
     * database rows) so every clinic sees it immediately with zero setup -
     * mirrors the Medicine::FORMS built-in pattern. Clinics can add their
     * own tests/categories on top via clinic-scoped `lab_tests` rows (see
     * catalogForClinic()).
     */
    const CATALOG = [
        'blood' => [
            'Complete Blood Count (CBC)',
            'Comprehensive Metabolic Panel (CMP)',
            'Basic Metabolic Panel (BMP)',
            'Lipid Panel / Cholesterol',
            'Hemoglobin A1c (HbA1c)',
            'Liver Function Tests (LFT)',
        ],
        'urine_stool' => [
            'Routine Urinalysis',
            'Urine Culture & Sensitivity',
            'Urine Pregnancy (hCG)',
            'Fecal Occult Blood (FOBT)',
            'Stool Culture',
            'Ova & Parasites (O&P)',
        ],
        'hormones' => [
            'Thyroid Panel (TSH, Free T3, Free T4)',
            'Estrogen / Estradiol',
            'Progesterone',
            'Testosterone (Total/Free)',
            'Cortisol (AM/PM)',
            'FSH & LH',
        ],
        'imaging' => [
            'X-Ray (Specify Body Part)',
            'Ultrasound (US)',
            'CT Scan (with/without contrast)',
            'MRI',
        ],
        'genetic_specialty' => [
            'BRCA 1 & BRCA 2 Mutation',
            'Noninvasive Prenatal (NIPT)',
            'Whole Exome Sequencing',
            'HLA Typing',
        ],
        'biopsy_pap' => [
            'Pap Smear (Cervical Screening)',
            'Skin Punch Biopsy',
            'Core Needle Biopsy',
            'Bone Marrow Aspiration',
        ],
    ];

    /**
     * Resolve the display category label for a submitted LabRequestTest,
     * used to group tests on the printed/PDF lab request into the same
     * categories shown on the New Lab Request checklist. Custom clinic
     * tests use their stored `category`; built-in checklist tests are
     * matched back to CATALOG by name (they have no `lab_test_id`, since
     * the catalog is a static PHP constant, not database rows); anything
     * else (freeform "Other" rows) falls back to 'Additional Tests'.
     */
    public static function resolveCategoryLabel(\App\Models\LabRequestTest $test): string
    {
        if ($test->labTest) {
            return $test->labTest->category_display;
        }

        foreach (self::CATALOG as $key => $names) {
            foreach ($names as $name) {
                if (strcasecmp($name, $test->test_name) === 0) {
                    return self::CATEGORIES[$key] ?? ucfirst($key);
                }
            }
        }

        return 'Additional Tests';
    }

    /**
     * Build the checklist catalog for a clinic: the built-in CATALOG groups
     * merged with any custom tests/categories the clinic has added. Custom
     * tests are grouped by their own free-text `category` (slugified into a
     * key), so a brand-new category typed by a doctor becomes its own group,
     * while picking an existing category key merges straight into that
     * built-in group.
     *
     * @return array<string, array{label: string, tests: array<int, array{id: int|null, name: string}>}>
     */
    public static function catalogForClinic(?int $clinicId): array
    {
        $catalog = [];

        foreach (self::CATALOG as $key => $names) {
            $catalog[$key] = [
                'label' => self::CATEGORIES[$key] ?? ucfirst($key),
                'tests' => array_map(fn ($name) => ['id' => null, 'name' => $name], $names),
            ];
        }

        if ($clinicId) {
            self::where('clinic_id', $clinicId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
                ->each(function ($test) use (&$catalog) {
                    $key = $test->category ? Str::slug($test->category, '_') : 'custom';

                    if (!isset($catalog[$key])) {
                        $catalog[$key] = [
                            'label' => $test->category ?: 'Custom',
                            'tests' => [],
                        ];
                    }

                    // Avoid duplicating a test the clinic re-added that already
                    // exists (by name) in the built-in list for this group.
                    $alreadyListed = collect($catalog[$key]['tests'])
                        ->contains(fn ($t) => strcasecmp($t['name'], $test->name) === 0);

                    if (!$alreadyListed) {
                        $catalog[$key]['tests'][] = ['id' => $test->id, 'name' => $test->name];
                    }
                });
        }

        return $catalog;
    }

    /**
     * Get the clinic that owns the lab test.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the lab request tests for this lab test.
     */
    public function labRequestTests(): HasMany
    {
        return $this->hasMany(LabRequestTest::class);
    }

    /**
     * Get the category display name.
     */
    public function getCategoryDisplayAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    /**
     * Get the normal range display.
     */
    public function getNormalRangeDisplayAttribute(): string
    {
        if ($this->normal_range_min !== null && $this->normal_range_max !== null) {
            $range = $this->normal_range_min . ' - ' . $this->normal_range_max;
            if ($this->unit) {
                $range .= ' ' . $this->unit;
            }
            return $range;
        } elseif ($this->normal_range_max !== null) {
            $range = '< ' . $this->normal_range_max;
            if ($this->unit) {
                $range .= ' ' . $this->unit;
            }
            return $range;
        } elseif ($this->normal_range_min !== null) {
            $range = '> ' . $this->normal_range_min;
            if ($this->unit) {
                $range .= ' ' . $this->unit;
            }
            return $range;
        }
        
        return 'Not specified';
    }

    /**
     * Get the full test name with code.
     */
    public function getFullNameAttribute(): string
    {
        $name = $this->name;
        if ($this->code) {
            $name .= ' (' . $this->code . ')';
        }
        return $name;
    }

    /**
     * Check if a value is within normal range.
     */
    public function isValueNormal(float $value): bool
    {
        if ($this->normal_range_min !== null && $value < $this->normal_range_min) {
            return false;
        }
        
        if ($this->normal_range_max !== null && $value > $this->normal_range_max) {
            return false;
        }
        
        return true;
    }

    /**
     * Get value status (normal, low, high).
     */
    public function getValueStatus(float $value): string
    {
        if ($this->normal_range_min !== null && $value < $this->normal_range_min) {
            return 'low';
        }
        
        if ($this->normal_range_max !== null && $value > $this->normal_range_max) {
            return 'high';
        }
        
        return 'normal';
    }

    /**
     * Scope to filter active lab tests.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter frequent lab tests.
     */
    public function scopeFrequent($query)
    {
        return $query->where('is_frequent', true);
    }

    /**
     * Scope to filter by clinic.
     */
    public function scopeByClinic($query, int $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Scope to search lab tests.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
