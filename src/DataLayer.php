<?php

namespace tonimoreiraa\essentials;

use Exception;
use PDO;
use PDOException;
use stdClass;
use tonimoreiraa\essentials\ACL\ACL;

/**
 * Class DataLayer
 * @package CoffeeCode\DataLayer
 */
abstract class DataLayer
{
    use CrudTrait;

    /** @var string $entity database table */
    protected $entity;

    /** @var string $primary table primary key field */
    protected $primary;

    /** @var array $required table required fields */
    protected $required;

    /** @var string $timestamps control created and updated at */
    protected $timestamps;

    /** @var string */
    protected $statement;

    /** @var string */
    protected $params;

    /** @var string */
    protected $group;

    /** @var string */
    protected $order;

    /** @var int */
    protected $limit;

    /** @var int */
    protected $offset;

    /** @var \PDOException|null */
    protected $fail;

    /** @var object|null */
    protected $data;

    /**
     * DataLayer constructor.
     * @param string $entity
     * @param array $required
     * @param string $primary
     * @param bool $timestamps
     */
    public function __construct(string $entity, array $required, string $primary = 'id', bool $timestamps = true)
    {
        $this->entity = $entity;
        $this->primary = $primary;
        $this->required = $required;
        $this->timestamps = $timestamps;
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        if (empty($this->data)) {
            $this->data = new stdClass();
        }

        $this->data->$name = $value;
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data->$name);
    }

    /**
     * @param $name
     * @return string|null
     */
    public function __get($name)
    {
        $method = $this->toCamelCase($name);
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        if (method_exists($this, $name)) {
            return $this->$name();
        }

        return ($this->data->$name ?? null);
    }

    /**
     * @return object|null
     */
    public function data(): ?object
    {
        return $this->data;
    }

    /**
     * @return PDOException|Exception|null
     */
    public function fail()
    {
        return $this->fail;
    }

    /**
     * @param string|null $terms
     * @param string|null $params
     * @param string $columns
     * @return DataLayer
     */
    public function find(?string $terms = null, ?string $params = null, string $columns = "*"): DataLayer
    {
        if ($terms) {
            $this->statement = "SELECT {$columns} FROM {$this->entity} WHERE {$terms}";
            parse_str($params, $this->params);
            return $this;
        }

        $this->statement = "SELECT {$columns} FROM {$this->entity}";
        return $this;
    }

    /**
     * @param int $id
     * @param string $columns
     * @return DataLayer|null
     */
    public function findById(int $id, string $columns = "*"): ?DataLayer
    {
        return $this->find("{$this->primary} = :id", "id={$id}", $columns)->fetch();
    }

    /**
     * @param string $column
     * @return DataLayer|null
     */
    public function group(string $column): ?DataLayer
    {
        $this->group = " GROUP BY {$column}";
        return $this;
    }

    /**
     * @param string $columnOrder
     * @return DataLayer|null
     */
    public function order(string $columnOrder): ?DataLayer
    {
        $this->order = " ORDER BY {$columnOrder}";
        return $this;
    }

    /**
     * @param int $limit
     * @return DataLayer|null
     */
    public function limit(int $limit): ?DataLayer
    {
        $this->limit = " LIMIT {$limit}";
        return $this;
    }

    /**
     * @param int $offset
     * @return DataLayer|null
     */
    public function offset(int $offset): ?DataLayer
    {
        $this->offset = " OFFSET {$offset}";
        return $this;
    }

    /**
     * @param bool $all
     * @return mixed
     */
    public function fetch(bool $all = false)
    {
        try {
            $stmt = Connect::getInstance()->prepare($this->statement . $this->group . $this->order . $this->limit . $this->offset);
            $stmt->execute($this->params);

            if (!$stmt->rowCount()) {
                $fetch = [];
            } else if ($all) {
                $fetch = $stmt->fetchAll(PDO::FETCH_CLASS, static::class);
            } else {
                $fetch = $stmt->fetchObject(static::class);
            }
            
        } catch (PDOException $exception) {
            $this->fail = $exception;
            return [];
        }

        if(!empty($this->required_permission) OR is_int($this->required_permission)){
            $acl = new ACL();
            if(is_array($fetch)){
                $return = [];
                foreach($fetch as $obj){
                    if($acl->getPermissionOnTarget($obj) >= $this->required_permission){
                        array_push($return, $obj);
                    }
                }
                return $return ?? null;
            } else if(is_object($fetch)){
                if($acl->getPermissionOnTarget($fetch) >= $this->required_permission){
                    return $fetch;
                }
            }

        } else {
            return $fetch;
        }
        
        return [];
    }

    /**
     * @return int
     */
    public function count(): int
    {
        $stmt = Connect::getInstance()->prepare($this->statement);
        $stmt->execute($this->params);
        return $stmt->rowCount();
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        $primary = $this->primary;
        $id = null;

        if ($this->timestamps) {
            if(!is_string($this->is_active)){
                $this->is_active = isset($this->is_active) ? ($this->is_active ? 'true' : 'false') : 'true';
            }
            if(!is_string($this->is_deleted)){
                $this->is_deleted = $this->is_deleted ? 'true' : 'false';
            }
        }

        try {
            if (!$this->required()) {
                throw new Exception("Preencha os campos necessários");
            }

            /** Update */
            if (!empty($this->data->$primary)) {
                $id = $this->data->$primary;
                $this->update($this->safe(), "{$this->primary} = :id", "id={$id}");
            }

            /** Create */
            if (empty($this->data->$primary)) {
                $id = $this->create($this->safe());
            }

            if (!$id) {
                return false;
            }

            $this->data = $this->findById($id)->data();
            return true;
        } catch (Exception $exception) {
            $this->fail = $exception;
            return false;
        }
    }

    /** Procura entidade pelo campo "name"
     * @param string $name
     */
    public function findByName(string $name)
    {
        return $this->find("name = :name", http_build_query(['name' => $name]));
    }

    /**
     * @return bool
     */
    public function destroy(): bool
    {
        $primary = $this->primary;
        $id = $this->data->$primary;

        if (empty($id)) {
            return false;
        }

        return $this->delete("{$this->primary} = :id", "id={$id}");
    }

    /**
     * @return bool
     */
    protected function required(): bool
    {
        $data = (array)$this->data();
        foreach ($this->required as $field) {
            if (empty($data[$field]) && !is_int($data[$field])) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return array|null
     */
    protected function safe(): ?array
    {
        $safe = (array)$this->data;
        unset($safe[$this->primary]);
        return $safe;
    }


    /**
     * @param string $string
     * @return string
     */
    protected function toCamelCase(string $string): string
    {
        $camelCase = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
        $camelCase[0] = strtolower($camelCase[0]);
        return $camelCase;
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

        $primary = $this->primary;
        $return .= !empty($this->$primary) ? " ($this->$primary)" : '';

        return $return;
    }
    
    /** Verifica se tem permissão
     * @param int $permission Permissão a ser verificado
     * @return $this
     */
    public function requirePermission(int $permission)
    {
        $this->required_permission = $permission;
        return $this;
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
            array_push($arr, $column->name);
        }

        unset($arr[array_search('id', $arr)]);
        unset($arr[array_search('created_at', $arr)]);
        unset($arr[array_search('updated_at', $arr)]);

        return $arr;
    }

    /* Seta data pelo array
     * @param array $data Array com data
    */
    public function setDataByArr(array $data)
    {
        foreach ($this->getEntityFields() as $field){
            $this->$field = $data[$field] ?? null;
        }
    }
}
