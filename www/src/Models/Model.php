<?php

namespace AlessandroIsDev\CronJob\Models;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Query\QueryBuilder;
use Dotenv\Dotenv;

abstract class Model
{
    protected Connection $conn;
    protected ?Exception $fail = null;
    protected array $params = [];
    protected array $values = [];

    public function __construct()
    {
        $dotenvPath = dirname(__DIR__, 2);
        if (file_exists($dotenvPath . '/.env')) {
            $dotenv = Dotenv::createImmutable($dotenvPath);
            $dotenv->load();
        }

        $connectionParams = [
            'dbname'   => $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'cronjob',
            'user'     => $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: 'root',
            'host'     => $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost',
            'driver'   => $_ENV['DB_DRIVER'] ?? getenv('DB_DRIVER') ?: 'pdo_mysql',
        ];

        try {
            $this->conn = DriverManager::getConnection($connectionParams);
        } catch (Exception $e) {
            echo "Erro na conxão: " . $e->getMessage();
            die((string)$e->getCode());
        }
    }

    public function getConn(): Connection
    {
        return $this->conn;
    }

    public function builder(): QueryBuilder
    {
        return $this->getConn()->createQueryBuilder();
    }

    public function prepare(array $params): void
    {
        $this->values = [];
        $this->params = [];
        foreach ($params as $param => $value) {
            $this->values[$param] = ":{$param}";
            $this->params[$param] = is_array($value) || is_object($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
        }
    }

    public function save(string $table, ?array $data): false|int|string|null
    {
        if ($data) {
            $this->prepare($data);
        }

        try {
            $this->conn->createQueryBuilder()
                ->insert($table)
                ->values($this->getValues())
                ->setParameters($this->getParams())
                ->executeQuery();
                
            return $this->conn->lastInsertId();
        } catch (Exception $e) {
            $this->fail = $e;
            return null;
        }
    }
    
    public function update(string $table, array $data, array $identifier): bool
    {
        try {
            $builder = $this->conn->createQueryBuilder()->update($table);
            
            foreach ($data as $key => $value) {
                $builder->set($key, ":$key");
                $builder->setParameter($key, is_array($value) || is_object($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value);
            }
            
            foreach ($identifier as $key => $value) {
                $paramName = "id_$key";
                $builder->andWhere("$key = :$paramName");
                $builder->setParameter($paramName, $value);
            }
            
            $builder->executeQuery();
            return true;
        } catch (Exception $e) {
            $this->fail = $e;
            return false;
        }
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function fail(): ?Exception
    {
        return $this->fail;
    }
}