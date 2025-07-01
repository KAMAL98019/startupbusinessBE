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

        if (!isset($input['full_name']) || !isset($input['email']) || !isset($input['phone_number']) || 
            !isset($input['city_country']) || !isset($input['startup_name']) || 
            !isset($input['industry_sector']) || !isset($input['current_stage']) ||
            !isset($input['business_model_description']) || !isset($input['current_funding_status']) || 
            !isset($input['investment_amount_required']) || !isset($input['team_members']) || 
            !isset($input['website_links']) || !isset($input['linkedin_links']) ||
            !isset($input['social_links']) || !isset($input['interested_in_incubation']) || 
            !isset($input['incubation_centers']) || !isset($input['agree_terms'])) {
            $this->response(["error" => "All required fields must be provided"], 400);
            return;
        }

        // Insert startup data
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
            "team_members" => json_encode($input['team_members']), // JSON Encoding
            "website_links" => json_encode($input['website_links']),
            "linkedin_links" => json_encode($input['linkedin_links']),
            "social_links" => json_encode($input['social_links']),
            "pitch_video" => isset($input['pitch_video']) ? $input['pitch_video'] : NULL,
            "business_plan" => isset($input['business_plan']) ? $input['business_plan'] : NULL,
            "interested_in_incubation" => $input['interested_in_incubation'],
            "incubation_centers" => json_encode($input['incubation_centers']),
            "agree_terms" => (bool) $input['agree_terms']
        ];

        $this->db->insert("startup_register", $data);
        $this->response(["message" => "Startup registered successfully."], 201);
    }

    public function get_startup_by_id_get($id = null)
{
    // Validate the ID
    if ($id === null || !is_numeric($id)) {
        return $this->response([
            'status' => false,
            'message' => 'Valid startup ID is required'
        ], 400);
    }

    // Fetch startup record by ID
    $this->db->select("*");
    $this->db->from("startup_register");
    $this->db->where("id", $id);
    $query = $this->db->get();
    $startup = $query->row();

    if ($startup) {
        // Decode JSON fields before sending response
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
