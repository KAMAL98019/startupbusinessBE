<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

class Mentor extends RestController {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // 📌 GET: Fetch all mentor registrations
    public function index_get() {
        $query = $this->db->get("mentor_register");
        $mentors = $query->result();

        // Decode JSON fields for each mentor
        foreach ($mentors as &$mentor) {
            $mentor->preferred_communication = json_decode($mentor->preferred_communication, true);
            $mentor->availability = json_decode($mentor->availability, true);
            $mentor->additional_links = json_decode($mentor->additional_links, true);
        }

        $this->response($mentors, 200);
    }

    // 📌 POST: Register a new mentor
    public function index_post() {
        $input = json_decode(file_get_contents("php://input"), true);

        // Required fields validation
        $required_fields = ['full_name', 'email', 'phone_number', 'short_bio', 'agree_terms'];
        foreach ($required_fields as $field) {
            if (!isset($input[$field])) {
                return $this->response(["error" => "$field is required"], 400);
            }
        }

        // Prepare data with proper encoding for array fields
        $data = [
            "profile_picture" => isset($input['profile_picture']) ? $input['profile_picture'] : NULL,
            "full_name" => $input['full_name'],
            "email" => $input['email'],
            "phone_number" => $input['phone_number'],
            "short_bio" => $input['short_bio'],
            "industry" => isset($input['industry']) ? $input['industry'] : NULL,
            "years_of_experience" => isset($input['years_of_experience']) ? intval($input['years_of_experience']) : 0,
            "mentor_fee" => isset($input['mentor_fee']) ? $input['mentor_fee'] : NULL,

            // JSON encode arrays
            "preferred_communication" => isset($input['preferred_communication']) && is_array($input['preferred_communication']) 
                ? json_encode($input['preferred_communication']) 
                : json_encode([$input['preferred_communication']]),

            "availability" => isset($input['availability']) && is_array($input['availability']) 
                ? json_encode($input['availability']) 
                : json_encode([$input['availability']]),

            "previous_mentorships" => isset($input['previous_mentorships']) ? $input['previous_mentorships'] : NULL,

            "additional_links" => isset($input['additional_links']) && is_array($input['additional_links']) 
                ? json_encode($input['additional_links']) 
                : json_encode([$input['additional_links']]),

            "linkedin_url" => isset($input['linkedin_url']) ? $input['linkedin_url'] : NULL,
            "twitter_url" => isset($input['twitter_url']) ? $input['twitter_url'] : NULL,
            "agree_terms" => (bool) $input['agree_terms']
        ];

        // Insert data into the DB
        if ($this->db->insert("mentor_register", $data)) {
            $this->response(["message" => "Mentor registered successfully."], 201);
        } else {
            $error = $this->db->error();
            $this->response([
                "error" => "Failed to register mentor.",
                "db_error" => $error
            ], 500);
        }
    }

    // 📌 GET: Get mentor by ID
    public function get_mentor_by_id_get($id = null) {
        if ($id === null || !is_numeric($id)) {
            return $this->response([
                'status' => false,
                'message' => 'Valid mentor ID is required'
            ], 400);
        }

        $this->db->where("id", $id);
        $query = $this->db->get("mentor_register");
        $mentor = $query->row();

        if ($mentor) {
            // Decode JSON fields
            $mentor->preferred_communication = json_decode($mentor->preferred_communication, true);
            $mentor->availability = json_decode($mentor->availability, true);
            $mentor->additional_links = json_decode($mentor->additional_links, true);

            return $this->response([
                'status' => true,
                'data' => $mentor
            ], 200);
        } else {
            return $this->response([
                'status' => false,
                'message' => 'Mentor not found'
            ], 404);
        }
    }
}
?>
