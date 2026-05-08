<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'order_id',
        'user_id',
        'screening_id',
        'seat_id',
        'unit_price',
        'status',
    ];

    protected function casts()
    {
        return [
            'unit_price' => 'decimal:2',
        ];
    }

    // boot - auto gerar codigo unico

    protected static function booted(){
        static::creating(function (Ticket $ticket) {
            if(empty($ticket->code)) {
                $ticket->code = self::generateUniqueCode();
            }
        });
    }

    // gerar codigo alfanumerico, 12 caracteres
    // formato TKT-XXXXXXXX (total 12 chars)
    public static function generateUniqueCode(){
        do{
            $code = 'TKT-' . strtoupper(Str::random(8));
        } while(self::where('code', $code)->exists());

        return $code;
    }

    // relacionamentos
    public function order(){
        return $this->belongsTo(Order::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function screening(){
        return $this->belongsTo(Screening::class);
    }

    public function seat(){
        return $this->belongsTo(Seat::class);
    }

    // scopes

    // tickets ativos
    public function scopeActive(Builder $query){
        return $query->where('status', 'active');
    }

    // tickets utilizados
    public function scopeUsed(Builder $query){
        return $query->where('status', 'used');
    }

    // tickets cancelados
    public function scopeCancelled(Builder $query){
        return $query->where('status', 'cancelled');
    }

    // tickets validos (nao cancelados)
    public function scopeValid(Builder $query){
        return $query->where('status', ['active', 'used']);
    }

    // tickets de um usuário
    public function scopeForUser(Builder $query, int $userId){
        return $query->where('user_id', $userId);
    }

    // tickets de uma sessão
    public function scopeForScreening(Builder $query, int $screeningId){
        return $query->where('screening_id', $screeningId);
    }

    // tickets para sessões futuras (proximos)
    public function scopeForUpcomingScreenings(Builder $query){
        return $query->whereHas('screening', fn (Builder $query) => $query->upcoming());
    }

    // helpers
    public function isActive(){
        return $this->status === 'active';
    }

    public function isUsed(){
        return $this->status === 'used';
    }

    public function isCancelled(){
        return $this->status === 'cancelled';
    }

    // cancela o ticket
    public function cancel(){
        return $this->update(['status' => 'cancelled']);
    }
}
