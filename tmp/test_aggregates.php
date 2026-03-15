<?php

require_once 'c:\Users\hp\Downloads\appwrite\tmp\utopia-php-database\vendor\autoload.php';

use Utopia\Database\Query;
use Utopia\Database\Adapter\SQLite;
use Utopia\Database\Database;

echo "Testing AGGREGATE and DISTINCT SQL Generation...\n";

// Mock PDO using anonymous class
$pdo = new class extends \PDO {
    public function __construct() {}
    public function prepare($query, $options = []): \PDOStatement|false {
        return new class extends \PDOStatement {
            public function bindValue($param, $value, $type = \PDO::PARAM_STR): bool { return true; }
            public function execute($params = null): bool { return true; }
            public function fetchAll($mode = \PDO::FETCH_DEFAULT, ...$args): array { return []; }
            public function closeCursor(): bool { return true; }
        };
    }
};

$adapter = new SQLite($pdo);
$adapter->setNamespace('utopia');

// Test Scenarios
$scenarios = [
    'SELECT DISTINCT' => [
        Query::selectDistinct(['name', 'email']),
    ],
    'COUNT' => [
        Query::count('*'),
    ],
    'COUNT with attribute' => [
        Query::count('id'),
    ],
    'SUM' => [
        Query::sum('price'),
    ],
    'AVG' => [
        Query::avg('rating'),
    ],
    'MIN/MAX' => [
        Query::min('age'),
        Query::max('age'),
    ],
    'Combination' => [
        Query::count('*'),
        Query::sum('amount'),
        Query::equal('status', ['active'])
    ]
];

foreach ($scenarios as $name => $queries) {
    echo "\nScenario: $name\n";
    try {
        // We use a dummy collection 'test'
        // The SQL adapter's find method will call getAttributeProjection
        $adapter->find('test', $queries);
    } catch (\Throwable $e) {
        // We expect an error because the mock PDO doesn't actually work, 
        // but the debug echo in SQL.php should have fired by now.
        if (!str_contains($e->getMessage(), 'SQLSTATE[HY000]')) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}
