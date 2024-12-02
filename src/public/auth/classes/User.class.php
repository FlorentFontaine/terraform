<?php

require_once __DIR__ . '/../../dbClasses/AccesDonnees.php';

use CICD\Lockers\Services\Client;

class User
{
    protected $lockers = null;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->lockers = new Client;
    }

    /**
     * Retrieve user by email
     */
    public function getUserByUsername($email)
    {
        return $this->lockers->getUserByEmail($email);
    }

    /**
     * Accept File
     */
    public function acceptFile($params)
    {
        return $this->lockers->acceptFile($params["fileId"]);
    }

    /**
     * Create user
     */
    public function create($user)
    {
        return $this->lockers->createUser($user);
    }

    /**
     * Update user
     */
    public function update($id, $user)
    {
        return $this->lockers->updateUser($id, $user);
    }

    /**
     * Remove access
     */
    public function removeAccess($id, $params)
    {
        return $this->lockers->removeAccessUser($id, $params);
    }
}
