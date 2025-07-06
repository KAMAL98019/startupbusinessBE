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
        $data = $query->result();
        $this->response($data, 200);
    }

    // 📌 POST: Register a new mentor
    public function index_post() {
        $input = json_decode(file_get_contents("php://input"), true);
    
        // ✅ Required field check
        $required_fields = ['full_name', 'email', 'phone_number', 'short_bio', 'agree_terms'];
        foreach ($required_fields as $field) {
            if (!isset($input[$field])) {
                return $this->response(["error" => "$field is required"], 400);
            }
        }
    
        // ✅ Safe JSON encoding
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
            "additional_links" => isset($input['additional_links']) && is_array($input['additional_links']) 
                ? json_encode($input['additional_links']) 
                : (is_string($input['additional_links'] ?? null) ? $input['additional_links'] : NULL),
            "linkedin_url" => isset($input['linkedin_url']) ? $input['linkedin_url'] : NULL,
            "twitter_url" => isset($input['twitter_url']) ? $input['twitter_url'] : NULL,
            "agree_terms" => (bool) $input['agree_terms']
        ];
    
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

        $this->db->select("*");
        $this->db->from("mentor_register");
        $this->db->where("id", $id);
        $query = $this->db->get();
        $mentor = $query->row();

        if ($mentor) {
            // Safe JSON decode
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
