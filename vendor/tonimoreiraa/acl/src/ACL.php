<?php
namespace ACL;

use stdClass;

/* Controle de acesso
 * @author Toni Moreira <toni (at) itentecnologia (dot) com (dot) br>
 */
class ACL
{
    use CrudACL;

    protected stdClass $acl;
    public array $objects_to_verify = [];

    const BLOCKED = 0;
    const EMPTY = 1;
    const READ = 2;
    const MODIFY = 3;

    public function __construct()
    {
        $acl = file_get_contents($GLOBALS['ACL_FILE_LOCATION'] ?? __DIR__.'/../files/ACL.json') ?? '{}';
        $acl = json_decode($acl);
        $this->acl = $acl;

        $this->objects_to_verify = $GLOBALS['ACL_OBJECTS_TO_VERIFY'] ?? [];
    }

    /* Seta permissão para objeto
     * @param Object $base_object Objeto base
     * @param string $permission_name Permissão a ser procurada
     * @return int Valor da permissão
     */
    public function setPermission(Object $base_object, string $permission_name, int $permission)
    {
        $class = get_class($base_object);
        
        $this->modifyACL([$class, $base_object->id, 'permissions', $permission_name, $permission]);
    }

    /* Seta permissão de objeto em cima de outro
     * @param Object $base_object Objeto base
     * @param ?string $permission_name Permissão a ser procurada
     * @return int Valor da permissão
     */
    public function setPermissionOnTarget(Object $base_object, Object $target_object, int $permission, ?string $permission_name)
    {
        $bo_class = get_class($base_object);
        $to_class = get_class($target_object);
        $permission_name = $permission_name ?? strtolower($to_class).'_control';

        $this->modifyACL([$bo_class, $base_object->id, 'targets', $to_class, $target_object->id, 'permissions', $permission_name, $permission]);
    }

    /* Procura permissão
     * @param string $permission_name Permissão a ser verificada
     * @return int Valor da permissão
     */
    public function getPermission(string $permission_name): int
    {
        $final = ACL::EMPTY;
        foreach($this->objects_to_verify as $object){
            $object = is_object($object) ? [$object] : $object;
            if(is_array($object)){
                foreach($object as $v_object){
                    $class = get_class($v_object);
                    $permission = $this->findOnACL([$class, $v_object->id, 'permissions', $permission_name]);
                    $final = ($permission != ACL::EMPTY) ? $permission : $final;
                }
            }
        }
        return intval($final);
    }

    /* Procura permissão
     * @param Object $target Alvo a ser verificado
     * @param string $permission_name Permissão a ser verificada
     * @return int Valor da permissão
     */
    public function getPermissionOnTarget(Object $target, $permission_name = null): int
    {
        $target_class = get_class($target);
        $permission_name = $permission_name ?? strtolower($target_class).'_control';
        $final = $this->getPermission($permission_name);

        foreach($this->objects_to_verify as $object){

            $object = is_object($object) ? [$object] : $object;

            if(is_array($object)){
                foreach($object as $v_object){

                    $class = get_class($v_object);
                    $permission = $this->findOnACL([$class, $v_object->id, 'targets', $target_class, $target->id, 'permissions', $permission_name]);

                    $final = ($permission != ACL::EMPTY) ? $permission : $final;
                }
            }
        }
        return intval($final);
    }
}