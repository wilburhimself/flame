<?php

namespace WilburHimself\Flame\Tests\Fixtures;

use WilburHimself\Flame\Flame;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Database\BaseBuilder;

/**
 * Test Model for Flame tests
 */
class TestModel extends Flame
{
    protected $table = 'tests';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'email', 'status'];
    
    /**
     * Flag to skip parent constructor for testing
     */
    protected $skipParentConstruct = true;
    
    /**
     * Builder for testing
     */
    protected $builder;
    
    /**
     * Constructor
     */
    public function __construct(?ConnectionInterface $db = null, ?\CodeIgniter\Validation\Validation $validation = null)
    {
        // Set dependencies before parent constructor is called
        if ($db !== null) {
            $this->db = $db;
        }
        
        if ($validation !== null) {
            $this->validation = $validation;
        }
        
        // Skip parent constructor for testing
        if (!$this->skipParentConstruct) {
            parent::__construct($db, $validation);
        }
        
        // Simulate table fields info
        $this->fieldNames = ['id', 'name', 'email', 'status'];
        $this->unicode = 'test';
    }
    
    /**
     * Override parent method for testing
     */
    public function find($id = null)
    {
        // This will be mocked in the tests
        return null;
    }
    
    /**
     * Override parent method for testing
     * 
     * @param int|null $limit
     * @param int $offset
     * @return array
     */
    public function findAll(?int $limit = null, int $offset = 0): array
    {
        // This will be mocked in the tests
        return [];
    }
    
    /**
     * Override parent method for testing
     * 
     * @param string|null $table
     * @return BaseBuilder
     */
    public function builder(?string $table = null)
    {
        if ($this->builder) {
            return $this->builder;
        }
        
        $table = $table ?? $this->table;
        return $this->db->table($table);
    }
    
    /**
     * Override where method for testing
     */
    public function where($key, $value = null)
    {
        // This will be mocked in tests
        return $this;
    }
    
    /**
     * Override update method for testing
     */
    public function update($id = null, $data = null): bool
    {
        // This will be mocked in tests
        return true;
    }
    
    /**
     * Override insert method for testing
     */
    public function insert($data = null, bool $returnID = true)
    {
        // This will be mocked in tests
        return 1;
    }
    
    /**
     * Override delete method for testing
     */
    public function delete($id = null, bool $purge = false)
    {
        // This will be mocked in tests
        return true;
    }
    
    /**
     * Set the builder for testing
     */
    public function setBuilder($builder)
    {
        $this->builder = $builder;
        return $this;
    }
    
    /**
     * Expose protected properties for testing
     */
    public function getProperty($name)
    {
        return $this->$name;
    }
    
    /**
     * Set a property value for testing
     */
    public function setProperty($name, $value)
    {
        $this->$name = $value;
        return $this;
    }
}
