<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

class DBHandler {

    protected $pdo;

    public function __construct() {
        $this->pdo = new PDO('mysql:host=' . env('DB_HOST') . ';dbname=' . env('DB_NAME') . ';charset=utf8', env('DB_USER'), env('DB_PASSWORD'));

        if (!$this->cacheTableExists()) {
            $this->createCacheTable();
        }

        if (!$this->logTableExists()) {
            $this->createLogTable();
        }
    }

    public function read(string $url, bool $increaseCacheCount = true): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, title, body FROM cache WHERE url = :url LIMIT 1');
        $stmt->execute(['url' => $url]);
        foreach ($stmt as $row) {
            if ($increaseCacheCount) {
                $this->increaseCacheCount($url);
            }
            return [$row['title'], $row['body'], $row['id']];
        }

        return null;
    }

    public function cache($url, $title, $body)
    {
        list($currentTitle, $currentTitle, $currentID) = $this->read($url, false);

        if ($currentID) {
            // already has cache, let's update the row
            $stmt = $this->pdo->prepare("UPDATE cache SET url=:url, title=:title, body=:body WHERE id=:id");
            $stmt->bindParam(':id', $currentID);
        } else {
            // no cache, add it!
            $stmt = $this->pdo->prepare("INSERT INTO cache (url, title, body) VALUES (:url, :title, :body)");
        }

        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':body', $body);
        $stmt->execute();
    }

    public function log($url, $file_get_contents_error, $userAgent)
    {
        $stmt = $this->pdo->prepare("INSERT INTO log (url, file_get_contents_error, user_agent) VALUES (:url, :file_get_contents_error, :user_agent)");
        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':file_get_contents_error', $file_get_contents_error);
        $stmt->bindParam(':user_agent', $userAgent);
        $stmt->execute();
    }

    private function createCacheTable()
    {
        $stmt = $this->pdo->prepare("CREATE TABLE IF NOT EXISTS `cache` (
  `id` BIGINT(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nr_readings` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
  `url` text COLLATE utf8_general_ci NOT NULL,
  `title` text COLLATE utf8_general_ci NOT NULL,
  `body` longtext COLLATE utf8_general_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
)");
        $stmt->execute();
    }

    private function cacheTableExists():bool
    {
        $stmt = $this->pdo->prepare("SHOW TABLES LIKE 'cache'");
        $stmt->execute();
        return $stmt->rowCount() >= 1;
    }

    private function createLogTable()
    {
        $stmt = $this->pdo->prepare("CREATE TABLE IF NOT EXISTS `log` (
  `id` BIGINT(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `url` text COLLATE utf8_general_ci NOT NULL,
  `file_get_contents_error` text COLLATE utf8_general_ci NOT NULL,
  `user_agent` tinytext COLLATE utf8_general_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");
        $stmt->execute();
    }

    private function logTableExists():bool
    {
        $stmt = $this->pdo->prepare("SHOW TABLES LIKE 'log'");
        $stmt->execute();
        return $stmt->rowCount() >= 1;
    }

    private function increaseCacheCount($url)
    {
        $stmt = $this->pdo->prepare('UPDATE cache SET nr_readings = nr_readings + 1 WHERE url = :url');
        $stmt->execute(['url' => $url]);
    }
}
