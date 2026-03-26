<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ActivityLog;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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

        // Un usuario puede tener muchos assets
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    // Un usuario puede tener muchos registros de actividad
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
    // Comprueba si el usuario es admin
    
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Comprueba si el usuario es editor
    public function isEditor(): bool
    {
        return $this->role === 'editor';
    }

    // Comprueba si el usuario es viewer
    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }

    // Comprueba si el usuario tiene al menos un rol determinado
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }
}
