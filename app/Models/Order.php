<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_id',
        'user_id',
        'gold_amount',
        'price',
        'status',
        'payment_type',
        'payment_details',
        'paid_at',
    ];

    protected $casts = [
        'payment_details' => 'array',
        'paid_at' => 'datetime',
        'price' => 'decimal:2',
    ];

    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if order is paid.
     */
    public function isPaid()
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Check if order is pending.
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Mark order as paid.
     */
    public function markAsPaid($paymentType = null, $paymentDetails = null)
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'payment_type' => $paymentType,
            'payment_details' => $paymentDetails,
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark order as failed.
     */
    public function markAsFailed()
    {
        $this->update([
            'status' => self::STATUS_FAILED,
        ]);
    }

    /**
     * Cancel order.
     */
    public function cancel()
    {
        if ($this->isPending()) {
            $this->update([
                'status' => self::STATUS_CANCELLED,
            ]);
            return true;
        }
        return false;
    }
}
