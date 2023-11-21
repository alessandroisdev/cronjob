<?php

namespace AlessandroIsDev\CronJob\Models;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 *
 */
abstract class Model
{
    /**
     * @var Connection
     */
    protected Connection $conn;
    /**
     * @var Exception
     */
    protected Exception $fail;
    /**
     * @var array
     */
    protected array $params = [];
    /**
     * @var array
     */
    protected array $values = [];

    /**
     *
     */
    public function __construct()
    {
        try {
            $this->conn = DriverManager::getConnection(connectionParams);
        } catch (Exception $e) {
            echo $e->getMessage();
            die($e->getCode());
        }
    }

    /**
     * @return Connection
     */
    public function getConn(): Connection
    {
        return $this->conn;
    }

    /**
     * @return QueryBuilder
     */
    public function builder(): QueryBuilder
    {
        return $this->getConn()->createQueryBuilder();
    }

    /**
     * @param array $params
     * @return void
     */
    public function prepare(array $params): void
    {
        foreach ($params as $param => $value) {
            $this->values[$param] = ":{$param}";
            $this->params[$param] = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $value;
        }
    }

    /**
     * @param string $table
     * @param array|null $data
     * @return false|int|string|null
     */
    public function save(string $table, ?array $data): false|int|string|null
    {
        if ($data) {
            $this->prepare($data);
        }

        try {
            $this->conn->createQueryBuilder()->insert($table)->values($this->getValues())->setParameters($this->getParams());
            return $this->conn->lastInsertId();
        } catch (Exception $e) {
            $this->fail = $e;
            return null;
        }
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @return Exception
     */
    public function fail(): Exception
    {
        return $this->fail;
    }

}