<?php

declare(strict_types=1);

namespace Dontvisit;

use PDO;

class DBHandler
{
    protected $pdo;

    public function __construct()
    {
        $this->pdo = new PDO('mysql:host='.env('DB_HOST').';dbname='.env('DB_NAME').';charset=utf8', env('DB_USER'), env('DB_PASSWORD'));

        if (!$this->cacheTableExists()) {
            $this->createCacheTable();
        }

        if (!$this->logTableExists()) {
            $this->createLogTable();
        }
    }

    /*********************************************************************************
     *    ___       __   ___                  __  __           __
     *   / _ \__ __/ /  / (_)___   __ _  ___ / /_/ /  ___  ___/ /__
     *  / ___/ // / _ \/ / / __/  /  ' \/ -_) __/ _ \/ _ \/ _  (_-<
     * /_/   \_,_/_.__/_/_/\__/  /_/_/_/\__/\__/_//_/\___/\_,_/___/
     *
     *********************************************************************************/

    public function cache($url, $title, $body, $fullURL, $userAgent)
    {
        list($currentTitle, $currentTitle, $currentFullURL, $currentID) = $this->read($url, false);

        if ($currentID) {
            // already has cache, let's update the row
            $stmt = $this->pdo->prepare('UPDATE cache SET url=:url, title=:title, body=:body, full_url=:full_url, user_agent=:user_agent WHERE id=:id');
            $stmt->bindParam(':id', $currentID);
        } else {
            // no cache, add it!
            $stmt = $this->pdo->prepare('INSERT INTO cache (url, title, body, full_url, user_agent) VALUES (:url, :title, :body, :full_url, :user_agent)');
        }

        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':body', $body);
        $stmt->bindParam(':full_url', $fullURL);
        $stmt->bindParam(':user_agent', $userAgent);
        $stmt->execute();
    }

    public function latestURLs(int $limit = 5): array
    {
        $ret = [];
        $stmt = $this->pdo->prepare('SELECT url, title FROM cache ORDER BY timestamp DESC LIMIT :limit');
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT); // https://stackoverflow.com/a/11738633/1109380
        $stmt->execute();
        foreach ($stmt as $row) {
            $ret []= [
                'url' => $row['url'],
                'title' => $row['title'],
            ];
        }

        return $ret;
    }

    public function log($url, $file_get_contents_error, $userAgent)
    {
        $stmt = $this->pdo->prepare('INSERT INTO log (url, file_get_contents_error, user_agent) VALUES (:url, :file_get_contents_error, :user_agent)');
        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':file_get_contents_error', $file_get_contents_error);
        $stmt->bindParam(':user_agent', $userAgent);
        $stmt->execute();
    }

    public function read(string $url, bool $increaseCacheCount = true): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, title, body, full_url FROM cache WHERE url = :url LIMIT 1');
        $stmt->execute(['url' => $url]);
        foreach ($stmt as $row) {
            if ($increaseCacheCount) {
                $this->increaseCacheCount($url);
            }

            return [$row['title'], $row['body'], $row['full_url'], $row['id']];
        }

        return null;
    }

    public function randomURLs(int $limit = 5): array
    {
        $ret = [];
        // https://stackoverflow.com/a/4329447/1109380
        $stmt = $this->pdo->prepare('SELECT url, title
  FROM cache AS r1 JOIN
       (SELECT CEIL(RAND() *
                     (SELECT MAX(id)
                        FROM cache)) AS id)
        AS r2
 WHERE r1.id >= r2.id
 ORDER BY r1.id ASC
 LIMIT :limit');
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT); // https://stackoverflow.com/a/11738633/1109380
        $stmt->execute();
        foreach ($stmt as $row) {
            $ret []= [
                'url' => $row['url'],
                'title' => $row['title'],
            ];
        }

        return $ret;
    }

    public function topURLs(int $limit = 5): array
    {
        $ret = [];
        $stmt = $this->pdo->prepare('SELECT url, title FROM cache ORDER BY nr_readings DESC, timestamp DESC LIMIT :limit');
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT); // https://stackoverflow.com/a/11738633/1109380
        $stmt->execute();
        foreach ($stmt as $row) {
            $ret []= [
                'url' => $row['url'],
                'title' => $row['title'],
            ];
        }

        return $ret;
    }

    /*********************************************************************************
     *    ___      _           __                   __  __           __
     *   / _ \____(_)  _____ _/ /____    __ _  ___ / /_/ /  ___  ___/ /__
     *  / ___/ __/ / |/ / _ `/ __/ -_)  /  ' \/ -_) __/ _ \/ _ \/ _  (_-<
     * /_/  /_/ /_/|___/\_,_/\__/\__/  /_/_/_/\__/\__/_//_/\___/\_,_/___/
     *
     *********************************************************************************/

    private function cacheTableExists(): bool
    {
        $stmt = $this->pdo->prepare("SHOW TABLES LIKE 'cache'");
        $stmt->execute();

        return $stmt->rowCount() >= 1;
    }

    private function createCacheTable()
    {
        $stmt = $this->pdo->prepare("CREATE TABLE IF NOT EXISTS `cache` (
  `id` BIGINT(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nr_readings` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
  `url` text COLLATE utf8_general_ci NOT NULL,
  `title` text COLLATE utf8_general_ci NOT NULL,
  `body` longtext COLLATE utf8_general_ci NOT NULL,
  `full_url` text COLLATE utf8_general_ci NOT NULL,
  `user_agent` tinytext COLLATE utf8_general_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
)");
        $stmt->execute();
    }

    private function createLogTable()
    {
        $stmt = $this->pdo->prepare('CREATE TABLE IF NOT EXISTS `log` (
  `id` BIGINT(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `url` text COLLATE utf8_general_ci NOT NULL,
  `file_get_contents_error` text COLLATE utf8_general_ci NOT NULL,
  `user_agent` tinytext COLLATE utf8_general_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)');
        $stmt->execute();
    }

    private function increaseCacheCount($url)
    {
        $stmt = $this->pdo->prepare('UPDATE cache SET nr_readings = nr_readings + 1 WHERE url = :url');
        $stmt->execute(['url' => $url]);
    }

    private function logTableExists(): bool
    {
        $stmt = $this->pdo->prepare("SHOW TABLES LIKE 'log'");
        $stmt->execute();

        return $stmt->rowCount() >= 1;
    }
}
