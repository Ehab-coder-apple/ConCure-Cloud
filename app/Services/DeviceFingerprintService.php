<?php

namespace App\Services;

use Illuminate\Http\Request;

class DeviceFingerprintService
{
    /**
     * Generate a device fingerprint from request data
     * Fingerprint = hash of user agent + IP address
     */
    public static function generate(Request $request): string
    {
        $userAgent = $request->header('User-Agent', '');
        $ipAddress = $request->ip() ?? '';
        
        // Create a consistent hash from user agent and IP
        $fingerprint = hash('sha256', $userAgent . '|' . $ipAddress);
        
        return $fingerprint;
    }

    /**
     * Parse browser name from user agent
     */
    public static function parseBrowser(string $userAgent): string
    {
        if (preg_match('/Chrome\//', $userAgent)) {
            return 'Chrome';
        } elseif (preg_match('/Safari\//', $userAgent) && !preg_match('/Chrome\//', $userAgent)) {
            return 'Safari';
        } elseif (preg_match('/Firefox\//', $userAgent)) {
            return 'Firefox';
        } elseif (preg_match('/Edge\//', $userAgent)) {
            return 'Edge';
        } elseif (preg_match('/Trident\//', $userAgent)) {
            return 'Internet Explorer';
        } else {
            return 'Unknown';
        }
    }

    /**
     * Parse OS name from user agent
     */
    public static function parseOS(string $userAgent): string
    {
        if (preg_match('/Windows/', $userAgent)) {
            return 'Windows';
        } elseif (preg_match('/Macintosh/', $userAgent)) {
            return 'macOS';
        } elseif (preg_match('/Linux/', $userAgent)) {
            return 'Linux';
        } elseif (preg_match('/iPhone/', $userAgent)) {
            return 'iOS';
        } elseif (preg_match('/Android/', $userAgent)) {
            return 'Android';
        } else {
            return 'Unknown';
        }
    }

    /**
     * Get full device info from request
     */
    public static function getDeviceInfo(Request $request): array
    {
        $userAgent = $request->header('User-Agent', '');
        $ipAddress = $request->ip() ?? '';
        
        return [
            'fingerprint' => self::generate($request),
            'user_agent' => $userAgent,
            'ip_address' => $ipAddress,
            'browser' => self::parseBrowser($userAgent),
            'os' => self::parseOS($userAgent),
        ];
    }
}
