<?php

namespace WilburHimself\Flame;

/**
 * IFlame Interface for CodeIgniter 4
 * 
 * Defines the core methods required for the Flame Active Record implementation
 */
interface IFlame
{
    /**
     * Get a single record by primary key
     *
     * @param mixed $id The primary key value
     * @return object|null
     */
    public function get($id);
    
    /**
     * Get a list of records with optional pagination
     *
     * @param array $args Optional conditions
     * @param int $num Optional limit
     * @param int $offset Optional offset
     * @return array|null
     */
    public function get_list($args = null, $num = null, $offset = null);
    
    /**
     * Update a record
     *
     * @param mixed $id The primary key value
     * @param object|array $data The data to update
     * @return bool
     */
    public function update($id, $data);
    
    /**
     * Add a new record
     *
     * @param object|array $data The data to insert
     * @return int|bool The insert ID or false on failure
     */
    public function add($data);
    
    /**
     * Delete a record
     *
     * @param mixed $id The primary key value
     * @return bool
     */
    public function delete($id);
}
