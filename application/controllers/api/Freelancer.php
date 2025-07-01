<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

class Freelancer extends RestController {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // GET: Fetch all freelancer registrations
    public function index_get() {
        $query = $this->db->get("freelancer_register");
        $data = $query->result();
        $this->response($data, 200);
    }

    // POST: Register a new freelancer
    public function index_post() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['full_name']) || !isset($input['email']) || !isset($input['phone_number']) || 
            !isset($input['short_bio']) || !isset($input['service_categories']) || !isset($input['skills']) || 
            !isset($input['rate_amount']) || !isset($input['availability']) || !isset($input['agree_terms'])) {
            $this->response(["error" => "Required fields are missing"], 400);
            return;
        }

        $data = [
            "profile_picture" => isset($input['profile_picture']) ? $input['profile_picture'] : NULL,
            "full_name" => $input['full_name'],
            "email" => $input['email'],
            "phone_number" => $input['phone_number'],
            "short_bio" => $input['short_bio'],
            "service_categories" => json_encode($input['service_categories']),
            "skills" => json_encode($input['skills']),
            "rate_type" => isset($input['rate_type']) ? $input['rate_type'] : 'Hourly',
            "currency" => isset($input['currency']) ? $input['currency'] : 'INR',
            "rate_amount" => $input['rate_amount'],
            "portfolio_samples" => isset($input['portfolio_samples']) ? $input['portfolio_samples'] : NULL,
            "availability" => $input['availability'],
            "payment_method" => isset($input['payment_method']) ? $input['payment_method'] : NULL,
            "account_number" => isset($input['account_number']) ? $input['account_number'] : NULL,
            "ifsc_code" => isset($input['ifsc_code']) ? $input['ifsc_code'] : NULL,
            "swift_code" => isset($input['swift_code']) ? $input['swift_code'] : NULL,
            "agree_terms" => (bool) $input['agree_terms']
        ];

        $this->db->insert("freelancer_register", $data);
        $this->response(["message" => "Freelancer registered successfully."], 201);
    }
    public function get_freelancer_by_id_get($id = null)
{
    // Validate the ID
    if ($id === null || !is_numeric($id)) {
        return $this->response([
            'status' => false,
            'message' => 'Valid freelancer ID is required'
        ], 400);
    }

    // Fetch freelancer data by ID
    $this->db->select("*");
    $this->db->from("freelancer_register");
    $this->db->where("id", $id);
    $query = $this->db->get();
    $freelancer = $query->row();

    if ($freelancer) {
        // Decode JSON fields
        $freelancer->service_categories = json_decode($freelancer->service_categories, true);
        $freelancer->skills = json_decode($freelancer->skills, true);

        return $this->response([
            'status' => true,
            'data' => $freelancer
        ], 200);
    } else {
        return $this->response([
            'status' => false,
            'message' => 'Freelancer not found'
        ], 404);
    }
}

}
?>
