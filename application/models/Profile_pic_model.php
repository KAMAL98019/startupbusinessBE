<?php
class Profile_pic_model extends CI_Model {

    public function insert_profile_pic($data) {
        return $this->db->insert('profile_pic', $data);
    }

    public function update_profile_pic($id, $data) {
        $this->db->where('user_id', $id);
        return $this->db->update('profile_pic', $data);
    }

    public function delete_profile_pic($id) {
        $this->db->where('user_id', $id);
        return $this->db->delete('profile_pic');
    }
    public function get_all_profiles() {
        return $this->db->get('profile_pic')->result();
    }
    public function get_by_user_id($user_id) {
        return $this->db->get_where('profile_pic', ['user_id' => $user_id])->row();
    }
}
