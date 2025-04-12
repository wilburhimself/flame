<?php

namespace WilburHimself\Flame;

use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;

/**
 * Flame - Active Record Implementation for CodeIgniter 4
 * 
 * Provides an easy way to implement the active record design pattern
 * in CodeIgniter 4 models
 */
abstract class Flame extends Model implements IFlame
{
    /**
     * @var string The model's table name
     */
    protected $table;
    
    /**
     * @var string The primary key field name
     */
    protected $primaryKey = 'id';
    
    /**
     * @var bool Whether to use auto-increment for primary key
     */
    protected $useAutoIncrement = true;
    
    /**
     * @var string Return type - array, object, or class name
     */
    protected $returnType = 'object';
    
    /**
     * @var bool Whether to use soft deletes
     */
    protected $useSoftDeletes = false;
    
    /**
     * @var array Allowed fields for insert/update
     */
    protected $allowedFields = [];
    
    /**
     * @var string Created at field name
     */
    protected $createdField = 'created_at';
    
    /**
     * @var string Updated at field name
     */
    protected $updatedField = 'updated_at';
    
    /**
     * @var string Deleted at field name
     */
    protected $deletedField = 'deleted_at';
    
    /**
     * @var array Models this model belongs to
     */
    protected $belongs_to = [];
    
    /**
     * @var array Models that belong to this model (one-to-many)
     */
    protected $has_many = [];
    
    /**
     * @var array Models that have many-to-many relationship with this model
     */
    protected $has_and_belongs_to_many = [];
    
    /**
     * @var string Object name (singular version of table name)
     */
    protected $unicode;
    
    /**
     * @var array Field names from the table
     */
    protected $fieldNames = [];

    /**
     * Constructor
     */
    public function __construct(?ConnectionInterface $db = null, ?\CodeIgniter\Validation\Validation $validation = null)
    {
        parent::__construct($db, $validation);
        
        // Load the inflector helper
        \helper('inflector');
        
        // Set unicode (singular table name)
        $this->unicode = \singular($this->table);
        
        // Get all fields
        $fields = $this->db->getFieldData($this->table);
        $this->fieldNames = array_column($fields, 'name');
        
        // Set allowed fields (all fields except primary key)
        $this->allowedFields = array_diff($this->fieldNames, [$this->primaryKey]);
    }

    /**
     * Magic method to handle dynamic finder methods like find_by_fieldname
     *
     * @param string $method The method name
     * @param array $args The method arguments
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (preg_match("/find_by_(.*)/", $method, $found)) {
            if (in_array($found[1], $this->fieldNames)) {
                return $this->get_by($found[1], $args[0]);
            }
        }
        
        // Call parent method if not handled here
        return parent::__call($method, $args);
    }

    /**
     * Get a single record by primary key
     * Will also fetch related models if relationships are defined
     *
     * @param mixed $id The primary key value
     * @return FlameResult|null
     */
    public function get($id)
    {
        $data = $this->find($id);
        
        if (!$data) {
            return null;
        }
        
        // Handle belongs_to relationships
        if (!empty($this->belongs_to)) {
            foreach ($this->belongs_to as $entity) {
                $modelName = ucfirst(\singular($entity));
                $foreignKey = \singular($entity) . '_id';
                
                if (isset($data->$foreignKey)) {
                    $modelClass = "\\App\\Models\\{$modelName}Model";
                    $model = new $modelClass();
                    $data->$entity = $model->find($data->$foreignKey);
                }
            }
        }
        
        // Handle has_many relationships
        if (!empty($this->has_many)) {
            foreach ($this->has_many as $entity) {
                $modelName = ucfirst(\singular($entity));
                $foreignKey = $this->unicode . '_id';
                
                $modelClass = "\\App\\Models\\{$modelName}Model";
                $model = new $modelClass();
                $data->$entity = $model->where($foreignKey, $id)->findAll();
            }
        }
        
        // Handle many-to-many relationships
        if (!empty($this->has_and_belongs_to_many)) {
            foreach ($this->has_and_belongs_to_many as $entity) {
                $pivotTable = $this->table . '_' . $entity;
                $modelName = ucfirst(\singular($entity));
                $foreignKey = \singular($entity) . '_id';
                $localKey = $this->unicode . '_id';
                
                $modelClass = "\\App\\Models\\{$modelName}Model";
                $model = new $modelClass();
                
                // Get IDs from pivot table
                $builder = $this->db->table($pivotTable);
                $query = $builder->select($foreignKey)
                                 ->where($localKey, $id)
                                 ->get();
                                 
                $relatedIds = array_column($query->getResultArray(), $foreignKey);
                
                if (!empty($relatedIds)) {
                    $data->$entity = $model->whereIn($model->primaryKey, $relatedIds)->findAll();
                } else {
                    $data->$entity = [];
                }
            }
        }
        
        return new FlameResult($this, $data);
    }

