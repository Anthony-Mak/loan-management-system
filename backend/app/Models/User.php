<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Traits\AuditLogTrait;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, 
        Notifiable, 
        SoftDeletes, 
        AuditLogTrait;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'login_id',
        'employee_id',
        'username',
        'password',
        'role',
        'last_login',
        'password_change_required',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'password_change_required'
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
            'last_login' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'is_active' => 'boolean',
            'password_change_required' => 'boolean'
        ];
    }

    /**
     * Get the employee associated with the user.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    /**
     * Bootstrap model events and add custom logic.
     */
    protected static function booted()
    {
        // Automatically hash password during user creation
        static::creating(function ($user) {
            if (empty($user->password)) {
                $user->password = Hash::make(Str::random(16));
            }

            // Ensure password is hashed
            if (Hash::needsRehash($user->password)) {
                $user->password = Hash::make($user->password);
            }

            // Set default values
            $user->is_active = $user->is_active ?? true;
            $user->password_change_required = $user->password_change_required ?? true;
        });

        // Validate password changes
        static::updating(function ($user) {
            // Check if password is being changed
            if ($user->isDirty('password')) {
                // Prevent reusing the same password
                if (Hash::check($user->password, $user->getOriginal('password'))) {
                    throw ValidationException::withMessages([
                        'password' => 'New password must be different from current password'
                    ]);
                }

                // Only set password_change_required to true if this is an admin reset
                 // Don't override it if it's being explicitly set to false in a password change operation
                if (!$user->isDirty('password_change_required')) {
                    $user->password_change_required = true;
                }
            }

            // Log user status changes
            if ($user->isDirty('is_active')) {
                $user->logCustomAction(
                    'update_user_status', 
                    "User account status changed", 
                    [
                        'previous_status' => $user->getOriginal('is_active'),
                        'new_status' => $user->is_active
                    ]
                );
            }
        });

        // Log login attempts
        static::retrieved(function ($user) {
            if (request()->routeIs('login')) {
                $user->update(['last_login' => now()]);
                
                AuditLog::log(
                    'user_login', 
                    self::class, 
                    $user->user_id, 
                    'User logged in', 
                    null, 
                    ['username' => $user->username, 'ip_address' => request()->ip()]
                );
            }
        });
    }

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is an HR representative.
     *
     * @return bool
     */
    public function isHR(): bool
    {
        return $this->role === 'hr';
    }

    /**
     * Custom method to reset password with audit logging.
     *
     * @param string $newPassword
     * @return void
     */
    public function resetPassword(string $newPassword)
    {
        $this->password = $newPassword;
        $this->password_change_required = false;
        $this->save();

        $this->logCustomAction(
            'password_reset', 
            "Password reset for user", 
            ['username' => $this->username]
        );
    }
}