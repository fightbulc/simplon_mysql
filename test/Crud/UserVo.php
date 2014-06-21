<?php

class UserVo extends \Simplon\Mysql\Crud\SqlCrudVo
{
    protected $crudIgnoreVariables = ['undefined'];
    protected $id;
    protected $name;
    protected $email;
    protected $createdAt;
    protected $updatedAt;
    protected $undefined;

//    /**
//     * @return array
//     */
//    public function crudColumns()
//    {
//        return [
//            'id'        => 'id',
//            'name'      => 'name',
//            'email'     => 'email',
//            'createdAt' => 'created_at',
//            'updatedAt' => 'updated_at',
//        ];
//    }
//
    /**
     * @param bool $isCreateEvent
     *
     * @return bool
     */
    public function crudBeforeSave($isCreateEvent)
    {
        if ($isCreateEvent === true)
        {
            $this->setCreatedAt(time());
        }

        $this->setUpdatedAt(time());

        return true;
    }

    /**
     * @return int
     */
    public function getCreatedAt()
    {
        return (int)$this->createdAt;
    }

    /**
     * @param int $createdAt
     *
     * @return UserVo
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return (string)$this->email;
    }

    /**
     * @param string $email
     *
     * @return UserVo
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return (int)$this->id;
    }

    /**
     * @param int $id
     *
     * @return UserVo
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return (string)$this->name;
    }

    /**
     * @param string $name
     *
     * @return UserVo
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getUpdatedAt()
    {
        return (int)$this->updatedAt;
    }

    /**
     * @param int $updatedAt
     *
     * @return UserVo
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
} 