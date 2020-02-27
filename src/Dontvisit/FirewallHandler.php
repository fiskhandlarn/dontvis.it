<?php

declare(strict_types=1);

namespace Dontvisit;

use PDO;
use MetaRush\Firewall\Builder;

class FirewallHandler
{
    protected $pdo;
    protected $fw;

    // https://github.com/metarush/firewall#setup
    private static $tables = [
        'fw_tempBan',
        'fw_extendedBan',
        'fw_whitelist',
        'fw_failCount',
        'fw_blockCount',
    ];

    public function __construct()
    {
        $this->pdo = new PDO('mysql:host='.env('DB_HOST').';dbname='.env('DB_NAME').';charset=utf8', env('DB_USER'), env('DB_PASSWORD'));

        $builder = (new Builder)
            ->setDsn('mysql:host='.env('DB_HOST').';dbname='.env('DB_NAME').';charset=utf8')
            ->setDbUser(env('DB_USER'))
            ->setDbPass(env('DB_PASSWORD'))
            ->setTempBanTable('fw_tempBan')
            ->setExtendedBanTable('fw_extendedBan')
            ->setWhitelistTable('fw_whitelist')
            ->setFailCountTable('fw_failCount')
            ->setBlockCountTable('fw_blockCount');

        $this->fw = $builder->build();

        if (!$this->tablesExist()) {
            $this->createTables();

            // add to whitelist
            if (env('FIREWALL_WHITELIST', false)) {
                $addresses = explode(',', env('FIREWALL_WHITELIST'));
                foreach ($addresses as $address) {
                    //var_dump("adding " . $address . " to whitelist");
                    $this->fw->whitelist($address);
                }
            }
        }
    }


    /*********************************************************************************
     *    ___       __   ___                  __  __           __
     *   / _ \__ __/ /  / (_)___   __ _  ___ / /_/ /  ___  ___/ /__
     *  / ___/ // / _ \/ / / __/  /  ' \/ -_) __/ _ \/ _ \/ _  (_-<
     * /_/   \_,_/_.__/_/_/\__/  /_/_/_/\__/\__/_//_/\___/\_,_/___/
     *
     *********************************************************************************/

    public function begin()
    {
        $this->fw->flushExpired(); // put this on top

        if ($this->fw->banned($_SERVER['REMOTE_ADDR'])) {
            header('HTTP/1.1 403 Forbidden');
            exit('Forbidden'); // or redirect somewhere else
        }
    }

    public function prevent($address)
    {
        $this->fw->preventBruteForce($address);
    }

    /*********************************************************************************
     *    ___           __          __         __             __  __           __
     *   / _ \_______  / /____ ____/ /____ ___/ /  __ _  ___ / /_/ /  ___  ___/ /__
     *  / ___/ __/ _ \/ __/ -_) __/ __/ -_) _  /  /  ' \/ -_) __/ _ \/ _ \/ _  (_-<
     * /_/  /_/  \___/\__/\__/\__/\__/\__/\_,_/  /_/_/_/\__/\__/_//_/\___/\_,_/___/
     *
     *********************************************************************************/




    /*********************************************************************************
     *    ___      _           __                   __  __           __
     *   / _ \____(_)  _____ _/ /____    __ _  ___ / /_/ /  ___  ___/ /__
     *  / ___/ __/ / |/ / _ `/ __/ -_)  /  ' \/ -_) __/ _ \/ _ \/ _  (_-<
     * /_/  /_/ /_/|___/\_,_/\__/\__/  /_/_/_/\__/\__/_//_/\___/\_,_/___/
     *
     *********************************************************************************/

    private function tablesExist(): bool
    {
        foreach (self::$tables as $table) {
            $stmt = $this->pdo->prepare("SHOW TABLES LIKE '". $table ."'");
            $stmt->execute();

            if ($stmt->rowCount() < 1) {
                return false;
            }
        }

        return true;
    }

    private function createTables()
    {
        foreach (self::$tables as $table) {
            $stmt = $this->pdo->prepare('CREATE TABLE `'. $table .'` (
    `ip` VARCHAR(45),
    `dateTime` DATETIME
)');
            $stmt->execute();
        }
    }
}
