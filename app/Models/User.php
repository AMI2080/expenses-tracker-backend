<?php

namespace App\Models;

use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public $incrementing = false;
    protected $keyType = 'string';

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
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'is_approved',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_approved' => 'boolean',
        ];
    }

    /**
     * Get the expense groups owned by the user.
     */
    public function ownedExpenseGroups()
    {
        return $this->hasMany(ExpenseGroup::class, 'owner_id');
    }

    /**
     * Get the group memberships for the user.
     */
    public function groupMemberships()
    {
        return $this->hasMany(GroupMember::class);
    }

    /**
     * Get the expense groups the user is a member of.
     */
    public function groups()
    {
        return $this->belongsToMany(ExpenseGroup::class, 'group_members', 'user_id', 'group_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the expense centers the user has access to (through group membership).
     */
    public function accessibleCenters()
    {
        // Get all groups user is a member of or owns
        $groupIds = $this->groups()->pluck('expense_groups.id')
            ->merge($this->ownedExpenseGroups()->pluck('id'));

        // Get all centers in those groups
        return ExpenseCenter::whereIn('group_id', $groupIds);
    }

    /**
     * Get all expenses created by the user.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }

    /**
     * Check if the user is approved.
     */
    public function isApproved(): bool
    {
        return $this->is_approved === true;
    }

    /**
     * Check if the user can login (email verified and approved).
     */
    public function canLogin(): bool
    {
        return $this->hasVerifiedEmail() && $this->isApproved();
    }

    /**
     * Send the email verification notification.
     * Override to use queued notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }
}
