<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ProfileApi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Profile_model');
        $this->load->helper('url');
        $this->load->helper('security');
        header("Content-Type: application/json");
    }

    // POST /profile
    public function create_profile() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!empty($data['user_id'])) {
            $insert_data = [
                'user_id' => $data['user_id'],
                'phone_number' => $data['phone_number'],
                'role' => $data['role'],
                'skills' => json_encode($data['skills']), // store as JSON
                'bio' => $data['bio']
            ];
            $this->Profile_model->insert_profile($insert_data);
            echo json_encode(['status' => 'success', 'message' => 'Profile created']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Missing user_id']);
        }
    }

    // GET /profile or /profile/{id}
    public function get_profile($id = null) {
        $result = $this->Profile_model->get_profiles($id);
        echo json_encode($result);
    }

    // PUT /profile/{id}
    public function update_profile($id) {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID is required']);
            return;
        }

        if (!empty($data)) {
            if (isset($data['skills'])) {
                $data['skills'] = json_encode($data['skills']);
            }
            $this->Profile_model->update_profile($id, $data);
            echo json_encode(['status' => 'success', 'message' => 'Profile updated']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No data provided']);
        }
    }
}
