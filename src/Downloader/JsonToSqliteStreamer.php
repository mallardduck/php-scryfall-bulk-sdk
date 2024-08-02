<?php

namespace Mallardduck\ScryfallBulkSdk\Downloader;

use JsonMachine\Items;
use Mallardduck\ScryfallBulkSdk\BulkFileType;
use Mallardduck\ScryfallBulkSdk\Scryfall\Card;
use PDO;
use PDOException;
use ReflectionClass;
use ReflectionProperty;

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
        $columns = $this->determineSchema();
        $this->createTable($columns);
        $this->insertData($columns);
    }

    private function determineSchema(): array
    {
        $columns = [];
        // TODO: rebuild this based on Reflection and using the new Card class.
        $cardReflection = new ReflectionClass(Card::class);
        foreach ($cardReflection->getProperties() as $property) {
            if ($property->isPublic()) {
                // Don't need to guess type, we now can infer it based on the PHP property type...
                $columns[$property->getName()] = $this->inferType($property);
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
            try {
                $stmt->execute($values);
            } catch (PDOException $e) {
                var_dump($row->name, $row->scryfall_uri);
                throw $e;
            }
            $values = null;
            $count++;

            if ($count % 100 === 0) {
                $this->db->commit();
                $count = 0;
            }
        }
    }


    private function inferType(ReflectionProperty $property): string
    {
        $typeCollector = function (array &$sink, \ReflectionNamedType $type) {
            $sink[] = $type->getName();
            if ($type->allowsNull()) {
                $sink[] = null;
            }
        };

        // Collection all the column/property types into a single array...
        $types = [];
        $reflectionType = $property->getType();
        if ($reflectionType instanceof \ReflectionUnionType) {
            $unionTypes = $reflectionType->getTypes();
            foreach ($unionTypes as $type) {
                $typeCollector($types, $type);
            }
        } else {
            $typeCollector($types, $reflectionType);
        }

        // Determine the type based on types...
        $sqliteType = "TEXT";
        if (in_array("float", $types) && in_array("int", $types)) {
            // Do nothing
            usleep(1);
        } elseif (in_array("float", $types)) {
            $sqliteType = "REAL";
        } elseif (in_array("int", $types)) {
            $sqliteType = "INTEGER";
        } elseif (in_array("bool", $types)) {
            $sqliteType = "BOOLEAN";
        }

        if (in_array(null, $types, true)) {
            $sqliteType .= " NULL";
        } else {
            $sqliteType .= " NOT NULL";
        }

        return $sqliteType;
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