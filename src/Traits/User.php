<?php
namespace tonimoreiraa\essentials\Traits;

use CoffeeCode\DataLayer\DataLayer;
use tonimoreiraa\essentials\User as Core;

trait User
{
    /* Seta usuário
     * @param Core $user Usuário válido
     */
    public function setUser(Core $user)
    {
        $this->user_id = $user->id;
    }

    /* Procura usuário
     * @return Core|DataLayer
    */
    public function getUser(): Core|DataLayer
    {
        $user = new Core();
        $user = $user->findById($this->user_id);
        return $user;
    }

    /* Procura $this pelo usuário
     * @param Core $user
    */
    public function findByUser(Core $user)
    {
        return $this->find('user_id = :id', http_build_query(['id' => $user->id]));
    }
}