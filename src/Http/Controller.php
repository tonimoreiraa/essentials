<?php
namespace tonimoreiraa\essentials\Http;

use \League\Plates\Engine;
use tonimoreiraa\essentials\Auth\Session;

class Controller
{
    protected bool $verify_session = true;

    public function __construct()
    {
        if($this->verify_session AND !Session::isActive()){
            header('Location: '.URL_BASE.'/login');
        }
    }

    public static function getEngine(): Engine
    {
        return new Engine(__ROOT__.'/Front/Pages');
    }

    static public function verifyPermission(int $permission, Object $object): void
    {
        try {
            $acl = new ACL();
            $acl = $acl->getPermissionOnTarget($object);
        } catch (\Exception $e){
            $acl = ACL::EMPTY;
        }
        if($acl < $permission){
            self::error(403);
        }
    }

    static public function error($code): void
    {
        try {
            $user = Session::getUser()->id;
        } catch (\Exception $e) {
            $user = 'Usuário não conectado';
        }
        $GLOBALS['log']->debug("Erro com código {$code}", [
            'server' => $_SERVER,
            'user' => $user
        ]);

        header('Location: '.URL_BASE.'/error/'.$code);
        exit;
    }
}