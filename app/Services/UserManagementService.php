<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * UserManagementService
 *
 * Handles all business logic related to user management
 * Provides CRUD operations, filtering, and statistics for users
 */
class UserManagementService
{
    /**
     * Get users with filters and pagination
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUsers(array $filters = [], int $perPage = 15)
    {
        $query = User::query();

        // Apply role filter
        if (!empty($filters['role']) && $filters['role'] !== 'all') {
            $query->where('role', $filters['role']);
        }

        // Apply status filter
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            if ($filters['status'] === 'active') {
                $query->where('is_active', 1);
            } elseif ($filters['status'] === 'inactive') {
                $query->where('is_active', 0);
            }
        }

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('nip', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    /**
     * Get user statistics
     *
     * @return array
     */
    public function getUserStatistics(): array
    {
        try {
            return [
                'total' => User::count(),
                'active' => User::where('is_active', true)->count(),
                'pimpinan' => User::whereIn('role', ['kasubbag', 'sekretaris', 'ppk'])->count(),
                'inactive' => User::where('is_active', false)->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting user statistics: ' . $e->getMessage());

            return [
                'total' => 0,
                'active' => 0,
                'pimpinan' => 0,
                'inactive' => 0,
            ];
        }
    }

    /**
     * Create a new user
     *
     * @param array $userData
     * @return User
     * @throws \Exception
     */
    public function createUser(array $userData): User
    {
        try {
            // Hash password
            $userData['password'] = Hash::make($userData['password']);
            $userData['is_active'] = true;

            $user = User::create($userData);

            // Assign role using Spatie Permission if available
            if (method_exists($user, 'assignRole')) {
                $user->assignRole($userData['role']);
            }

            Log::info("User created successfully: {$user->name}");

            return $user;

        } catch (\Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            throw new \Exception('Gagal membuat user: ' . $e->getMessage());
        }
    }

    /**
     * Update user status (activate/deactivate)
     *
     * @param User $user
     * @return bool
     * @throws \Exception
     */
    public function toggleUserStatus(User $user): bool
    {
        try {
            $oldStatus = $user->is_active;
            $user->update(['is_active' => !$user->is_active]);

            $status = $user->is_active ? 'activated' : 'deactivated';
            Log::info("User {$user->name} status changed from {$oldStatus} to {$user->is_active}");

            return true;

        } catch (\Exception $e) {
            Log::error('Error toggling user status: ' . $e->getMessage());
            throw new \Exception('Gagal mengubah status user: ' . $e->getMessage());
        }
    }

    /**
     * Update user information
     *
     * @param User $user
     * @param array $userData
     * @return User
     * @throws \Exception
     */
    public function updateUser(User $user, array $userData): User
    {
        try {
            // Hash password if provided
            if (!empty($userData['password'])) {
                $userData['password'] = Hash::make($userData['password']);
            } else {
                unset($userData['password']);
            }

            $user->update($userData);

            // Update role if changed
            if (!empty($userData['role']) && method_exists($user, 'syncRoles')) {
                $user->syncRoles([$userData['role']]);
            }

            Log::info("User updated successfully: {$user->name}");

            return $user;

        } catch (\Exception $e) {
            Log::error('Error updating user: ' . $e->getMessage());
            throw new \Exception('Gagal mengupdate user: ' . $e->getMessage());
        }
    }

    /**
     * Delete a user
     *
     * @param User $user
     * @return bool
     * @throws \Exception
     */
    public function deleteUser(User $user): bool
    {
        try {
            // Check if user can be deleted (e.g., no related travel requests)
            $travelRequestsCount = $user->travelRequests()->count();

            if ($travelRequestsCount > 0) {
                throw new \Exception('User tidak dapat dihapus karena memiliki data SPPD terkait.');
            }

            $userName = $user->name;
            $user->delete();

            Log::info("User deleted successfully: {$userName}");

            return true;

        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            throw new \Exception('Gagal menghapus user: ' . $e->getMessage());
        }
    }

    /**
     * Get users for export with filters
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUsersForExport(array $filters = [])
    {
        $query = User::query();

        // Apply same filters as in getUsers
        if (!empty($filters['role']) && $filters['role'] !== 'all') {
            $query->where('role', $filters['role']);
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            if ($filters['status'] === 'active') {
                $query->where('is_active', 1);
            } elseif ($filters['status'] === 'inactive') {
                $query->where('is_active', 0);
            }
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('nip', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Validate user data for creation/update
     *
     * @param array $data
     * @param User|null $user (for update)
     * @return array
     */
    public function validateUserData(array $data, ?User $user = null): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'nip' => 'required|string|max:18|regex:/^[0-9]{18}$/', // NIP format: 18 digit angka (YYYYMMDDNNNNNNNNN)
            'role' => 'required|in:staff,kasubbag,sekretaris,ppk',
            'jabatan' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'pangkat' => 'nullable|string|max:100',
            'golongan' => 'nullable|string|max:20',
            'unit_kerja' => 'nullable|string|max:255',
        ];

        // Email validation
        if ($user) {
            // For update, email unique except current user
            $rules['email'] = 'required|email|unique:users,email,' . $user->id;
            $rules['nip'] = 'required|string|max:18|regex:/^[0-9]{18}$/|unique:users,nip,' . $user->id;
            $rules['password'] = 'nullable|string|min:8|confirmed';
        } else {
            // For creation, email must be unique
            $rules['email'] = 'required|email|unique:users';
            $rules['nip'] = 'required|string|max:18|regex:/^[0-9]{18}$/|unique:users';
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        return $rules;
    }

    /**
     * Get available user roles
     *
     * @return array
     */
    public function getAvailableRoles(): array
    {
        return [
            'staff' => 'staff',
            'kasubbag' => 'kasubbag',
            'sekretaris' => 'sekretaris',
            'ppk' => 'ppk',
        ];
    }

    /**
     * Search users by term
     *
     * @param string $term
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchUsers(string $term, int $limit = 10)
    {
        return User::where('name', 'LIKE', "%{$term}%")
            ->orWhere('email', 'LIKE', "%{$term}%")
            ->orWhere('nip', 'LIKE', "%{$term}%")
            ->limit($limit)
            ->get();
    }
}
