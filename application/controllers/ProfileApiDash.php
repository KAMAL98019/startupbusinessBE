<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ProfileApiDash extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('ProfileDashModel');
        $this->load->helper(['url', 'security']);
        header("Content-Type: application/json");
    }

    // POST /profileapidash/create_profile
    public function create_profile() {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!empty($data['user_id']) && !empty($data['name']) && !empty($data['email'])) {
            $insert_data = [
                'user_id'      => $data['user_id'],
                'name'         => $data['name'],
                'email'        => $data['email'],
                'phone_number' => $data['phone_number'] ?? null,
                'bio'          => $data['bio'] ?? null
            ];
            $this->ProfileDashModel->insert_profile($insert_data);
            echo json_encode(['status' => 'success', 'message' => 'Profile created']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Missing required fields (user_id, name, email)']);
        }
    }

    // GET /profileapidash/get_profile or /profileapidash/get_profile/{id}
    public function get_profile($id = null) {
        $result = $this->ProfileDashModel->get_profiles($id);
        echo json_encode($result);
    }

    // PUT /profileapidash/update_profile/{id}
    public function update_profile($id) {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'ID is required']);
            return;
        }

        if (!empty($data)) {
            $this->ProfileDashModel->update_profile($id, $data);
            echo json_encode(['status' => 'success', 'message' => 'Profile updated']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No data provided']);
        }
    }
}
