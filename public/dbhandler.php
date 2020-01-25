<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

class DBHandler {

	protected $pdo;

	function __construct() {
		$this->pdo = new PDO('mysql:host=' . env('DB_HOST') . ';dbname=' . env('DB_NAME') . ';charset=utf8', env('DB_USER'), env('DB_PASSWORD'));

        if (!$this->tableExists()) {
            $this->createTable();
        }
	}

	public function read($hash): ?array
    {
		$stmt = $this->pdo->prepare('SELECT title, body FROM cached WHERE hash = :hash LIMIT 1');
		$stmt->execute(array('hash' => $hash));
		foreach ($stmt as $row) {
            return [$row['title'], $row['body']];
		}

        return null;
	}

	function cache($hash, $title, $body){
		$stmt = $this->pdo->prepare("INSERT INTO cached (hash, title, body) VALUES (:hash, :title, :body)");
		$stmt->bindParam(':hash', $hash);
		$stmt->bindParam(':title', $title);
		$stmt->bindParam(':body', $body);
		$stmt->execute();
	}

    private function createTable()
    {
        $stmt = $this->pdo->prepare("CREATE TABLE IF NOT EXISTS `cached` (
  `hash` text COLLATE utf8_unicode_ci NOT NULL,
  `title` text COLLATE utf8_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8_unicode_ci NOT NULL
)");
        $stmt->execute();
    }

    private function tableExists():bool
    {
        $stmt = $this->pdo->prepare("SHOW TABLES LIKE 'cached'");
        $stmt->execute();
        return $stmt->rowCount() >= 1;
    }
}
