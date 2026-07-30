<?php

namespace Modules\Negotiation\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Modules\Advertisements\Models\Advertisement;
use Modules\Negotiation\Enums\NegotiationStatus;

class Negotiation extends Model
{
    use HasFactory;

    protected $table = 'negotiations';

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }

            // Populate legacy columns if present in DB schema
            try {
                if (Schema::hasColumn('negotiations', 'initiator_user_id') && empty($model->initiator_user_id)) {
                    $model->initiator_user_id = $model->buyer_id ?? $model->seller_id ?? null;
                }
                if (Schema::hasColumn('negotiations', 'counterparty_user_id') && empty($model->counterparty_user_id)) {
                    $model->counterparty_user_id = $model->seller_id ?? $model->buyer_id ?? null;
                }

                if (Schema::hasColumn('negotiations', 'proposed_price') && empty($model->proposed_price)) {
                    $model->proposed_price = 0;
                }
            } catch (\Exception $e) {
                // Schema may not be available in certain test bootstrap phases; ignore silently
            }
        });
    }

    protected $fillable = [
        'uuid',
        'advertisement_id',
        'buyer_id',
        'seller_id',
        'initiator_user_id',
        'counterparty_user_id',
        'conversation_id',
        'status',
        'started_at',
        'accepted_at',
        'cancelled_at',
        'expired_at',
        'closed_at',
        'selected_offer_id',
        'order_id',
        'proposed_price',
        'currency',
        'agreed_price',
    ];

    protected $casts = [
        'status' => NegotiationStatus::class,
        'started_at' => 'datetime',
        'accepted_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'expired_at' => 'datetime',
        'closed_at' => 'datetime',
        'agreed_price' => 'decimal:2',
    ];

    public function scopeForUser(Builder $query, User $user): Builder
    {
        if (Schema::hasColumn($this->getTable(), 'buyer_id') && Schema::hasColumn($this->getTable(), 'seller_id')) {
            return $query->where(function (Builder $query) use ($user) {
                $query->where('buyer_id', $user->id)
                    ->orWhere('seller_id', $user->id);
            });
        }

        if (Schema::hasColumn($this->getTable(), 'initiator_user_id') && Schema::hasColumn($this->getTable(), 'counterparty_user_id')) {
            return $query->where(function (Builder $query) use ($user) {
                $query->where('initiator_user_id', $user->id)
                    ->orWhere('counterparty_user_id', $user->id);
            });
        }

        return $query->where(function (Builder $query) use ($user) {
            $query->where('buyer_id', $user->id)
                ->orWhere('seller_id', $user->id);
        });
    }

    public function advertisement(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Advertisement::class, 'advertisement_id');
    }

    public function buyer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function offers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(NegotiationOffer::class, 'negotiation_id');
    }

    public function histories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(NegotiationHistory::class, 'negotiation_id');
    }
}
