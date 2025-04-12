<?php

namespace WilburHimself\Flame;

/**
 * FlameResult for CodeIgniter 4
 * 
 * Represents a result object from a Flame query
 */
class FlameResult
{
    /**
     * @var Flame The parent model
     */
    protected $model;

    /**
     * Constructor
     *
     * @param Flame $model The parent model
     * @param object $data The data object
     */
    public function __construct($model, $data)
    {
        $this->model = $model;
        
        if ($data) {
            foreach ($data as $key => $value) {
                $this->$key = $value;
            }
        }
    }
    
    /**
     * Magic method to handle dynamic method calls
     *
     * @param string $method The method name
     * @param array $args The method arguments
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (isset($this->$method) && is_callable($this->$method)) {
            $func = $this->$method;
            return $func(...$args);
        }
        
        if (method_exists($this->model, $method)) {
            return $this->model->$method(...$args);
        }
        
        return null;
    }
}
