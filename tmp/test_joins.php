<?php

require_once 'vendor/autoload.php';

use Utopia\Database\Database;
use Utopia\Database\Adapter\MySQL;
use Utopia\Database\Query;
use Utopia\Database\Document;
use Utopia\Cache\Cache;
use Utopia\Cache\Adapter\None;

// This is a mockup/scratch script to verify SQL generation
class MockSQL extends MySQL {
    public function execute($stmt): bool {
        return true;
    }
    
    // Override to capture SQL for verification
    public function find(Document $collection, array $queries = [], ?int $limit = 25, ?int $offset = null, array $orderAttributes = [], array $orderTypes = [], array $cursor = [], string $cursorDirection = Database::CURSOR_AFTER, string $forPermission = Database::PERMISSION_READ): array {
        $sql = $this->generateSQL($collection, $queries, $limit, $offset, $orderAttributes, $orderTypes, $cursor, $cursorDirection);
        echo "GENERATED SQL:\n" . $sql . "\n\n";
        return [];
    }

    private function generateSQL(Document $collection, array $queries, ?int $limit, ?int $offset, array $orderAttributes, array $orderTypes, array $cursor, string $cursorDirection) {
        // We reuse the logic but return the SQL string
        // Since I can't easily refactor the whole class, I'll just rely on what I implemented.
        // Actually, I can just call parent::find and look at what I triggered if I had a logger.
        // But for this test, I'll just use reflections or similar if needed.
        // For now, let's just assume I can see the output if I add an echo in SQL.php Temporarily.
        return ""; 
    }
}

// Better approach: Use a real PDO with a mock or sqlite
// But I just want to see the SQL string.

echo "Testing JOIN SQL Generation...\n";

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

$adapter = new \Utopia\Database\Adapter\SQLite($pdo);
$adapter->setNamespace('utopia');

$collection = new Document([
    '$id' => 'books',
    'attributes' => []
]);

$queries = [
    Query::innerJoin('authors', 'authors.id = books.author_id', 'a'),
    Query::leftJoin('publishers', 'publishers.id = books.publisher_id', 'p'),
    Query::equal('a.name', ['Tolkien'])
];

echo "Query 1: Inner and Left Join with and Filter on joined table alias\n";
try {
    $adapter->find($collection, $queries);
} catch (\Throwable $e) {
    // Expected to fail on execute, but we should see the echo before that
}
