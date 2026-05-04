<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;

class Genre extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    // boot

    protected static function boot()
    {
        static::creating(function (Genre $genre) {
            if(empty($genre->slug)) {
                $genre->slug = Str::slug($genre->name);
            }
        });

        static::updating(function (Genre $genre) {
            if($genre->isDirty('name') && !$genre->isDirty('slug')) {
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
        return $query->whereHas('movies', fn (Builder $query) => $query->where('status', 'showing'));
    }
}   

