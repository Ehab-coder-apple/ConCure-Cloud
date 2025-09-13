<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Clinic;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Show the master dashboard.
     */
    public function index()
    {
        // Get system statistics
        $stats = $this->getSystemStats();
        
        // Get recent activities
        $recentClinics = $this->getRecentClinics();
        $recentUsers = $this->getRecentUsers();
        
        // Get growth data for charts
        $growthData = $this->getGrowthData();

        return view('master.dashboard', compact('stats', 'recentClinics', 'recentUsers', 'growthData'));
    }

    /**
     * Get pending clinic registrations for approval.
     */
    public function getPendingRegistrations()
    {
        $pendingClinics = Clinic::where('is_active', false)
            ->with(['users' => function($q) {
                $q->where('role', 'admin');
            }])
            ->latest()
            ->get();

        return response()->json($pendingClinics);
    }

    /**
     * Approve a clinic registration.
     */
    public function approveClinic(Clinic $clinic)
    {
        if ($clinic->is_active) {
            return response()->json(['error' => 'Clinic is already active'], 400);
        }

        DB::beginTransaction();
        try {
            // Activate clinic
            $clinic->update([
                'is_active' => true,
                'activated_at' => now(),
            ]);

            // Activate admin users
            $clinic->users()->where('role', 'admin')->update([
                'is_active' => true,
                'activated_at' => now(),
            ]);

            DB::commit();

            // TODO: Send approval email to clinic admin
            \Log::info('Clinic approved', [
                'clinic_id' => $clinic->id,
                'clinic_name' => $clinic->name,
                'approved_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Clinic approved successfully',
                'clinic' => $clinic->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to approve clinic: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reject a clinic registration.
     */
    public function rejectClinic(Request $request, Clinic $clinic)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($clinic->is_active) {
            return response()->json(['error' => 'Cannot reject an active clinic'], 400);
        }

        DB::beginTransaction();
        try {
            // Update clinic settings with rejection reason
            $settings = json_decode($clinic->settings, true) ?? [];
            $settings['rejection_reason'] = $request->reason;
            $settings['rejected_at'] = now()->toISOString();
            $settings['rejected_by'] = auth()->id();

            $clinic->update([
                'settings' => json_encode($settings),
            ]);

            DB::commit();

            // TODO: Send rejection email to clinic admin
            \Log::info('Clinic rejected', [
                'clinic_id' => $clinic->id,
                'clinic_name' => $clinic->name,
                'reason' => $request->reason,
                'rejected_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Clinic registration rejected',
                'clinic' => $clinic->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Failed to reject clinic: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get system-wide statistics.
     */
    private function getSystemStats()
    {
        return [
            'total_clinics' => Clinic::count(),
            'active_clinics' => Clinic::where('is_active', true)->count(),
            'pending_clinics' => Clinic::where('is_active', false)->count(),
            'total_users' => User::where('role', '!=', 'super_admin')->count(),
            'active_users' => User::where('role', '!=', 'super_admin')->where('is_active', true)->count(),
            'total_patients' => Patient::count(),
            'total_prescriptions' => Prescription::count(),
            'total_appointments' => Appointment::count(),
            'monthly_new_clinics' => Clinic::whereMonth('created_at', now()->month)->count(),
            'monthly_new_users' => User::where('role', '!=', 'super_admin')->whereMonth('created_at', now()->month)->count(),
        ];
    }

    /**
     * Get recent clinics.
     */
    private function getRecentClinics()
    {
        return Clinic::with(['users' => function($query) {
            $query->where('role', 'admin')->first();
        }])
        ->latest()
        ->limit(10)
        ->get();
    }

    /**
     * Get recent users.
     */
    private function getRecentUsers()
    {
        return User::with('clinic')
            ->where('role', '!=', 'super_admin')
            ->latest()
            ->limit(10)
            ->get();
    }

    /**
     * Get growth data for charts.
     */
    private function getGrowthData()
    {
        $months = [];
        $clinicData = [];
        $userData = [];
        $patientData = [];

        // Get data for last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            $clinicData[] = Clinic::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
                
            $userData[] = User::where('role', '!=', 'super_admin')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
                
            $patientData[] = Patient::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
        }

        return [
            'months' => $months,
            'clinics' => $clinicData,
            'users' => $userData,
            'patients' => $patientData,
        ];
    }

    /**
     * Get clinic distribution by status.
     */
    public function getClinicStatusData()
    {
        $data = [
            'active' => Clinic::where('is_active', true)->count(),
            'inactive' => Clinic::where('is_active', false)->count(),
        ];

        return response()->json($data);
    }

    /**
     * Get user distribution by role.
     */
    public function getUserRoleData()
    {
        $data = User::where('role', '!=', 'super_admin')
            ->select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->pluck('count', 'role')
            ->toArray();

        return response()->json($data);
    }

    /**
     * Get system health status.
     */
    public function getSystemHealth()
    {
        $health = [
            'database' => $this->checkDatabaseHealth(),
            'storage' => $this->checkStorageHealth(),
            'cache' => $this->checkCacheHealth(),
        ];

        return response()->json($health);
    }

    /**
     * Check database health.
     */
    private function checkDatabaseHealth()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'healthy', 'message' => 'Database connection is working'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Database connection failed'];
        }
    }

    /**
     * Check storage health.
     */
    private function checkStorageHealth()
    {
        try {
            $diskSpace = disk_free_space(storage_path());
            $totalSpace = disk_total_space(storage_path());
            $usedPercentage = (($totalSpace - $diskSpace) / $totalSpace) * 100;
            
            if ($usedPercentage > 90) {
                return ['status' => 'warning', 'message' => 'Storage usage is high (' . round($usedPercentage, 1) . '%)'];
            }
            
            return ['status' => 'healthy', 'message' => 'Storage usage is normal (' . round($usedPercentage, 1) . '%)'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Unable to check storage'];
        }
    }

    /**
     * Check cache health.
     */
    private function checkCacheHealth()
    {
        try {
            cache()->put('health_check', 'test', 60);
            $value = cache()->get('health_check');
            
            if ($value === 'test') {
                return ['status' => 'healthy', 'message' => 'Cache is working'];
            }
            
            return ['status' => 'warning', 'message' => 'Cache test failed'];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Cache connection failed'];
        }
    }
}
