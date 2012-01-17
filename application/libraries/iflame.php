<?php
interface IFlame {
    public function get($id);
    public function get_list($num=null, $offset=null);
    public function update($id, $data);
    public function add($data);
    public function delete($id);

}
