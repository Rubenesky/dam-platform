<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
    ];

    // Una categoría puede tener una categoría padre
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Una categoría puede tener subcategorías
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Una categoría puede tener muchos assets
    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class);
    }
}