<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

class Incubation extends RestController {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // 📌 Get all incubation records
    public function index_get() {
        $query = $this->db->get("incubation_register");
        $this->response($query->result(), 200);
    }

    // 📌 Register a new incubation center
    public function index_post() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['center_name']) || !isset($input['agree_terms'])) {
            $this->response(["error" => "Required fields are missing."], 400);
            return;
        }

        $data = [
            "center_name" => $input['center_name'],
            "address" => isset($input['address']) ? $input['address'] : NULL,
            "point_of_contact" => isset($input['point_of_contact']) ? $input['point_of_contact'] : NULL,
            "role_in_center" => isset($input['role_in_center']) ? $input['role_in_center'] : NULL,
            "social_media_links" => isset($input['social_media_links']) ? json_encode($input['social_media_links']) : NULL,
            "incubation_benefits" => isset($input['incubation_benefits']) ? json_encode($input['incubation_benefits']) : NULL,
            "application_process" => isset($input['application_process']) ? $input['application_process'] : NULL,
            "eligibility_criteria" => isset($input['eligibility_criteria']) ? json_encode($input['eligibility_criteria']) : NULL,
            "number_of_startups" => isset($input['number_of_startups']) ? (int)$input['number_of_startups'] : NULL,
            "annual_program_slot" => isset($input['annual_program_slot']) ? $input['annual_program_slot'] : NULL,
            "agree_terms" => (bool)$input['agree_terms']
        ];

        $this->db->insert("incubation_register", $data);
        $this->response(["message" => "Incubation center registered successfully."], 201);
    }
}
