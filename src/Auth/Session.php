<?php
namespace tonimoreiraa\essentials\Auth;

use CoffeeCode\DataLayer\Connect;
use tonimoreiraa\essentials\User;
use Exception;

class Session
{
    CONST TIMEOUT = 5 * 60; // tempo da sessão em segundos


    /** Retorna token da sessão
     * @return String
     * @throws \Exception
     */
    public static function getToken(): String
    {
        if(session_status() !== PHP_SESSION_ACTIVE){
            session_start();
        }
        // verifica se sessão esta setada
        if(empty($_SESSION['token'])){
            throw new Exception('Você deve iniciar uma sessão antes.');
        }

        return $_SESSION['token'];
    }

    /** Retorna usuário da sessão
     * @return User
     * @throws \Exception
     */
    public static function getUser(): User
    {
        if(session_status() != PHP_SESSION_ACTIVE){
            session_start();
        }
        // verifica se sessão esta setada
        if(empty($_SESSION['user'])){
            throw new \Exception('Você deve iniciar uma sessão antes.');
        }

        return unserialize($_SESSION['user']);
    }

    /** Retorna se sessão está ativa
     * @return bool True para está ativo, False para não está ativo ou sessão inválida
     */
    public static function isActive(): bool
    {
        // verifica se sessão esta setada
        if(session_status() != PHP_SESSION_ACTIVE){
            session_start();
        }
        if(empty($_SESSION['token']) || empty($_SESSION['user'])){
            return false;
        }

        // lê usuário e token
        $user = unserialize($_SESSION['user']);
        $token = $_SESSION['token'];

        // busca na db
        $db = Connect::getInstance();
        $verify = $db->prepare('SELECT * FROM sessions WHERE data = ? AND user_id = ? AND is_valid = true LIMIT 1;');
        $verify->execute([$token, $user->id]);

        // verifica se token existe na db
        if($verify->rowCount() == 0){
            return false;
        }

        $session = $verify->fetch();

        // verifica se token expirou
        if(strtotime($session->valid_to) <= strtotime('now')){
            return false;
        }

        $new_user = new User();
        $new_user = $new_user->find('is_active = true AND is_deleted = false AND id = :id LIMIT 1', http_build_query(['id' => $user->id]));

        // verifica se usuario existe
        if($new_user->count() == 0){
            return false;
        } else {
            $new_user = $new_user->fetch();
        }

        return true;
    }

    /** Inicia sessão
     * @param User $user Usuário para abrir sessão
     * @param null $timeout Timeout em minutos
     * @return string Token da sessão
     * @throws \Exception
     */
    public static function start(User $user, $timeout = NULL): string
    {
        self::destroy();

        session_start();

        if(empty($user->id)){
            throw new \Exception('Usuário inválido.');
        }

        $token = bin2hex(random_bytes(20));

        // TIMEOUT
        $timeout = (!empty($timeout)) ? ($timeout * 60) : (self::TIMEOUT * 60);
        $timeout = date('Y-m-d H:i:s', strtotime('now +'.$timeout.' seconds'));

        $db = Connect::getInstance();
        $insert = $db->prepare("INSERT INTO sessions (data, user_id, valid_to, ip_addr) VALUES (:token, :user_id, :timeout, :ip);");
        $insert->execute([
            'token' => $token,
            'user_id' => $user->id,
            'timeout' => $timeout,
            'ip' => $_SERVER['REMOTE_ADDR']]
        );

        $user->last_ipaddr = $_SERVER['REMOTE_ADDR'];
        $user->last_login = date('Y-m-d H:i:s');
        $user->save();

        $_SESSION['token'] = $token;
        $_SESSION['user'] = serialize($user);

        return $token;
    }

    /** Finaliza a sessão
     * @return void
     */
    public static function destroy(): void
    {
        if(self::isActive()){
            $token = $_SESSION['token'];
            $db = Connect::getInstance();
            $insert = $db->prepare("UPDATE sessions SET is_valid = false WHERE data = ?;");
            $insert->execute([$token]);
        }
        unset($_SESSION);
        if(session_status() == PHP_SESSION_ACTIVE){
            session_unset();
            session_destroy();
        }
    }
}