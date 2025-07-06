<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

class Startup extends RestController {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // 📌 Get all startup registrations
    public function index_get() {
        $query = $this->db->get("startup_register");
        $data = $query->result();
        $this->response($data, 200);
    }

    // 📌 Register a new startup
    public function index_post() {
        $input = json_decode(file_get_contents("php://input"), true);

        // 🔒 Basic validation
        $required_fields = [
            'full_name', 'email', 'phone_number', 'city_country', 'startup_name',
            'industry_sector', 'current_stage', 'business_model_description',
            'current_funding_status', 'investment_amount_required', 'team_members',
            'website_links', 'linkedin_links', 'social_links',
            'interested_in_incubation', 'incubation_centers', 'agree_terms'
        ];

        foreach ($required_fields as $field) {
            if (!isset($input[$field])) {
                return $this->response(["error" => "$field is required"], 400);
            }
        }

        // ✅ Prepare data safely
        $data = [
            "full_name" => $input['full_name'],
            "email" => $input['email'],
            "phone_number" => $input['phone_number'],
            "city_country" => $input['city_country'],
            "startup_name" => $input['startup_name'],
            "startup_logo" => isset($input['startup_logo']) ? $input['startup_logo'] : NULL,
            "company_registration_number" => isset($input['company_registration_number']) ? $input['company_registration_number'] : NULL,
            "industry_sector" => $input['industry_sector'],
            "current_stage" => $input['current_stage'],
            "business_model_description" => $input['business_model_description'],
            "current_funding_status" => $input['current_funding_status'],
            "investment_amount_required" => $input['investment_amount_required'],
            "team_members" => json_encode($input['team_members']),
            "website_links" => json_encode($input['website_links']),
            "linkedin_links" => json_encode($input['linkedin_links']),
            "social_links" => json_encode($input['social_links']),
            "pitch_video" => isset($input['pitch_video']) ? $input['pitch_video'] : NULL,
            "business_plan" => isset($input['business_plan']) ? $input['business_plan'] : NULL,
            "interested_in_incubation" => $input['interested_in_incubation'],
            "incubation_centers" => json_encode($input['incubation_centers']),
            "agree_terms" => (bool) $input['agree_terms']
        ];

        // 🐞 Debug log
        log_message('debug', 'Startup Insert Data: ' . print_r($data, true));

        // ✅ Insert and check status
        if ($this->db->insert("startup_register", $data)) {
            $this->response(["message" => "Startup registered successfully."], 201);
        } else {
            $error = $this->db->error();
            $this->response([
                "error" => "Failed to insert startup data",
                "db_error" => $error
            ], 500);
        }
    }

    // 📌 Get single startup by ID
    public function get_startup_by_id_get($id = null) {
        if ($id === null || !is_numeric($id)) {
            return $this->response([
                'status' => false,
                'message' => 'Valid startup ID is required'
            ], 400);
        }

        $this->db->select("*");
        $this->db->from("startup_register");
        $this->db->where("id", $id);
        $query = $this->db->get();
        $startup = $query->row();

        if ($startup) {
            $startup->team_members = json_decode($startup->team_members, true);
            $startup->website_links = json_decode($startup->website_links, true);
            $startup->linkedin_links = json_decode($startup->linkedin_links, true);
            $startup->social_links = json_decode($startup->social_links, true);
            $startup->incubation_centers = json_decode($startup->incubation_centers, true);

            return $this->response([
                'status' => true,
                'data' => $startup
            ], 200);
        } else {
            return $this->response([
                'status' => false,
                'message' => 'Startup not found'
            ], 404);
        }
    }
}
?>
