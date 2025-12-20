<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class GroupMember extends Model
{
    protected $table = 'group_members';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'group_id',
        'user_id',
        'role',
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

    /**
     * Get the expense group.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ExpenseGroup::class, 'group_id');
    }

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

