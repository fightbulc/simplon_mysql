<?php

namespace Test\Crud;

use Simplon\Mysql\Crud\CrudModel;

/**
 * @package Test\Crud
 */
class NameModel extends CrudModel
{
    const COLUMN_ID = 'id';
    const COLUMN_NAME = 'name';
    const COLUMN_AGE = 'age';

    /**
     * @var int
     */
    protected $id;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var int
     */
    protected $age;

    /**
     * @return int
     */
    public function getId(): int
    {
        return (int)$this->id;
    }

    /**
     * @param int $id
     *
     * @return NameModel
     */
    public function setId(int $id): NameModel
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return NameModel
     */
    public function setName(string $name): NameModel
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getAge(): int
    {
        return (int)$this->age;
    }

    /**
     * @param int $age
     *
     * @return NameModel
     */
    public function setAge(int $age): NameModel
    {
        $this->age = $age;

        return $this;
    }
}