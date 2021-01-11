<?php

namespace HnrAzevedo\Datamanager\Attributes;

use Attribute;

#[Attribute]
class Column
{

    public function __construct(
        private string $name,
        private string $type,
        private int $max,
        private int $min,
        private bool $nullable = false,
        private bool $unique = false,
        private ?string $default = null,
        private bool $primaryKey = false,
        private array $foreignKey = ['table' => null, 'column' => null]
    )
    {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMax(): int
    {
        return $this->max;
    }

    public function getMin(): int
    {
        return $this->min;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function isUnique(): bool
    {
        return $this->unique;
    }

    public function getDefault(): string
    {
        return $this->default;
    }

    public function isPrimaryKey(): bool
    {
        return $this->primaryKey;
    }

    public function isForeignKey(): bool
    {
        return (array_key_exists($this->foreignKey, 'column') && null !== $this->foreignKey['column']);
    }

}
