<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Screening extends Model
{
    use HasFactory;

    protected $fillable = [
        'movie_id',
        'room_id',
        'start_time',
        'end_time',
        'price',
        'is_active'
    ];

    protected function casts()
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'is_active' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    // relacionamentos

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    // scopes

    // sessões ativas
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    // sessões futuras
    public function scopeUpcoming(Builder $query)
    {
        return $query->where('start_time', '>', Carbon::now());
    }

    // sessões passadas
    public function scopePast(Builder $query)
    {
        return $query->where('start_time', '<', Carbon::now());
    }

    // sessoes numa determinada data
    public function scopeOnDate(Builder $query, string $date)
    {
        return $query->whereDate('start_time', $date);
    }

    // sessoes entre duas datas
    public function scopeBetweenDates(Builder $query, string $startDate, string $endDate)
    {
        return $query->whereBetween('start_time', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ]);
    }

    // sessoes de um filme
    public function scopeForMovie(Builder $query, int $movieId)
    {
        return $query->where('movie_id', $movieId);
    }

    // sessoes de uma sala
    public function scopeForRoom(Builder $query, int $roomId)
    {
        return $query->where('room_id', $roomId);
    }

    // sessoes dentro de uma faixa de preço
    public function scopeBetweenPrices(Builder $query, float $min, float $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    // verifica conflito de horário para uma sala (usado ao criar/atualizar sessões).
    // exclui opcionalmente a própria sessão (para updates).
    public function scopeConflictsWithTimeSlot(
        Builder $query,
        int $roomId,
        string $startTime,
        string $endTime,
        ?int $excludeId = null
    ) {
        $query->where('room_id', $roomId)
            ->where('is_active', true)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query;
    }

    // accessors

    // horário formatado: '14:30'
    public function getFormattedTimeAttribute(): string{
        return $this->start_time->format('H:i');
    }

    // data formatada: "04/05/2026"
    public function getFormattedDateAttribute(): string{
        return $this->start_time->format('d/m/Y');
    }

    // verifica se a sessão já começou
    public function getHasStartedAttribute(): bool{
        return $this->start_time->isPast();
    }

    // verifica se a sessão acabou
    public function getHasEndedAttribute(): bool{
        return $this->end_time->isPast();
    }
}
