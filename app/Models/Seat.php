<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class Seat extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'row_label',
        'column_label',
        'label',
        'is_active',
    ];

    protected function casts(){
        return [
            'column_number' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    // relacionamentos

    public function room(){
        return $this->belongsTo(Room::class);
    }

    public function tickets(){
        return $this->hasMany(Ticket::class);
    }

    // scopes

    // apenas assentos ativos
    public function scopeActive(Builder $query){
        return $query->where('is_active', true);
    }

    // apenas assentos inativos
    public function scopeInactive(Builder $query){
        return $query->where('is_active', false);
    }

    // filtro por sala
    public function scopeForRoom(Builder $query, int $roomId){
        return $query->where('room_id', $roomId);
    }

    // assentos disponíveis para uma sessão (sem ticket ativo).
    public function scopeAvailableForScreening(Builder $query, int $screeningId){
        return $query->where('is_active', true)
        ->whereDoesntHave('tickets', function(Builder $query) use ($screeningId){
            $query->where('screening_id', $screeningId)
            ->whereIn('status', ['active', 'used']);
        });
    }

    // assentos ocupados para uma sessão
    public function scopeOccupiedForScreening(Builder $query, int $screeningId){
        return $query->whereHas('tickets', function(Builder $query) use ($screeningId){
            $query->where('screening_id', $screeningId)
            ->whereIn('status', ['active', 'used']);
        });
    }

    // filtro por fileira
    public function scopeByRow(Builder $query, string $row){
        return $query->where('row_label', $row);
    }

    // ordernação padrao, fileira e coluna
    public function scopeOrdered(Builder $query){
        return $query->orderBy('row_label')->orderBy('column_label');
    }
}
