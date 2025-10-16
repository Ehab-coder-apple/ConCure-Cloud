<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MealType extends Model
{
    use HasFactory;

    protected $table = 'meal_types';

    protected $fillable = [
        'key',
        'name',
    ];

    /**
     * Default keys
     */
    public const KEYS = ['breakfast', 'lunch', 'dinner', 'snack'];

    /**
     * Foods associated with this meal type.
     */
    public function foods(): BelongsToMany
    {
        return $this->belongsToMany(Food::class, 'food_meal_type');
    }
}

