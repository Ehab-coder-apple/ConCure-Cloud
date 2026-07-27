<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Custom platform-level expense category (master finance only).
 *
 * See MasterExpense::categoriesAll() for the merge with the built-in list.
 */
class MasterExpenseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'label',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Slugify a label into a stable key. Falls back to a hash for empty/non-ASCII
     * results so we never produce a blank key.
     */
    public static function makeKey(string $label): string
    {
        $slug = Str::slug($label, '_');
        $slug = $slug !== '' ? $slug : 'cat_' . substr(md5($label), 0, 8);
        return Str::limit($slug, 60, '');
    }
}
