<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Profile_model');
        $this->load->helper('url');
        header('Content-Type: application/json');
    }

    // GET API
    public function get_profiles($id = null) {
        $data = $this->Profile_model->get_profiles($id);
        echo json_encode($data);
    }

    // POST API
    public function create_profile() {
        $input = json_decode(trim(file_get_contents('php://input')), true);
        if ($this->Profile_model->create_profile($input)) {
            echo json_encode(['status' => 'success', 'message' => 'Profile created']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create profile']);
        }
    }
}
