<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
      * The attributes that are mass assignable.
      *
      * @var list<string>
      */
    protected $fillable = [
        'name',
        'username',
        'password',
        'role_id',
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
            'password' => 'hashed',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Check if user has a specific role by slug.
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->role && $this->role->slug === $roleSlug;
    }

    /**
     * Check if user has a specific permission by slug.
     */
    public function hasPermission(string $permissionSlug): bool
    {
        if (!$this->role) {
            return false;
        }
        
        // Super Admin gets all permissions
        if ($this->role->slug === 'super-admin') {
            return true;
        }

        return $this->role->permissions()->where('slug', $permissionSlug)->exists();
    }
}
