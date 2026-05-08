<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Genre extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    // boot

    protected static function booted(): void
    {
        static::creating(function (Genre $genre) {
            if (empty($genre->slug)) {
                $genre->slug = Str::slug($genre->name);
                // dd('creating fired', $genre);
            }
        });

        static::updating(function (Genre $genre) {
            if ($genre->isDirty('name') && !$genre->isDirty('slug')) {
                $genre->slug = Str::slug($genre->name);
            }
        });
    }

    // relacionamentos

    public function movies()
    {
        return $this->belongsToMany(Movie::class, 'genre_movie');
    }

    // scopes

    // genero por slug
    public function scopeBySlug(Builder $query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    // generos com ao menos um filme em cartaz
    public function scopeWithShowingMovies(Builder $query){
        return $query->whereHas('movies', fn (Builder $q) => $q->where('status', 'showing'));
    }
}   

