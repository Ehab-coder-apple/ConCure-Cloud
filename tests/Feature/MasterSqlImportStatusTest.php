<?php

namespace Tests\Feature;

use Tests\TestCase;

class MasterSqlImportStatusTest extends TestCase
{
    private const JOB_ID = 'imp_teststatus123';

    protected function setUp(): void
    {
        parent::setUp();

        $directory = dirname($this->statusFilePath());
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
    }

    protected function tearDown(): void
    {
        @unlink($this->statusFilePath());

        parent::tearDown();
    }

    public function test_it_returns_import_status_for_a_valid_token(): void
    {
        $token = 'valid-status-token';

        file_put_contents($this->statusFilePath(), json_encode([
            'job_id' => self::JOB_ID,
            'status_token_hash' => hash('sha256', $token),
            'status' => 'completed',
            'message' => 'Import completed successfully in 1.2s.',
            'elapsed' => 1.2,
            'updated_at' => now()->toIso8601String(),
        ]));

        $this->getJson('/api/master/import-sql/status?job_id=' . self::JOB_ID . '&token=' . $token)
            ->assertOk()
            ->assertJson([
                'job_id' => self::JOB_ID,
                'status' => 'completed',
                'message' => 'Import completed successfully in 1.2s.',
                'elapsed' => 1.2,
            ])
            ->assertJsonMissing(['status_token_hash' => hash('sha256', $token)]);
    }

    public function test_it_rejects_an_invalid_status_token(): void
    {
        file_put_contents($this->statusFilePath(), json_encode([
            'job_id' => self::JOB_ID,
            'status_token_hash' => hash('sha256', 'expected-token'),
            'status' => 'running',
            'message' => 'Import is running...',
            'updated_at' => now()->toIso8601String(),
        ]));

        $this->getJson('/api/master/import-sql/status?job_id=' . self::JOB_ID . '&token=wrong-token')
            ->assertForbidden()
            ->assertJson([
                'status' => 'forbidden',
                'message' => 'Invalid status token.',
            ]);
    }

    public function test_it_rejects_invalid_job_ids(): void
    {
        $this->getJson('/api/master/import-sql/status?job_id=bad/job&id=1&token=test')
            ->assertStatus(400)
            ->assertJson([
                'status' => 'error',
                'message' => 'Invalid job ID.',
            ]);
    }

    private function statusFilePath(): string
    {
        return storage_path('app/sql_import_' . self::JOB_ID . '.json');
    }
}