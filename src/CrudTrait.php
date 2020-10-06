<?php

namespace HnrAzevedo\Datamanager;

use HnrAzevedo\Datamanager\DatamanagerException;
use Exception;
use PDO;

trait CrudTrait{

    protected ?DatamanagerException $fail = null;
    protected string $lastQuery = '';
    protected array $lastData = [];

    protected function check_fail()
    {
        if(!is_null($this->fail)){
            throw $this->fail;
        }
    }

    protected function transaction(string $transaction): ?bool
    {
        if(array_key_exists($transaction,['begin','commit','rollback'])){
            throw new DatamanagerException("{$transaction} é um estado inválido para transações.");
        }

        if(!Connect::getInstance()->inTransaction()){
           return Connect::getInstance()->beginTransaction();
        }

        switch ($transaction) {
            case 'commit': return Connect::getInstance()->commit();
            case 'rollback': return Connect::getInstance()->rollBack();
        }
        return false;
    }

    protected function select(string $query,array $data): ?array
    {
        try{
            $stmt = Connect::getInstance()->prepare("{$query}");

            $this->lastQuery = "{$query}";
            $this->lastData = $data;

            $stmt->execute($data);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $exception){
            $this->fail = new DatamanagerException($exception->getMessage(), $exception->getCode(), $exception);
        }
        return [];
    }

    protected function describe(): array
    {
        try{
            $stmt = Connect::getInstance()->prepare("DESCRIBE {$this->table}");

            $this->lastQuery = "DESCRIBE {$this->table}";
            $this->lastData = [];

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }catch(Exception $exception){
            $this->fail = new DatamanagerException($exception->getMessage(), $exception->getCode(), $exception);
            return [];
        }
    }

    protected function insert(array $data): ?string
    {
        try {
            $columns = implode(", ", array_keys($data));
            $values = ":" . implode(", :", array_keys($data));

            $stmt = Connect::getInstance()->prepare("INSERT INTO {$this->table} ({$columns}) VALUES ({$values})");

            $this->checkNull($data);
            $dataInsert = $this->filter($data);

            $this->lastQuery = "INSERT INTO {$this->table} ({$columns}) VALUES ({$values})";
            $this->lastData = $dataInsert;

            $stmt->execute($dataInsert);

            return Connect::getInstance()->lastInsertId();
        } catch (Exception $exception) {
            $this->fail = new DatamanagerException($exception->getMessage(), $exception->getCode(), $exception);
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

            parse_str($params, $arr);

            $dataUpdate = $this->filter(array_merge($data, $arr));

            $stmt = Connect::getInstance()->prepare("UPDATE {$this->table} SET {$dateSet} WHERE {$terms}");

            $this->lastQuery = "UPDATE {$this->table} SET {$dateSet} WHERE {$terms}";
            $this->lastData = $dataUpdate;

            $this->checkNull($data);
            $stmt->execute($dataUpdate);

            return ($stmt->rowCount() ?? 1);
        } catch (Exception $exception) {
            $this->fail = new DatamanagerException($exception->getMessage(), $exception->getCode(), $exception);
            return null;
        }
    }

    public function delete(string $terms, ?string $params): bool
    {
        try {
            $stmt = Connect::getInstance()->prepare("DELETE FROM {$this->table} WHERE {$terms}");

            $this->lastQuery = "DELETE FROM {$this->table} WHERE {$terms}";
            $this->lastData = [];

            if($params){
                parse_str($params, $arr);
                $this->lastData = $arr;

                $stmt->execute($arr);
                return true;
            }

            $stmt->execute();
            return true;
        } catch (Exception $exception) {
            $this->fail = new DatamanagerException($exception->getMessage(), $exception->getCode(), $exception);
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
