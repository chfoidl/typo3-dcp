<?php

namespace Sethorax\Dcp\Utility;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConnectionUtility
{
    /**
     * Returns the DB connection for the given $tablename
     *
     * @return Connection
     */
    public static function getDBConnectionForTable(string $tableName)
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($tableName);
    }
}
