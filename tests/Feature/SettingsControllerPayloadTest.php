<?php

namespace Tests\Feature;

use App\Http\Controllers\Master\SettingsController;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use ReflectionMethod;
use Tests\TestCase;

class SettingsControllerPayloadTest extends TestCase
{
    public function test_it_extracts_gzip_encoded_sql_payload(): void
    {
        $sql = "INSERT INTO patients (id, name) VALUES (1, 'Test');";
        $compressed = gzencode($sql, 9);

        $request = Request::create('/master/settings/import-sql?clinic_id=1', 'POST', ['clinic_id' => 1], [], [], [
            'CONTENT_TYPE' => 'application/octet-stream',
            'CONTENT_LENGTH' => strlen($compressed),
            'HTTP_X_SQL_IMPORT_ENCODING' => 'gzip',
            'HTTP_X_SQL_FILE_NAME' => rawurlencode('db-backup.sql'),
        ], $compressed);

        $payload = $this->invokeControllerMethod(new SettingsController(), 'extractSqlPayload', [$request]);

        $this->assertSame($sql, $payload['sql']);
        $this->assertSame('db-backup.sql', $payload['original_name']);
        $this->assertSame(strlen($sql), $payload['file_size']);
        $this->assertSame('gzip-body', $payload['transport']);
    }

    public function test_it_rejects_non_sql_filename_for_gzip_payload(): void
    {
        $this->expectException(ValidationException::class);

        $sql = 'INSERT INTO clinics (id) VALUES (1);';
        $compressed = gzencode($sql, 9);

        $request = Request::create('/master/settings/import-sql?clinic_id=1', 'POST', ['clinic_id' => 1], [], [], [
            'CONTENT_TYPE' => 'application/octet-stream',
            'CONTENT_LENGTH' => strlen($compressed),
            'HTTP_X_SQL_IMPORT_ENCODING' => 'gzip',
            'HTTP_X_SQL_FILE_NAME' => rawurlencode('db-backup.txt'),
        ], $compressed);

        $this->invokeControllerMethod(new SettingsController(), 'extractSqlPayload', [$request]);
    }

    public function test_it_allows_standard_mysqldump_wrapper_statements(): void
    {
        $sql = <<<'SQL'
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
LOCK TABLES `patients` WRITE;
/*!40000 ALTER TABLE `patients` DISABLE KEYS */;
INSERT INTO `patients` (`id`, `name`) VALUES (1, 'Test');
/*!40000 ALTER TABLE `patients` ENABLE KEYS */;
UNLOCK TABLES;
SQL;

        $controller = new SettingsController();
        $normalized = $this->invokeControllerMethod($controller, 'normalizeImportSql', [$sql]);
        $blocked = $this->invokeControllerMethod($controller, 'detectBlockedSqlStatement', [$normalized]);

        $this->assertSame("INSERT INTO `patients` (`id`, `name`) VALUES (1, 'Test');", $normalized);
        $this->assertNull($blocked);
    }

    public function test_it_still_blocks_real_schema_changes(): void
    {
        $sql = <<<'SQL'
INSERT INTO `patients` (`id`, `name`) VALUES (1, 'Test');
ALTER TABLE `patients` ADD COLUMN `legacy_code` VARCHAR(20) NULL;
SQL;

        $controller = new SettingsController();
        $normalized = $this->invokeControllerMethod($controller, 'normalizeImportSql', [$sql]);
        $blocked = $this->invokeControllerMethod($controller, 'detectBlockedSqlStatement', [$normalized]);

        $this->assertNotNull($blocked);
        $this->assertStringContainsString('ALTER TABLE `patients` ADD COLUMN', $blocked);
    }

    private function invokeControllerMethod(object $controller, string $methodName, array $arguments = []): mixed
    {
        $method = new ReflectionMethod($controller, $methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($controller, $arguments);
    }
}