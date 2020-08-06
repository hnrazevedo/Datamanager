<?php

namespace HnrAzevedo\Datamanager;

use Exception;
use PDOException;
use PDO;

trait CrudTrait{

    protected ?Exception $fail = null;

    protected function check_fail()
    {
        if(!is_null($this->fail)){
            throw $this->fail;
        }
    }

    protected function transaction(string $transaction): ?bool
    {
        switch ($transaction) {
            case 'begin': return (Connect::getInstance()->inTransaction()) ? Connect::getInstance()->beginTransaction() : false;
            case 'commit': return (Connect::getInstance()->inTransaction()) ? Connect::getInstance()->commit() : false;
            case 'rollback': return (Connect::getInstance()->inTransaction()) ? Connect::getInstance()->rollBack() : false;
        }
        return false;
    }

    protected function select(string $query,array $data): ?array
    {
        try{
            $stmt = Connect::getInstance()->prepare("{$query}");
            $stmt->execute($data);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $exception){
            $this->fail = $exception;
        }
        return [];
    }

    protected function describe(): ?array
    {
        try{
            $stmt = Connect::getInstance()->prepare("DESCRIBE {$this->table}");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $exception){
            $this->fail = $exception;
            return null;
        }
    }

    protected function insert(array $data): ?int
    {
        try {
            $columns = implode(", ", array_keys($data));
            $values = ":" . implode(", :", array_keys($data));

            $stmt = Connect::getInstance()->prepare("INSERT INTO {$this->table} ({$columns}) VALUES ({$values})");

            $stmt->execute($this->filter($data));

            return Connect::getInstance()->lastInsertId();
        } catch (PDOException $exception) {
            $this->fail = $exception;
            return null;
        }
    }

    protected function update(array $data, string $terms, string $params): ?int
    {
        try {
            $dateSet = [];
            foreach ($data as $bind => $value) {
                $dateSet[] = "{$bind} = :{$bind}";
            }
            $dateSet = implode(", ", $dateSet);
            parse_str($params, $params);

            $stmt = Connect::getInstance()->prepare("UPDATE {$this->table} SET {$dateSet} WHERE {$terms}");
            $stmt->execute($this->filter(array_merge($data, $params)));
            return ($stmt->rowCount() ?? 1);
        } catch (PDOException $exception) {
            $this->fail = $exception;
            return null;
        }
    }

    public function delete(string $terms, ?string $params): bool
    {
        try {
            $stmt = Connect::getInstance()->prepare("DELETE FROM {$this->table} WHERE {$terms}");

            if($params){
                parse_str($params, $params);
                $stmt->execute($params);
                return true;
            }

            $stmt->execute();
            return true;
        } catch (PDOException $exception) {
            $this->fail = $exception;
            return false;
        }
    }

    private function filter(array $data): ?array
    {
        $filter = [];
        foreach ($data as $key => $value) {
            $filter[$key] = (is_null($value) ? null : filter_var($value, FILTER_DEFAULT));
        }
        return $filter;
    }

}
