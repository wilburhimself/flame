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
     * @var array Data properties
     */
    protected $data = [];

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
                $this->data[$key] = $value;
            }
        }
    }
    
    /**
     * Magic getter to access data properties
     *
     * @param string $name Property name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }
    
    /**
     * Magic setter for data properties
     *
     * @param string $name Property name
     * @param mixed $value Property value
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }
    
    /**
     * Check if a property exists
     *
     * @param string $name Property name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
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
        // Check if the method is a callable property in our data
        if (isset($this->data[$method]) && is_callable($this->data[$method])) {
            $func = $this->data[$method];
            return $func(...$args);
        }
        
        // If not found in our data, check if the model has this method
        if ($this->model !== null) {
            // For method calls like someModelMethod, we need to delegate to the model
            // regardless of whether the method is found with method_exists or not,
            // since it might be handled by the model's __call method
            return call_user_func_array([$this->model, $method], $args);
        }
        
        return null;
    }
}
