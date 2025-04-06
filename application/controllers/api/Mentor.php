<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

class Mentor extends RestController {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // GET: Fetch all mentor registrations
    public function index_get() {
        $query = $this->db->get("mentor_register");
        $data = $query->result();
        $this->response($data, 200);
    }

    // POST: Register a new mentor
    public function index_post() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['full_name']) || !isset($input['email']) || !isset($input['phone_number']) || 
            !isset($input['short_bio']) || !isset($input['agree_terms'])) {
            $this->response(["error" => "Required fields missing"], 400);
            return;
        }

        $data = [
            "profile_picture" => isset($input['profile_picture']) ? $input['profile_picture'] : NULL,
            "full_name" => $input['full_name'],
            "email" => $input['email'],
            "phone_number" => $input['phone_number'],
            "short_bio" => $input['short_bio'],
            "industry" => isset($input['industry']) ? $input['industry'] : NULL,
            "years_of_experience" => isset($input['years_of_experience']) ? intval($input['years_of_experience']) : 0,
            "mentor_fee" => isset($input['mentor_fee']) ? $input['mentor_fee'] : NULL,
            "preferred_communication" => isset($input['preferred_communication']) ? $input['preferred_communication'] : NULL,
            "availability" => isset($input['availability']) ? $input['availability'] : 'Flexible',
            "previous_mentorships" => isset($input['previous_mentorships']) ? $input['previous_mentorships'] : NULL,
            "additional_links" => isset($input['additional_links']) ? json_encode($input['additional_links']) : NULL,
            "linkedin_url" => isset($input['linkedin_url']) ? $input['linkedin_url'] : NULL,
            "twitter_url" => isset($input['twitter_url']) ? $input['twitter_url'] : NULL,
            "agree_terms" => (bool) $input['agree_terms']
        ];

        $this->db->insert("mentor_register", $data);
        $this->response(["message" => "Mentor registered successfully."], 201);
    }
}
?>
