<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

class DBHandler {

	protected $pdo;

	function __construct() {
		$this->pdo = new PDO('mysql:host=' . env('DB_HOST') . ';dbname=' . env('DB_NAME') . ';charset=utf8', env('DB_USER'), env('DB_PASSWORD'));

        if (!$this->tableExists()) {
            $this->createTable();
        }
	}

	function read($hash){
		$stmt = $this->pdo->prepare('SELECT body FROM cached WHERE hash = :hash');
		$stmt->execute(array('hash' => $hash));
		foreach ($stmt as $row) {
		    return $row[0];
		}
		return null;
	}

	function cache($hash, $body){
		$stmt = $this->pdo->prepare("INSERT INTO cached (hash, body) VALUES (:hash, :body)");
		$stmt->bindParam(':hash', $hash);
		$stmt->bindParam(':body', $body);
		$stmt->execute();
	}

    private function createTable()
    {
        $stmt = $this->pdo->prepare("CREATE TABLE IF NOT EXISTS `cached` (
  `hash` text COLLATE utf8_unicode_ci NOT NULL,
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