    /**
     * Get a list of records with optional pagination and conditions
     *
     * @param array $args Optional conditions
     * @param int $num Optional limit
     * @param int $offset Optional offset
     * @return array|null Array of objects or null if no results
     */
    public function get_list($args = null, $num = null, $offset = null)
    {
        if (isset($args) && is_array($args)) {
            foreach ($args as $k => $v) {
                $this->where($k, $v);
            }
        }
        
        if ($num !== null) {
            $this->limit($num, $offset);
        }
        
        $results = $this->findAll();
        
        if (empty($results)) {
            return null;
        }
        
        $objects = [];
        foreach ($results as $result) {
            $objects[] = $this->get($result->{$this->primaryKey});
        }
        
        return $objects;
    }

    /**
     * Get records by a specific field value
     *
     * @param string $field The field name
     * @param mixed $value The field value
     * @return array|null Array of objects or null if no results
     */
    public function get_by($field, $value)
    {
        $results = $this->where($field, $value)->findAll();
        
        if (empty($results)) {
            return null;
        }
        
        $objects = [];
        foreach ($results as $result) {
            $objects[] = $this->get($result->{$this->primaryKey});
        }
        
        return $objects;
    }

    /**
     * Add a new record
     *
     * @param object|array $data The data to insert
     * @return int|bool The insert ID or false on failure
     */
    public function add($data)
    {
        return $this->insert($data, true);
    }

    /**
     * Update a record
     *
     * @param mixed $id The primary key value
     * @param object|array $data The data to update
     * @return bool Success/failure
     */
    public function update($id, $data)
    {
        return parent::update($id, $data);
    }

    /**
     * Delete a record
     *
     * @param mixed $id The primary key value
     * @return bool Success/failure
     */
    public function delete($id = null)
    {
        if ($id === null) {
            return false;
        }
        
        return parent::delete($id);
    }

    /**
     * Define a belongs_to relationship
     *
     * @param string $entity The entity name
     * @return $this
     */
    public function belongs_to($entity)
    {
        $this->belongs_to[] = $entity;
        return $this;
    }

    /**
     * Define a has_and_belongs_to_many relationship
     *
     * @param string $entity The entity name
     * @return $this
     */
    public function has_and_belongs_to_many($entity)
    {
        $this->has_and_belongs_to_many[] = $entity;
        return $this;
    }

    /**
     * Define a has_many relationship
     *
     * @param string $entity The entity name
     * @return $this
     */
    public function has_many($entity)
    {
        $this->has_many[] = $entity;
        return $this;
    }

    /**
     * Populate an object from post data
     *
     * @return object
     */
    public function populate()
    {
        $request = \service('request');
        $params = $request->getPost($this->unicode);
        
        $data = new \stdClass();
        foreach ($this->fieldNames as $field) {
            if ($field == $this->primaryKey) continue;
            $value = $params[$field] ?? '';
            $data->$field = $value;
        }
        
        return $data;
    }

    /**
     * Generate an object from post data
     *
     * @return object
     */
    public function generate_from_post()
    {
        $request = \service('request');
        $data = new \stdClass();
        
        if ($request->getMethod() === 'post') {
            foreach ($this->fieldNames as $field) {
                $value = $request->getPost($field) ?? '';
                $data->$field = $value;
            }
        } else {
            foreach ($this->fieldNames as $field) {
                $data->$field = '';
            }
        }
        
        return $data;
    }

    /**
     * Search for records based on field values
     *
     * @param array $request The search parameters
     * @param array $exclude Optional IDs to exclude
     * @return array
     */
    public function search($request, $exclude = null)
    {
        $builder = $this->builder();
        
        if ($exclude) {
            $builder->whereNotIn($this->primaryKey, $exclude);
        }
        
        $data = [];
        foreach ($request as $k => $v) {
            if (in_array($k, $this->fieldNames)) {
                $data[$k] = $v;
            }
        }
        
        foreach ($data as $k => $v) {
            if (in_array($k, $this->fieldNames)) {
                if (is_array($v)) {
                    $i = 0;
                    $builder->groupStart();
                    foreach ($v as $value) {
                        if ($i == 0) {
                            $builder->where($k, $value);
                        } else {
                            $builder->orWhere($k, $value);
                        }
                        $i++;
                    }
                    $builder->groupEnd();
                } else {
                    if ($v != 0) {
                        $builder->where($k, $v);
                    }
                }
            }
        }
        
        $results = $builder->get()->getResult();
        
        $objects = [];
        foreach ($results as $result) {
            $objects[] = $this->get($result->{$this->primaryKey});
        }
        
        return $objects;
    }
}
