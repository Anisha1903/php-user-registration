<?php
/**
 * Copyright (C) Phppot
 *
 * Distributed under 'The MIT License (MIT)'
 * In essence, you can do commercial use, modify, distribute and private use.
 * Though not mandatory, you are requested to attribute Phppot URL in your code or website.
 */

namespace Phppot;

/**
 * Generic datasource class for handling DB operations.
 * Uses MySqli and PreparedStatements.
 *
 * @version 2.7 - PDO connection option added
 */
class DataSource
{
    const HOST = 'localhost';
    const USERNAME = 'root';
    const PASSWORD = 'admin123';
    const DATABASENAME = 'user-registration';

    private $conn;

    function __construct()
    {
        $this->conn = $this->getConnection();
    }

    public function getConnection()
    {
        $conn = new \mysqli(self::HOST, self::USERNAME, self::PASSWORD, self::DATABASENAME);

        if (mysqli_connect_errno()) {
            trigger_error("Problem with connecting to the database.");
        }

        $conn->set_charset("utf8");
        return $conn;
    }

    public function getPdoConnection()
    {
        $conn = false;

        try {
            $dsn = 'mysql:host=' . self::HOST . ';dbname=' . self::DATABASENAME;
            $conn = new \PDO($dsn, self::USERNAME, self::PASSWORD);
            $conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\Exception $e) {
            exit("PDO Connect Error: " . $e->getMessage());
        }

        return $conn;
    }

    public function select($query, $paramType = "", $paramArray = array())
    {
        $stmt = $this->conn->prepare($query);

        if (!empty($paramType) && !empty($paramArray)) {
            $this->bindQueryParams($stmt, $paramType, $paramArray);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $resultset = array();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $resultset[] = $row;
            }
        }

        return $resultset;
    }

    public function insert($query, $paramType, $paramArray)
    {
        $stmt = $this->conn->prepare($query);
        $this->bindQueryParams($stmt, $paramType, $paramArray);

        $stmt->execute();
        $insertId = $stmt->insert_id;

        return $insertId;
    }

    public function execute($query, $paramType = "", $paramArray = array())
    {
        $stmt = $this->conn->prepare($query);

        if (!empty($paramType) && !empty($paramArray)) {
            $this->bindQueryParams($stmt, $paramType, $paramArray);
        }

        $stmt->execute();
    }

    public function bindQueryParams($stmt, $paramType, $paramArray = array())
{
    if ($stmt instanceof \mysqli_stmt) {
        $bindParams = array($paramType);

        for ($i = 0; $i < count($paramArray); $i++) {
            $bindParams[] = & $paramArray[$i];
        }

        // Use call_user_func_array to dynamically call bind_param with variable arguments
        call_user_func_array(array($stmt, 'bind_param'), $bindParams);

        // Ensure that the binding was successful
        if ($stmt->errno) {
            trigger_error("Binding parameters failed: " . $stmt->error);
        }
    } else {
        // Handle the case where $stmt is not a valid mysqli_stmt object
        trigger_error("Invalid statement handle for binding parameters.");
    }
}


    public function getRecordCount($query, $paramType = "", $paramArray = array())
    {
        $stmt = $this->conn->prepare($query);

        if (!empty($paramType) && !empty($paramArray)) {
            $this->bindQueryParams($stmt, $paramType, $paramArray);
        }

        $stmt->execute();
        $stmt->store_result();
        $recordCount = $stmt->num_rows;

        return $recordCount;
    }
}
