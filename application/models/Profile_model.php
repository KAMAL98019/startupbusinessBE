<?php
class Profile_model extends CI_Model {

    public function get_profiles($id = null) {
        if ($id) {
            $this->db->where('id', $id);
        }
        $query = $this->db->get('profile_details');
        $result = $query->result_array();

        // Decode skills JSON
        foreach ($result as &$row) {
            $row['skills'] = json_decode($row['skills']);
        }

        return $result;
    }

    public function create_profile($data) {
        // Encode skills array to JSON
        if (isset($data['skills']) && is_array($data['skills'])) {
            $data['skills'] = json_encode($data['skills']);
        }
        return $this->db->insert('profile_details', $data);
    }
}
