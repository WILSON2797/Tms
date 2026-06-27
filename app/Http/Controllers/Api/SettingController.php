<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Public API for mobile app version check.
     */
    public function getAppVersion()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'latest_version' => Setting::get('mobile_latest_version', '1.0.0'),
                'minimum_version' => Setting::get('mobile_minimum_version', '1.0.0'),
                'download_url' => Setting::get('mobile_download_url', ''),
            ]
        ]);
    }

    /**
     * Admin API to retrieve current settings.
     */
    public function getMobileSettings()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'mobile_latest_version' => Setting::get('mobile_latest_version', '1.0.0'),
                'mobile_minimum_version' => Setting::get('mobile_minimum_version', '1.0.0'),
                'mobile_download_url' => Setting::get('mobile_download_url', ''),
            ]
        ]);
    }

    /**
     * Admin API to update settings.
     */
    public function updateMobileSettings(Request $request)
    {
        $validated = $request->validate([
            'mobile_latest_version' => 'required|string',
            'mobile_minimum_version' => 'required|string',
            'mobile_download_url' => 'required|url',
        ]);

        Setting::set('mobile_latest_version', $validated['mobile_latest_version']);
        Setting::set('mobile_minimum_version', $validated['mobile_minimum_version']);
        Setting::set('mobile_download_url', $validated['mobile_download_url']);

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan versi aplikasi berhasil diperbarui',
            'data' => $validated
        ]);
    }
}
