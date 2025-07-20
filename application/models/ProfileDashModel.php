<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ProfileDashModel extends CI_Model {

    private $table = 'profile_dash';

    public function insert_profile($data) {
        return $this->db->insert($this->table, $data);
    }

    public function get_profiles($id = null) {
        if ($id) {
            $query = $this->db->get_where($this->table, ['user_id' => $id]);
            return $query->row_array();
        } else {
            $query = $this->db->get($this->table);
            return $query->result_array();
        }
    }

    public function update_profile($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }
}
