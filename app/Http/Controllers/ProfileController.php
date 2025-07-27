<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function show(Request $request): View
    {
        return view('profile.show', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request)
    {
        try {
            $user = $request->user();
            $validated = $request->validated();

            // Handle avatar upload with image processing
            if ($request->hasFile('avatar')) {
                // Ensure avatars directory exists
                $this->ensureAvatarsDirectoryExists();
                
                // Delete old avatar if exists
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }

                // Store new avatar with optimized naming
                $file = $request->file('avatar');
                $fileName = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                $avatarPath = $file->storeAs('avatars', $fileName, 'public');
                
                // Pastikan path yang disimpan ke database benar
                // Jika path dimulai dengan 'avatars/' maka gunakan apa adanya
                // Jika tidak, tambahkan 'avatars/' di depan nama file
                if (str_starts_with($avatarPath, 'avatars/')) {
                    $validated['avatar'] = $avatarPath;
                } else {
                    $validated['avatar'] = 'avatars/' . $fileName;
                }
                
                // Log successful avatar upload
                \Log::info('Avatar uploaded successfully', [
                    'user_id' => $user->id,
                    'file_name' => $fileName,
                    'path' => $validated['avatar']
                ]);
                
                // Clear any cached data
                clearstatcache();
            }

            $user->fill($validated);

            if ($request->user()->isDirty('email')) {
                $request->user()->email_verified_at = null;
            }

            $user->save();

            // Check if this is an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Profile updated successfully!',
                    'user' => $user->fresh(),
                    'avatar_url' => $user->avatar ? asset('storage/' . $user->avatar) . '?v=' . time() : null,
                    'redirect' => route('profile.show')
                ]);
            }

            // Redirect to show profile instead of edit
            if ($request->has('view_profile') && $request->view_profile) {
                return Redirect::route('profile.show')->with('status', 'profile-updated');
            }

            return Redirect::route('profile.edit')->with('status', 'profile-updated');
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Profile update failed', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update profile: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Failed to update profile: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the user's profile information via AJAX.
     */
    public function updateAjax(ProfileUpdateRequest $request)
    {
        try {
            $user = $request->user();
            $validated = $request->validated();

            // Handle avatar upload with image processing
            if ($request->hasFile('avatar')) {
                try {
                    // Ensure avatars directory exists
                    $this->ensureAvatarsDirectoryExists();
                    
                    // Delete old avatar if exists
                    if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                        Storage::disk('public')->delete($user->avatar);
                    }

                    // Store new avatar with optimized naming
                    $file = $request->file('avatar');
                    $fileName = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $avatarPath = $file->storeAs('avatars', $fileName, 'public');
                    
                    // Pastikan path yang disimpan ke database benar
                    // Jika path dimulai dengan 'avatars/' maka gunakan apa adanya
                    // Jika tidak, tambahkan 'avatars/' di depan nama file
                    if (str_starts_with($avatarPath, 'avatars/')) {
                        $validated['avatar'] = $avatarPath;
                    } else {
                        $validated['avatar'] = 'avatars/' . $fileName;
                    }
                    
                    // Log successful avatar upload
                    \Log::info('Avatar uploaded via AJAX successfully', [
                        'user_id' => $user->id,
                        'file_name' => $fileName,
                        'path' => $validated['avatar']
                    ]);
                    
                    // Clear any cached data
                    clearstatcache();
                } catch (\Exception $e) {
                    // Log avatar upload error
                    \Log::error('Avatar upload failed', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                    
                    throw $e;
                }
            }

            $user->fill($validated);

            if ($request->user()->isDirty('email')) {
                $request->user()->email_verified_at = null;
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile berhasil diperbarui!',
                'user' => $user->fresh(), // Get fresh user data with updated info
                'avatar_url' => $user->avatar ? asset('storage/' . $user->avatar) . '?v=' . time() : null,
                'redirect' => route('profile.show')
            ]);

        } catch (\Exception $e) {
            // Log the error
            \Log::error('Profile AJAX update failed', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Ensure the avatars directory exists in public storage
     */
    private function ensureAvatarsDirectoryExists()
    {
        $avatarsPath = 'avatars';
        
        if (!Storage::disk('public')->exists($avatarsPath)) {
            Storage::disk('public')->makeDirectory($avatarsPath);
            \Log::info('Created avatars directory');
        }
        
        // Double check if the directory exists in the public folder
        if (!file_exists(public_path('storage/' . $avatarsPath))) {
            // Try to recreate the storage link
            try {
                \Artisan::call('storage:link');
                \Log::info('Storage link recreated');
            } catch (\Exception $e) {
                \Log::error('Failed to recreate storage link', [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
