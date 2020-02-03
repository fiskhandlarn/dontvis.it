<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

class DBHandler {

    protected $pdo;

    function __construct() {
        $this->pdo = new PDO('mysql:host=' . env('DB_HOST') . ';dbname=' . env('DB_NAME') . ';charset=utf8', env('DB_USER'), env('DB_PASSWORD'));

        if (!$this->tableExists()) {
            $this->createTable();
        }
    }

    public function read($url): ?array
    {
        $stmt = $this->pdo->prepare('SELECT title, body FROM cache WHERE url = :url LIMIT 1');
        $stmt->execute(array('url' => $url));
        foreach ($stmt as $row) {
            return [$row['title'], $row['body']];
        }

        return null;
    }

    function cache($url, $title, $body){
        $stmt = $this->pdo->prepare("INSERT INTO cache (url, title, body) VALUES (:url, :title, :body)");
        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':body', $body);
        $stmt->execute();
    }

    private function createTable()
    {
        $stmt = $this->pdo->prepare("CREATE TABLE IF NOT EXISTS `cache` (
  `id` BIGINT(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `url` text COLLATE utf8_unicode_ci NOT NULL,
  `title` text COLLATE utf8_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");
        $stmt->execute();
    }

    private function tableExists():bool
    {
        $stmt = $this->pdo->prepare("SHOW TABLES LIKE 'cache'");
        $stmt->execute();
        return $stmt->rowCount() >= 1;
    }
}
