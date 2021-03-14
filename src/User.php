<?php
namespace tonimoreiraa\essentials;

use \tonimoreiraa\essentials\Traits\Jobs;

class User extends DataLayer
{
    use Jobs;
    use \tonimoreiraa\essentials\Traits\Company;

    public function __construct()
    {
        parent::__construct('users', ['username', 'password', 'completename', 'jobs']);
    }

    /** Seta senha do usuário
     * @param string $pass Uma senha segura
     * @return void
     */
    public function setPassword(string $pass): void
    {
        $this->password = password_hash($pass, PASSWORD_ARGON2ID);
    }
}