<?php

namespace App\Helpers;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\SQLiteConnection;

class Sqlite
{
    /**
     * Returns the Database Connection
     *
     * @param \Illuminate\Database\DatabaseManager $manager
     * @param string $connection
     * @return SQLiteConnection
     * @throws Exception
     */
    public function getDatabase(DatabaseManager $manager, string $connection)
    {
        $db = $manager->connection($connection);

        // We will throw an exception if the database is not SQLite
        if(!$db instanceof SQLiteConnection) {
            throw new Exception("The '$connection' connection must be sqlite, [{$db->getDriverName()}] given.");
        }

        return $db;
    }

    /**
     * Sets the Journal Mode to WAL
     *
     * @param  \Illuminate\Database\ConnectionInterface $connection
     * @return bool
     */
    public function setWalJournalMode(ConnectionInterface $connection)
    {
        return $connection->statement('PRAGMA journal_mode=WAL;');
    }

    /**
     * Returns the current Journal Mode of the Database Connection
     *
     * @param  \Illuminate\Database\ConnectionInterface $connection
     * @return string
     */
    public function getJournalMode(ConnectionInterface $connection)
    {
        return data_get($connection->select(new Expression('PRAGMA journal_mode')), '0.journal_mode');
    }

}
