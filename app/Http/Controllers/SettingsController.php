<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    public function index()
    {
        // Ambil pengaturan dari database atau cache
        $settings = $this->getSettings();
        
        // Ambil data user untuk personalisasi pengaturan
        $user = Auth::user();
        $userSettings = $user->settings ?? [];
        
        return view('settings.pengaturan-sistem', [
            'settings' => $settings,
            'userSettings' => $userSettings,
            'user' => $user
        ]);
    }
    
    /**
     * Simpan pengaturan sistem
     */
    public function saveSettings(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'nullable|string|max:255',
            'notification_enabled' => 'nullable|boolean',
            'theme' => 'nullable|string|in:light,dark,system',
            'language' => 'nullable|string|in:id,en',
        ]);
        
        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
        
        // Clear cache
        Cache::forget('system_settings');
        
        return redirect()->route('settings.index')
            ->with('success', 'Pengaturan berhasil disimpan');
    }
    
    /**
     * Simpan pengaturan user
     */
    public function saveUserSettings(Request $request)
    {
        $user = Auth::user();
        $settings = $request->validate([
            'notification_preference' => 'nullable|string|in:all,important,none',
            'display_mode' => 'nullable|string|in:light,dark,system',
        ]);
        
        $user->settings = $settings;
        $user->save();
        
        return redirect()->route('settings.index')
            ->with('success', 'Pengaturan pengguna berhasil disimpan');
    }
    
    /**
     * Ambil pengaturan sistem dari cache atau database
     */
    private function getSettings()
    {
        return Cache::remember('system_settings', 60 * 24, function () {
            $settings = Setting::all()->pluck('value', 'key')->toArray();
            
            // Default settings jika belum ada di database
            $defaults = [
                'site_name' => 'SPPD KPU Kabupaten Cirebon',
                'notification_enabled' => true,
                'theme' => 'light',
                'language' => 'id',
            ];
            
            return array_merge($defaults, $settings);
        });
    }
}
