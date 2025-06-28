<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'stripe_customer_id',
        'name',
        'email',
        'phone',
        'address',
        'metadata',
        'created_at_stripe',
    ];

    protected $casts = [
        'address' => 'array',
        'metadata' => 'array',
        'created_at_stripe' => 'datetime',
    ];

    /**
     * Get all payments for this customer
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get successful payments only
     */
    public function successfulPayments(): HasMany
    {
        return $this->payments()->where('status', 'succeeded');
    }

    /**
     * Get total amount paid by customer
     */
    public function getTotalPaidAttribute(): float
    {
        return $this->successfulPayments()->sum('amount');
    }

    /**
     * Get customer's payment count
     */
    public function getPaymentCountAttribute(): int
    {
        return $this->successfulPayments()->count();
    }

    /**
     * Find customer by email
     */
    public static function findByEmail(string $email): ?self
    {
        return static::where('email', $email)->first();
    }

    /**
     * Find customer by Stripe ID
     */
    public static function findByStripeId(string $stripeId): ?self
    {
        return static::where('stripe_customer_id', $stripeId)->first();
    }
}
