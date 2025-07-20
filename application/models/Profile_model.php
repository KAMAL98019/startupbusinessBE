<?php
class Profile_model extends CI_Model {

    private $table = 'profile_details';

    public function insert_profile($data) {
        return $this->db->insert($this->table, $data);
    }

    public function get_profiles($id = null) {
        if ($id) {
            return $this->db->get_where($this->table, ['user_id' => $id])->row_array();
        }
        return $this->db->get($this->table)->result_array();
    }

    public function update_profile($id, $data) {
        $this->db->where('user_id', $id);
        return $this->db->update($this->table, $data);
    }
}
