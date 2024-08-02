<?php

namespace Mallardduck\ScryfallBulkSdk\Downloader;

use JsonMachine\Items;
use JsonMachine\JsonDecoder\PassThruDecoder;
use Mallardduck\ScryfallBulkSdk\BulkFileType;
use PDO;

class JsonToSqliteStreamer
{
    private PDO $db;
    private string $tableName;

    public static function sqlitePath(string $basepath, BulkFileType $bulkFileType): string
    {
        $sqliteFileName = $bulkFileType->slug() . '.sqlite';
        return $basepath . DIRECTORY_SEPARATOR . $sqliteFileName;
    }

    public function __construct(
        private readonly string $sqliteFilePath,
        private readonly string $jsonFilePath,
    ) {
        if (!file_exists($this->sqliteFilePath)) {
            touch($this->sqliteFilePath);
        }
        $this->db = new PDO("sqlite:$this->sqliteFilePath");
        $this->tableName = 'cards';
    }

    public function __invoke()
    {
        $sampleData = $this->getSampleData();
        $columns = $this->determineSchema($sampleData);
        $this->createTable($columns);
        $this->insertData($columns);
    }

    private function getSampleData(): array
    {
        $sample = [];
        $iterator = Items::fromFile($this->jsonFilePath);
        foreach ($iterator as $key => $value) {
            $sample[] = $value;
            if (count($sample) >= 10) {
                break;
            }
        }
        return $sample;
    }

    private function determineSchema(array $sampleData): array
    {
        $columns = [];
        foreach ($sampleData as $row) {
            foreach ($row as $key => $value) {
                if (!isset($columns[$key])) {
                    $columns[$key] = $this->guessType($value);
                } else {
                    // Ensure we handle mixed types
                    $existingType = $columns[$key];
                    $newType = $this->guessType($value);
                    if ($existingType !== $newType) {
                        $columns[$key] = 'TEXT NULL';
                    }
                }
            }
        }
        return $columns;
    }

    private function insertData(array $columns)
    {
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $columnNames = implode(', ', array_map(fn($value) => "`" . $value . "`", array_keys($columns)));
        $stmt = $this->db->prepare("INSERT INTO {$this->tableName} ($columnNames) VALUES ($placeholders)");

        $overallCount = 0;
        $count = 0;
        $outUsage = memory_get_usage();
        $outUsageReal = memory_get_usage(true);

        $jsonItems = Items::fromFile($this->jsonFilePath);
        foreach ($jsonItems as $row) {
            if ($count === 0 && !$this->db->inTransaction()) $this->db->beginTransaction();
            $values = [];
            foreach ($columns as $name => $type) {
                $value = $row->{$name} ?? null;
                if (is_bool($value)) {
                    $value = $value ? 1 : 0;
                }
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                }
                if (is_string($value) && empty($value)) {
                    $value = null;
                }
                $values[] = $value ?? null;
                unset($value);
            }
            $stmt->execute($values);
            $values = null;
            $count++;

            if ($count % 100 === 0) {
                $this->db->commit();
                $count = 0;
            }
        }
    }

    private function guessType($value): string
    {
        if (is_int($value)) {
            return 'INTEGER';
        } elseif (is_float($value)) {
            return 'REAL';
        } elseif (is_bool($value)) {
            return 'INTEGER'; // SQLite does not have a boolean type
        } elseif (is_null($value)) {
            return 'TEXT NULL';
        } elseif (is_string($value)) {
            // Try to parse the string as an integer
            if (ctype_digit($value) && filter_var($value, FILTER_VALIDATE_INT) !== false) {
                return 'INTEGER';
            }

            // Try to parse the string as a float
            if (is_numeric($value) && str_contains($value, '.') && filter_var($value, FILTER_VALIDATE_FLOAT) !== false) {
                return 'REAL';
            }
        }

        // If the string doesn't fit any of the above cases, treat it as TEXT
        return 'TEXT';
    }

    private function createTable(array $columns)
    {
        $columnDefinitions = [];
        foreach ($columns as $name => $type) {
            $columnDefinitions[] = "`$name` $type";
        }
        $columnDefinitions = implode(', ', $columnDefinitions);
        $sql = "CREATE TABLE IF NOT EXISTS {$this->tableName} (_id INTEGER PRIMARY KEY AUTOINCREMENT, $columnDefinitions)";
        $this->db->exec($sql);
    }

}