<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_amount',
        'payment_method',
        'payment_status',
        'paid_at',
    ];

    protected function casts()
    {
        return [
            'total_amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    // relacionamentos

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    // scopes

    // pagamentos especificos
    public function scopeWithPaymentStatus(Builder $query, string $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }

    // pagos
    public function scopePaid(Builder $query)
    {
        return $query->where('payment_status', 'paid');
    }

    // pendentes
    public function scopePending(Builder $query)
    {
        return $query->where('payment_status', 'pending');
    }

    // cancelados
    public function scopeCanceled(Builder $query)
    {
        return $query->where('payment_status', 'cancelled');
    }

    // reembolsados
    public function scopeRefunded(Builder $query)
    {
        return $query->where('payment_status', 'refunded');
    }

    // por usuário
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    // por metodo de pagamento
    public function scopeByPaymentMethod(Builder $query, string $paymentMethod)
    {
        return $query->where('payment_method', $paymentMethod);
    }

    // pedidos criados num periodo especifico
    public function scopeCreatedBetween(Builder $query, string $startDate, string $endDate)
    {
        return $query->whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ]);
    }

    // pedidos da ultima semana
    public function scopeLastWeek(Builder $query)
    {
        return $query->where('created_at', '>', Carbon::now()->subWeek());
    }

    // pedidos do ultimo mes
    public function scopeLastMonth(Builder $query)
    {
        return $query->where('created_at', '>=', Carbon::now()->subMonth());
    }

    // helpers

    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    public function isPending()
    {
        return $this->payment_status === 'pending';
    }

    public function isCanceled()
    {
        return $this->payment_status === 'cancelled';
    }

    public function isRefunded()
    {
        return $this->payment_status === 'refunded';
    }

    // marca pedido como pago
    public function markAsPaid(string $paymentMethod) {
        return $this->update([
            'payment_status' => 'paid',
            'payment_method' => $paymentMethod,
            'paid_at' => Carbon::now()
        ]);
    }

    // marca o pedido como cancelado e cancela todos os tickets.
    public function markAsCancelled() {
        $this->tickets()->update(['status' => 'cancelled']);
        return $this->update(['payment_status' => 'cancelled']);
    }
}
