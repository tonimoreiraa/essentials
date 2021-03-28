<?php
namespace tonimoreiraa\essentials\Auth;

use CoffeeCode\DataLayer\Connect;
use tonimoreiraa\essentials\User;
use Exception;

class Auth
{
    public static function login(string $login, string $password, bool $session = true): bool
    {
        $db = Connect::getInstance();
        
        // verifica se todos os campos estão completos
        if(empty($login) OR empty($password)){
            return false;
        }

        // procura usuário
        $user = new User();
        $user = $user->find('username = :login AND is_active = true AND is_deleted = false', http_build_query([
            'login' => $login
        ]))->limit(1);

        // verifica se usuário é válido
        if(empty($user->count())){
            return false;
        } else {
            $user = $user->fetch();
        }
        
        // verifica empresa
        if(!boolval($user->getCompany()->is_active)){
            return false;
        }

        // verifica senha
        if(!password_verify($password, $user->password)){
            return false;
        } else {
            try{
                Session::start($user);
            } catch (Exception $e){
                return false;
            }
            return true;
        }

    }
}
