<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy 
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool 
    {
        // Only admins can view all users
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool 
    {
        // Admins can view any user
        // Users can view their own profile
        return $user->role === 'admin' || $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool 
    {
        // Only admins can create users
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool 
    {
        // Admins can update any user
        // Users can update their own profile
        return $user->role === 'admin' || $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool 
    {
        // Only admins can delete users, and not their own account
        return $user->role === 'admin' && $user->id !== $model->id;
    }
}