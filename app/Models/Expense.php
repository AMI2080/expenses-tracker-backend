<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Expense extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'amount',
        'description',
        'center_id',
        'user_id',
        'spent_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'spent_at' => 'datetime',
        ];
    }

    /**
     * Get the expense center this expense belongs to.
     */
    public function center(): BelongsTo
    {
        return $this->belongsTo(ExpenseCenter::class, 'center_id');
    }

    /**
     * Get the user who created this expense.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
