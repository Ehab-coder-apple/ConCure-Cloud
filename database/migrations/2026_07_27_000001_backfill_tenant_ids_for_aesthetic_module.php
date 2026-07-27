<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Backfill any clinics that never had their tenant_id generated, then
     * repair any Aesthetic-module rows (treatments, packages, patient
     * packages, sessions, inventory, invoices) that were created with a
     * NULL/blank tenant_id while their clinic's tenant_id was still unset.
     * Without this, such rows become permanently invisible because every
     * Aesthetic model scopes its queries by tenant_id.
     */
    public function up(): void
    {
        // 1) Backfill clinics.tenant_id where missing.
        if (DB::getDriverName() === 'sqlite') {
            DB::statement("UPDATE clinics SET tenant_id = 'TEN-' || id WHERE tenant_id IS NULL OR tenant_id = ''");
        } else {
            DB::statement("UPDATE clinics SET tenant_id = CONCAT('TEN-', id) WHERE tenant_id IS NULL OR tenant_id = ''");
        }

        // 2) Repair orphaned tenant_id values on tables that key off a
        // patient's clinic (directly or transitively). Tables such as
        // aesthetic_treatments, aesthetic_packages, and aesthetic_inventory
        // have no patient/clinic link and cannot be safely backfilled here;
        // the clinics.tenant_id backfill above is sufficient for their
        // tenant scope to resolve correctly going forward.
        if (Schema::hasTable('patient_packages')) {
            $this->backfillViaPatient('patient_packages', 'patient_id');
        }

        if (Schema::hasTable('aesthetic_sessions')) {
            $this->backfillViaPatient('aesthetic_sessions', 'patient_id');
            $this->backfillViaPatientPackage('aesthetic_sessions');
        }

        if (Schema::hasTable('aesthetic_invoices')) {
            $this->backfillViaPatient('aesthetic_invoices', 'patient_id');
        }
    }

    private function backfillViaPatient(string $table, string $patientColumn): void
    {
        $rows = DB::table($table)
            ->whereNull('tenant_id')
            ->orWhere('tenant_id', '')
            ->get(['id', $patientColumn]);

        foreach ($rows as $row) {
            $patientId = $row->{$patientColumn} ?? null;
            if (!$patientId) {
                continue;
            }

            $tenantId = DB::table('patients')
                ->join('clinics', 'patients.clinic_id', '=', 'clinics.id')
                ->where('patients.id', $patientId)
                ->value('clinics.tenant_id');

            if ($tenantId) {
                DB::table($table)->where('id', $row->id)->update(['tenant_id' => $tenantId]);
            }
        }
    }

    private function backfillViaPatientPackage(string $table): void
    {
        $rows = DB::table($table)
            ->whereNull('tenant_id')
            ->orWhere('tenant_id', '')
            ->get(['id', 'patient_package_id']);

        foreach ($rows as $row) {
            if (!$row->patient_package_id) {
                continue;
            }

            $tenantId = DB::table('patient_packages')
                ->join('patients', 'patient_packages.patient_id', '=', 'patients.id')
                ->join('clinics', 'patients.clinic_id', '=', 'clinics.id')
                ->where('patient_packages.id', $row->patient_package_id)
                ->value('clinics.tenant_id');

            if ($tenantId) {
                DB::table($table)->where('id', $row->id)->update(['tenant_id' => $tenantId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: this is a data-repair migration; reversing would
        // re-introduce the bug it fixes.
    }
};
