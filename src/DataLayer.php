<?php
namespace tonimoreiraa\essentials;

use CoffeeCode\DataLayer as Core;
use CoffeeCode\DataLayer\Connect;
use tonimoreiraa\ACL\ACL;

class DataLayer extends Core\DataLayer
{
    public function __construct(string $entity, array $required, string $primary = 'id', bool $timestamps = true)
    {
        parent::__construct($entity, $required, $primary, $timestamps);
    }

    /**
     * @param bool $all
     * @return mixed
     */
    public function fetch(bool $all = false): mixed
    {
        $fetch = parent::fetch($all);
        $acl = new ACL();

        if(!empty($this->permission)){
            if(is_array($fetch)){

                $return = [];
                foreach($fetch as $obj){

                    try{
                        $permission = $acl->getPermissionOnTarget($obj);
                    } catch (\Exception $e){
                        $permission = ACL::EMPTY;
                    }

                    if($permission >= $this->permission){
                        array_push($return, $obj);
                    }
                }
                return $return ?? null;

            } else if(is_object($fetch)){

                try {
                    $permission = $acl->getPermissionOnTarget($fetch);
                } catch (\Exception $e) {
                    $permission = ACL::EMPTY;
                }

                if($permission >= $this->permission){
                    return $fetch;
                }
            }

        } else {
            return $fetch;
        }
        return [];
    }

    /** Retorna string para frontend
     * @return string
     */
    public function __toString(): string
    {
        /** CONFIGURAR CLASSE */
        $class_type = ucfirst(substr(strrchr(get_class($this), "\\"), 1));

        // define identificação
        $camps = ['completename', 'title', 'name', 'username'];
        foreach ($camps as $camp){
            if(!empty($this->$camp)){
                $return = $this->$camp;
                break;
            }
        }
        $return = $return ?? "{$class_type} desconhecido";

        // páginas
        $page = match($class_type){
            'Usuário', 'Client' => 'usuarios',
            default => lcfirst($class_type).'s'
        };

        try{
            $control = (new ACL())->getPermissionOnTarget($this);
        } catch (\Exception){
            $control = ACL::EMPTY;
        }

        $return .= (!empty($this->id) AND $control >= ACL::READ) ? " ({$this->id})" : '';
        $return = "<span title='{$class_type}'>{$return}</span>";

        if($control >= ACL::MODIFY AND !empty($this->id)){
            $urlbase = URL_BASE;
            $return = <<<HTML
            <a href="{$urlbase}/{$page}/{$this->id}" title="{$class_type}">{$return}</a>
            HTML;
        }

        return $return;
    }

    /** Pega campos da entidade
     * @return array Array com campos
    */
    public function getEntityFields(): array
    {
        $arr = [];

        $db = Connect::getInstance();
        $search = $db->prepare('SELECT column_name as name FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = :table_name');
        $search->execute([
            'table_name' => $this->entity
        ]);

        while($column = $search->fetch()){
            $prohibited = ['id', 'created_at', 'updated_at'];
            if(!in_array($column->name, $prohibited)){
                array_push($arr, $column->name);
            }
        }

        return $arr;
    }

    /* Seta data pelo array
     * @param array $data Array com data
    */
    public function setDataByArr(array $data)
    {
        foreach ($this->getEntityFields() as $field){
            switch ($field){
                default:
                    $this->$field = $data[$field] ?? null;
            }
        }
    }

    /** Verifica se tem permissão
     * @param int $permission Permissão a ser verificado
     * @return $this
     */
    public function requirePermission(int $permission)
    {
        $this->permission = $permission;
        return $this;
    }

    /** Procura entidade pelo campo "name"
     * @param string $name
     */
    public function findByName(string $name)
    {
        return $this->find("name = :name", http_build_query(['name' => $name]));
    }

    public function save(): bool
    {
        if ($this->timestamps) {
            $this->data->is_active = match($this->data->is_active){
                false => 'false',
                default => 'true'
            };
            $this->data->is_deleted = match ($this->data->is_deleted){
                true => 'true',
                default => 'false'
            };
        }
        return parent::save();
    }

    /** Verifica se já existe registro
     * @param string $name Nome para verificar
     * @param string $camp Coluna para procurar
     * @return bool
     */
    public function verifyAlreadyExists(string $name, string $camp = 'name'): bool
    {
        $db = Connect::getInstance();

        $search = $db->prepare("SELECT id FROM {$this->entity} WHERE {$camp} = ?;");
        $search->execute([$name]);

        return $search->rowCount() ? true : false;
    }

    /**
     * @param string $terms
     * @param string|null $params
     * @return bool
     */
    public function delete(string $terms, ?string $params): bool
    {
        try {
            $stmt = Connect::getInstance()->prepare("UPDATE {$this->entity} SET is_deleted = true, is_active = false WHERE {$terms}");
            if ($params) {
                parse_str($params, $params);
                $stmt->execute($params);
                return true;
            }

            $stmt->execute();
            return true;
        } catch (PDOException $exception) {
            $this->fail = $exception;
            return false;
        }
    }

    protected function required(): bool
    {
        $data = (array)$this->data();
        foreach ($this->required as $field) {
            if (isset($data[$field]) && $data[$field] != 0) {
                return false;
            }
        }
        return true;
    }
}
