<?php

require_once __DIR__ . '/../../config.php';

class Database
{
    private $pdo;

    public function __construct()
    {
        global $CFG;
        try {
            // Connect to the SQLite database
            $this->pdo = new PDO($CFG->dbDSN, $CFG->dbUser, $CFG->dbPassword);
            // Set error mode to exceptions
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    public function getConnection()
    {
        return $this->pdo;
    }

    public function select_many($table, $columns = '*', $filters = [], $sort = '', $offset = 0, $limit = 0)
    {
        $sql = "SELECT $columns FROM $table";

        $where = [];
        $params = [];
        foreach ($filters as $column => $value) {
            $where[] = "$column = :$column";
            $params[$column] = $value;
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        if (!empty($sort)) {
            $sql .= " ORDER BY $sort";
        }

        if ($limit > 0) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function select_many_where($table, $columns = '*', $where = '', $sort = '', $offset = 0, $limit = 0)
    {
        $sql = "SELECT $columns FROM $table";

        if (!empty($where)) {
            $sql .= " WHERE $where";
        }

        if (!empty($sort)) {
            $sql .= " ORDER BY $sort";
        }

        if ($limit > 0) {
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function select_many_sql($sql)
    {
        // TODO
    }

    public function select_one($table, $columns = '*', $filters = [])
    {
        $sql = "SELECT $columns FROM $table";

        $where = [];
        $params = [];
        foreach ($filters as $column => $value) {
            $where[] = "$column = :$column";
            $params[$column] = $value;
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= " LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function select_one_where($table, $columns = '*', $where = '')
    {
        $sql = "SELECT $columns FROM $table";

        if (!empty($where)) {
            $sql .= " WHERE $where";
        }

        $sql .= " LIMIT 1";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function select_one_sql($sql)
    {
        // TODO
    }

    public function delete($table, $filters)
    {
        $sql = "DELETE FROM $table";

        $where = [];
        $params = [];
        foreach ($filters as $column => $value) {
            $where[] = "$column = :$column";
            $params[$column] = $value;
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        } else {
            throw new InvalidArgumentException('Filters cannot be empty for delete operation.');
        }

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete_where($table, $filters)
    {
        // TODO
    }

    public function update($table, $dataitem)
    {
        // TODO
    }

    public function insert_one($table, $dataitem)
    {
        // TODO
    }

    public function insert_many($table, $dataitems)
    {
        // TODO
    }

    public function count($table, $filters)
    {
        // TODO
    }

    public function count_where($table, $filters)
    {
        // TODO
    }

    public function count_sql($table, $filters)
    {
        // TODO
    }
}

unset($DB);
global $DB;

$DB = new Database($CFG);
