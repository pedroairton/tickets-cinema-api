<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'synopsis',
        'duration_minutes',
        'image_url',
        'trailer_url',
        'age_rating',
        'original_title',
        'director',
        'distributor',
        'country_of_origin',
        'status',
        'release_date',
    ];

    protected $casts = [
        'release_date' => 'date',
        'duration_minutes' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Movie $movie) {
            // dd('creating fired');
            $movie->slug = Str::slug($movie->title);
        });
    }

    // relacionamentos

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'genre_movie');
    }

    public function screenings()
    {
        return $this->hasMany(Screening::class);
    }

    public function tickets()
    {
        return $this->hasManyThrough(Ticket::class, Screening::class);
    }

    // scopes

    // filmes em cartaz
    public function scopeShowing(Builder $query)
    {
        return $query->where('status', 'showing');
    }

    // filmes em breve
    public function scopeComingSoon(Builder $query)
    {
        return $query->where('status', 'coming_soon');
    }

    // filmes fora de cartaz
    public function scopeOffScreen(Builder $query)
    {
        return $query->where('status', 'off_screen');
    }

    // filtra por classificação indicativa máxima (ex: ageRatingUpTo('14') retorna filmes com classificação indicativa L e de 10 a 14 anos)
    public function scopeAgeRatingUpTo(Builder $query, string $maxRating)
    {
        $order = ['L', '10', '12', '14', '16', '18'];
        $maxIndex = array_search($maxRating, $order);

        if ($maxIndex === false) {
            return $query;
        }

        $allowed = array_slice($order, 0, $maxIndex + 1);

        return $query->whereIn('age_rating', $allowed);
    }

    // filtra por classificação indicativa exata
    public function scopeAgeRating(Builder $query, string $rating)
    {
        return $query->where('age_rating', $rating);
    }

    // filtra por gênero (id ou slug)
    public function scopeByGenre(Builder $query, string|int $genre)
    {
        return $query->whereHas('genres', function (Builder $query) use ($genre) {
            if (is_numeric($genre)) {
                $query->where('genres.id', $genre);
            } else {
                $query->where('genres.slug', $genre);
            }
        });
    }

    // filtra por faixa de duração
    public function scopeDurationBetween(Builder $query, int $min, int $max)
    {
        return $query->whereBetween('duration_minutes', [$min, $max]);
    }

    // filmes que possuem sessões em determinada data
    public function scopeWithScreeningsOnDate(Builder $query, string $date)
    {
        return $query->whereHas('screenings', function (Builder $query) use ($date) {
            $query->whereDate('start_time', $date)
                ->where('is_active', true);
        });
    }

    // busca por slug
    public function scopeBySlug(Builder $query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    // accessors

    // duração formatada (ex: 2h 30min)
    public function getFormattedDurationAttribute()
    {
        $hours = intdiv($this->duration_minutes, 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours === 0) {
            return "{$minutes} min";
        }
        if ($minutes === 0) {
            return "{$hours} h";
        }

        return "{$hours} h {$minutes} min";
    }
}
