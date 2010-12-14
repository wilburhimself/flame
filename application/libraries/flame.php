<?php
require_once APPPATH.'/libraries/iFlame.php';
abstract class Flame extends Model implements IFlame {
    protected $tablename;
    protected $object_name;
    protected $pk; // primary key
    private $fields;
    public $belongs_to = array();
    public $has_many = array();
    public $has_and_belongs_to_many = array();

    public function __construct() {
        $ci =& get_instance();
        $this->ci = $ci;
        $this->db = $this->ci->db;
        $this->fields = $this->db->list_fields($this->tablename);
        $this->unicode = singular($this->tablename);
    }

    public function __call($method, $args) {
        if (preg_match( "/find_by_(.*)/", $method, $found)) {
            if (in_array($found[1], $this->fields)) {
                return $this->get_by($found[1], $args[0]);
            }
        }
    }

    public function get_list($args=null, $num=null, $offset=null) {
        if (isset($args) and is_array($args)) {
            foreach ($args as $k => $v) {
                $this->db->where($k, $v);
            }
        }
        $query = $this->db->get($this->tablename, $num, $offset);
        switch($query->num_rows()) {
            case(0):
                return false;
            default:
                $p = $query->result();
                $o = array();
                foreach ($p as $e) {
                    $o[] = $this->get($e->{singular($this->tablename).'_id'});
                }
                return $o;
        }
    }

    public function get_by($method, $args) {
        $query = $this->db->where($method, $args)->get($this->tablename);
        switch($query->num_rows()) {
            case(0):
                return false;
            default:
                $p = $query->result();
                $o = array();
                foreach ($p as $e) {
                    $o[] = $this->get($e->{singular($this->tablename).'_id'});
                }
                return $o;
        }
    }

    public function get($id) {
        $this->db->where($this->pk, $id);
        if (!empty($this->belongs_to)) {

            foreach ($this->belongs_to as $entity) {
                $table = plural($entity);
                $entity_n = ucwords($entity);
                $this->db->join($this->ci->$entity_n->tablename, $this->ci->$entity_n->tablename.'.'.singular($this->ci->$entity_n->tablename).'_id='.$this->tablename.'.'.singular($this->ci->$entity_n->tablename));
            }
            
        }
        $obj = (object) $this->db->get($this->tablename)->row_array();

        if (!empty($this->has_many)) {
            foreach ($this->has_many as $entity) {
                $entity_n = ucwords($entity);
                $c = $this->ci->{$entity_n}->{'find_by_'.$this->unicode}($id);
                $obj->{$this->ci->{$entity_n}->tablename} = $c;
            }
        }

        if (!empty($this->has_and_belongs_to_many)) {
            foreach ($this->has_and_belongs_to_many as $entity) {
                $obj->{$entity} = array();
                $entity_n = singular($entity);
                $obj->{$entity}[] = $this->db->where($this->tablename.'_'.$entity.'.'.$this->unicode.'_id', $id)->join($entity, $entity.'.'.$entity_n.'_id='.$this->tablename.'_'.$entity.'.'.singular($entity).'_id')->get($this->tablename.'_'.$entity)->result();
                //print $this->db->last_query();
            }
        }

        return $obj;
    }

    public function add($object) {
        return $this->db->insert($this->tablename, $object);
    }

    public function update($id, $object) {
        return $this->db->where($this->pk, $id)->update($this->tablename, $object);
    }

    public function delete($id) {
        return $this->db->where($this->pk, $id)->delete($this->tablename);
    }

    public function generate_from_post() {
        $out = new stdClass();
        if ($_POST) {
            foreach ($this->fields as $f) {
                $v = $this->ci->input->post($f);
                $out->$f = !empty($v) ? $v : '';
            }
        } else {
            foreach ($this->fields as $f) {
                $out->$f = '';
            }
        }
        return $out;
    }

    public function belongs_to($entity) {
        $this->belongs_to[] = $entity;
    }

     public function has_and_belongs_to_many($entity) {
        $this->has_and_belongs_to_many[] = $entity;
    }

    public function has_many($entity) {
        $this->has_many[] = $entity;
    }

    public function search($request, $exclude=null) {
        $data = array();
        if ($exclude) {
            $this->db->where_not_in($this->pk, $exclude);
        }
        foreach ($request as $k => $v) {
            if (in_array($k, $this->fields)) {
                $data[$k] = $v;
            }
        }
        foreach ($data as $k => $v) {
            if (in_array($k, $this->fields)) {
                if (is_array($v)) {
                    $i = 0;
                    while($i < sizeof($v)) {
                        if ($i == 0) {
                            $this->db->where($k, $v[$i]);
                        } else {
                            $this->db->or_where($k, $v[$i]);
                        }
                        $i++;
                    }
                } else {
                    if (!$v == 0) {
                        $this->db->where($k, $v);
                    }
                }
            }
        }
        $vs = $this->db->get($this->tablename)->result();
        $o = array();
        foreach ($vs as $obj) {
            $o[] = $this->get($obj->{$this->pk});
        }
        return $o;
    }

}