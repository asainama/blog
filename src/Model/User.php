<?php

namespace App\Model;

class User
{
    private $id;

    private $username;

    private $password;

    private $email;

    private $validate;

    private $code;

    private $role_id;
    /**
     * Get the value of id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the value of username
     *
     * @return  self
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get the value of password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of password
     *
     * @return  self
     */
    public function setPassword($password)
    {
        if (empty($password)) {
            $this->password = null;
        } elseif (!empty($password)) {
            $this->password = password_hash($password, PASSWORD_BCRYPT);
        }
        return $this;
    }

    /**
     * Get the value of email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of validate
     */
    public function getValidate()
    {
        return $this->validate;
    }

    /**
     * Set the value of validate
     *
     * @return  self
     */
    public function setValidate($validate)
    {
        $this->validate = $validate;

        return $this;
    }

    /**
     * Get the value of code
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set the value of code
     *
     * @return  self
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    public function getArrayFromObject(): ?array
    {
        return get_object_vars($this);
    }

    /**
     * Get the value of role
     */
    public function getRoleId(): int
    {
        return (int)$this->role_id;
    }

    /**
     * Set the value of role
     *
     * @return  self
     */
    public function setRoleId($role)
    {
        $this->role_id = (int)$role;

        return $this;
    }
}
