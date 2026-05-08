<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'total_rows',
        'total_columns',
        'is_active',
    ];

    protected function casts(){
        return [
            'is_active' => 'boolean',
            'total_rows' => 'integer',
            'total_columns' => 'integer',
        ];
    }

    // relacionamentos

    public function seats(){
        return $this->hasMany(Seat::class);
    }

    public function screenings(){
        return $this->hasMany(Screening::class);
    }

    // scopes

    // salas ativas
    public function scopeActive(Builder $query){
        return $query->where('is_active', true);
    }

    // salas inativas
    public function scopeInactive(Builder $query){
        return $query->where('is_active', false);
    }

    // accessors

    // total de assentos (linhas x colunas)
    public function getTotalCapacityAttribute(){
        return $this->total_rows * $this->total_columns;
    }
}
