<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

class Investor extends RestController {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // 📌 Get all investor registrations
    public function index_get() {
        $query = $this->db->get("investor_register");
        $data = $query->result();
        $this->response($data, 200);
    }

    // 📌 Register a new investor
    public function index_post() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['full_name']) || !isset($input['email']) || !isset($input['phone_number']) || 
            !isset($input['company_name']) || !isset($input['investment_portfolio_link']) ||
            !isset($input['investment_min']) || !isset($input['investment_max']) || 
            !isset($input['currency']) || !isset($input['preferred_industry']) || 
            !isset($input['investment_stage_interest']) || !isset($input['linkedin_url']) ||
            !isset($input['social_links']) || !isset($input['agree_terms'])) {
            $this->response(["error" => "All required fields must be provided"], 400);
            return;
        }

        // Insert investor data
        $data = [
            "full_name" => $input['full_name'],
            "email" => $input['email'],
            "phone_number" => $input['phone_number'],
            "company_name" => $input['company_name'],
            "company_logo" => isset($input['company_logo']) ? $input['company_logo'] : NULL,
            "investment_portfolio_link" => $input['investment_portfolio_link'],
            "portfolio_document" => isset($input['portfolio_document']) ? $input['portfolio_document'] : NULL,
            "investment_min" => $input['investment_min'],
            "investment_max" => $input['investment_max'],
            "currency" => $input['currency'],
            "preferred_industry" => json_encode($input['preferred_industry']), // JSON Encoding for multiple selection
            "investment_stage_interest" => $input['investment_stage_interest'],
            "linkedin_url" => $input['linkedin_url'],
            "social_links" => json_encode($input['social_links']),
            "legal_document" => isset($input['legal_document']) ? $input['legal_document'] : NULL,
            "agree_terms" => (bool) $input['agree_terms']
        ];

        $this->db->insert("investor_register", $data);
        $this->response([ "success" => true, "message" => "Investor registered successfully."], 201);
    }

    public function get_investor_by_id_get($id = null)
{
    // Validate the ID
    if ($id === null || !is_numeric($id)) {
        return $this->response([
            'status' => false,
            'message' => 'Valid investor ID is required'
        ], 400);
    }

    // Fetch investor record by ID
    $this->db->select("*");
    $this->db->from("investor_register");
    $this->db->where("id", $id);
    $query = $this->db->get();
    $investor = $query->row();

    if ($investor) {
        // Decode JSON fields before responding
        $investor->preferred_industry = json_decode($investor->preferred_industry, true);
        $investor->social_links = json_decode($investor->social_links, true);

        return $this->response([
            'status' => true,
            'data' => $investor
        ], 200);
    } else {
        return $this->response([
            'status' => false,
            'message' => 'Investor not found'
        ], 404);
    }
}

}
?>
