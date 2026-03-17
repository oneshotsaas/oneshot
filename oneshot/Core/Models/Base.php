<?php

namespace OneShot\Core\Models;

abstract class Base extends \CodeIgniter\Model
{
    protected $DBGroup        = 'default';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;
    protected $createdField   = 'created_at';
    protected $updatedField   = 'updated_at';
    protected $deletedField   = 'deleted_at';
    protected $dateFormat     = 'datetime';
    protected $returnType     = 'object';

    public function getAll(array $params = []): array
    {
        $builder = $this->builder();

        foreach ($params as $key => $val) {
            $builder->where($this->table . '.' . $key, $val);
        }

        return $builder->get()->getResultObject();
    }

    public function getOne(array $params): object|null
    {
        $builder = $this->builder();

        foreach ($params as $key => $val) {
            $builder->where($this->table . '.' . $key, $val);
        }

        return $builder->limit(1)->get()->getRowObject() ?: null;
    }

    public function getById(int $id): object|null
    {
        return $this->find($id) ?: null;
    }

    public function add(array $data): int
    {
        $this->insert($data);
        return (int) $this->getInsertID();
    }

    public function addGet(array $data): object
    {
        $id = $this->add($data);
        return $this->getById($id);
    }

    public function getOrAdd(array $data, string $field): object
    {
        $existing = $this->getOne([$field => $data[$field]]);
        if ($existing !== null) {
            return $existing;
        }

        return $this->addGet($data);
    }
}
