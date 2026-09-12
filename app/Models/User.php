<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_MEMBER = 'member';

    public const ROLE_EDITOR = 'editor';

    public const ROLE_VIEWER = 'viewer';

    /** Reviews and approves pending sales returns before they can be refunded. */
    public const ROLE_MANAGEMENT = 'management';

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function canAccessFilament(): bool
    {
        return $this->status === 'active' && in_array($this->role, [
            self::ROLE_ADMIN, self::ROLE_MEMBER, self::ROLE_EDITOR, self::ROLE_VIEWER, self::ROLE_MANAGEMENT,
        ], true);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isMember(): bool
    {
        return $this->role === self::ROLE_MEMBER;
    }

    public function isEditor(): bool
    {
        return $this->role === self::ROLE_EDITOR;
    }

    public function isViewer(): bool
    {
        return $this->role === self::ROLE_VIEWER;
    }

    public function isManagement(): bool
    {
        return $this->role === self::ROLE_MANAGEMENT;
    }

    /** Management reviews pending sales returns; Admin retains override authority everywhere. */
    public function canApproveReturns(): bool
    {
        return $this->isAdmin() || $this->isManagement();
    }

    /** CRM staff = anyone who can be assigned deals/leads/tasks (admins + members). */
    public function scopeCrmStaff($query)
    {
        return $query->whereIn('role', [self::ROLE_ADMIN, self::ROLE_MEMBER]);
    }

    public function canAccessCrm(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && in_array($this->role, [self::ROLE_ADMIN, self::ROLE_MEMBER, self::ROLE_MANAGEMENT], true);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /** Avatar column may hold a relative storage path or (legacy) a full URL. */
    public function getAvatarUrlAttribute(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        return Str::startsWith($this->avatar, ['http://', 'https://'])
            ? $this->avatar
            : Storage::url($this->avatar);
    }
}
