<?php

namespace App\Helpers;

use App\Config\Database;

class QueryBuilder
{
    private $select;

    private $from;

    private $where = [];

    private $group;

    private $order;

    private $limit;

    private $offset;
    
    private $pdo;

    private $params;

    private $delete;

    private $update;

    private $set = [];

    private $insert;

    private $values = [];

    private $columns = [];

    public function __construct()
    {
        $this->pdo = Database::connect();
    }

    public function delete(): self
    {
        $this->delete = "DELETE";
        return $this;
    }
    public function update(string $table): self
    {
        $this->update = "UPDATE $table";
        return $this;
    }
    public function insert(string $table): self
    {
        $this->update = "INSERT INTO $table";
        return $this;
    }
    public function set(string ...$condition): self
    {
        $this->set = array_merge($this->set, $condition);
        return $this;
    }

    public function columns(string ...$condition): self
    {
        $this->columns = array_merge($this->set, $condition);
        return $this;
    }
    public function values(string ...$condition): self
    {
        $this->values = array_merge($this->set, $condition);
        return $this;
    }

    public function from(string $table, ?string $alias = null): self
    {
        if ($alias) {
            $this->from[$alias] = $table;
        } else {
            $this->from[] = $table;
        }
        return $this;
    }
    public function select(string ...$fields): self
    {
        $this->select = $fields;
        return $this;
    }
    public function orderBy(string $key, string $direction = "DESC"): self
    {
        $direction = strtoupper($direction);
        if (!in_array($direction, ["DESC","ASC"])) {
            $this->order = $key;
        } else {
            $this->order[] = "$key $direction";
        }
        return $this;
    }
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }
    public function where(string ...$condition): self
    {
        $this->where = array_merge($this->where, $condition);
        return $this;
    }
    public function count(): int
    {
        $this->select("COUNT(id)");
        return $this->execute()
            ->fetchColumn();
    }
    public function page(int $page): self
    {
        return $this->offset($this->limit * ($page - 1));
    }
    public function params(array $params): self
    {
        $this->params = $params;
        return $this;
    }
    public function __toString()
    {
        if ($this->delete) {
            $parts = [$this->delete];
        } elseif ($this->update) {
            $parts = [$this->update];
        } elseif ($this->insert) {
            $parts = [$this->insert];
        } else {
            $parts = ['SELECT'];
        }
        if ($this->select) {
            $parts[] = join(', ', $this->select);
        } else {
            if (!$this->delete & !$this->update & !$this->insert) {
                $parts[] = "*";
            }
        }
        if (empty($this->update) & empty($this->insert)) {
            $parts[] = 'FROM';
            $parts[] = $this->buildFrom();
        }
        if (!empty($this->set)) {
            $parts[] = "SET";
            $parts[] = " " . join(' , ', $this->set) . '';
        }
        if (!empty($this->columns)) {
            $parts[] = "(" . join(') AND (', $this->columns) . ')';
        }
        if (!empty($this->values)) {
            $parts[] = "VALUES";
            $parts[] = '("' . join('") AND ("', str_replace("§", '","', $this->values)) . '")';
        }
        if (!empty($this->where)) {
            $parts[] = "WHERE";
            $parts[] = "(" . join(') AND (', $this->where) . ')';
        }
        if (!empty($this->order)) {
            $parts[] = "ORDER BY " . implode(', ', $this->order);
        }
        if ($this->limit > 0) {
            $parts[] = "LIMIT " . $this->limit;
        }
        if ($this->offset !== null) {
            $parts[] = "OFFSET " . $this->offset;
        }
        return join(' ', $parts);
    }

    private function buildFrom(): string
    {
        $from = [];
        foreach ($this->from as $key => $value) {
            if (is_string($key)) {
                $from[] = "$value as $key";
            } else {
                $from[] = $value;
            }
        }
        return join(', ', $from);
    }
    public function execute()
    {
        $query = $this->__toString();
        if ($this->params) {
            $statement = $this->pdo->prepare($query);
            $statement->execute($this->params);
            return $statement;
        }
        return $this->pdo->query($query);
    }

    public function getLastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
}
