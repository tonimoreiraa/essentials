<?php
namespace tonimoreiraa\essentials;

use CoffeeCode\DataLayer\DataLayer;

class Company extends DataLayer
{

    use \tonimoreiraa\essentials\Traits\Company;

    public function __construct()
    {
        parent::__construct('companies', ['name', 'completename']);
    }

    /* Procura usuários filhos desta empresa
     * @return User[]
     */
    public function getUsers(): array
    {
        $users = new User();
        $users = $users->find('company_id = :id', http_build_query([
            'id' => $this->id
        ]))->fetch(true);

        return $users ?? [];
    }

    public function save(): bool
    {
        $this->logo = $this->logo ?? 'default-profilephoto.svg';
        return parent::save();
    }
}