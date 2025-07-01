<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

class Legal extends RestController {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // 📌 Get all registered legal experts
    public function index_get() {
        $query = $this->db->get("legal_register");
        $this->response($query->result(), 200);
    }

    // 📌 Register new legal expert
    public function index_post() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['full_name']) || !isset($input['email']) || !isset($input['agree_terms'])) {
            $this->response(["error" => "Required fields missing."], 400);
            return;
        }

        $data = [
            "full_name" => $input['full_name'],
            "email" => $input['email'],
            "phone_number" => isset($input['phone_number']) ? $input['phone_number'] : NULL,
            "company_name" => isset($input['company_name']) ? $input['company_name'] : NULL,
            "logo" => isset($input['logo']) ? $input['logo'] : NULL,
            "legal_specialization" => isset($input['legal_specialization']) ? $input['legal_specialization'] : NULL,
            "select_fee" => isset($input['select_fee']) ? $input['select_fee'] : NULL,
            "currency" => isset($input['currency']) ? $input['currency'] : NULL,
            "case_studies" => isset($input['case_studies']) ? $input['case_studies'] : NULL,
            "url_links" => isset($input['url_links']) ? json_encode($input['url_links']) : NULL,
            "certificate_proofs" => isset($input['certificate_proofs']) ? $input['certificate_proofs'] : NULL,
            "availability_date" => isset($input['availability_date']) ? $input['availability_date'] : NULL,
            "availability_time" => isset($input['availability_time']) ? $input['availability_time'] : NULL,
            "connect_google_calendar" => isset($input['connect_google_calendar']) ? (bool)$input['connect_google_calendar'] : false,
            "connect_zoom" => isset($input['connect_zoom']) ? (bool)$input['connect_zoom'] : false,
            "agree_terms" => (bool)$input['agree_terms']
        ];

        $this->db->insert("legal_register", $data);
        $this->response(["message" => "Legal expert registered successfully."], 201);
    }

    public function get_legal_by_id_get($id = null)
{
    // Validate the ID
    if ($id === null || !is_numeric($id)) {
        return $this->response([
            'status' => false,
            'message' => 'Valid legal expert ID is required'
        ], 400);
    }

    // Fetch data by ID
    $this->db->select("*");
    $this->db->from("legal_register");
    $this->db->where("id", $id);
    $query = $this->db->get();
    $legal = $query->row();

    if ($legal) {
        // Decode JSON fields
        $legal->url_links = json_decode($legal->url_links, true);

        return $this->response([
            'status' => true,
            'data' => $legal
        ], 200);
    } else {
        return $this->response([
            'status' => false,
            'message' => 'Legal expert not found'
        ], 404);
    }
}

}
?>