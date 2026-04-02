<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use App\Models\SavedPaymentMethod;
use App\Models\ShippingAddress;
use App\Models\CartReminder;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'is_admin', 'last_login_at', 'stripe_customer_id', 'preferred_locale'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_admin' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the orders for the user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the saved shipping addresses for the user.
     */
    public function shippingAddresses(): HasMany
    {
        return $this->hasMany(ShippingAddress::class)->orderByDesc('is_default')->orderBy('label');
    }

    /**
     * Get the saved payment methods for the user.
     */
    public function savedPaymentMethods(): HasMany
    {
        return $this->hasMany(SavedPaymentMethod::class)->orderByDesc('is_default')->orderBy('id');
    }

    public function cartReminders(): HasMany
    {
        return $this->hasMany(CartReminder::class);
    }
}
