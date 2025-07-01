<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

class Hr extends RestController {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // GET all HR records
    public function index_get() {
        $query = $this->db->get("hr_register");
        $this->response($query->result(), 200);
    }

    // POST new HR registration
    public function index_post() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['full_name']) || !isset($input['email']) || !isset($input['phone_number']) || 
            !isset($input['agree_terms'])) {
            $this->response(["error" => "Required fields missing."], 400);
            return;
        }

        $data = [
            "full_name" => $input['full_name'],
            "email" => $input['email'],
            "phone_number" => $input['phone_number'],
            "company_name" => isset($input['company_name']) ? $input['company_name'] : NULL,
            "company_website" => isset($input['company_website']) ? $input['company_website'] : NULL,
            "recruiter_role" => isset($input['recruiter_role']) ? $input['recruiter_role'] : NULL,
            "job_posting_description" => isset($input['job_posting_description']) ? $input['job_posting_description'] : NULL,
            "currency" => isset($input['currency']) ? $input['currency'] : NULL,
            "salary_min" => isset($input['salary_min']) ? $input['salary_min'] : NULL,
            "skills" => isset($input['skills']) ? json_encode($input['skills']) : NULL,
            "ats_integration" => isset($input['ats_integration']) ? $input['ats_integration'] : NULL,
            "interview_date" => isset($input['interview_date']) ? $input['interview_date'] : NULL,
            "timeslot" => isset($input['timeslot']) ? $input['timeslot'] : NULL,
            "connect_google_calendar" => isset($input['connect_google_calendar']) ? (bool)$input['connect_google_calendar'] : false,
            "connect_zoom" => isset($input['connect_zoom']) ? (bool)$input['connect_zoom'] : false,
            "agree_terms" => (bool)$input['agree_terms']
        ];

        $this->db->insert("hr_register", $data);
        $this->response(["message" => "HR registered successfully."], 201);
    }
    public function get_hr_by_id_get($id = null)
{
    // Validate ID
    if ($id === null || !is_numeric($id)) {
        return $this->response([
            'status' => false,
            'message' => 'Valid HR ID is required'
        ], 400);
    }

    // Fetch HR data by ID
    $this->db->select("*");
    $this->db->from("hr_register");
    $this->db->where("id", $id);
    $query = $this->db->get();
    $hr = $query->row();

    if ($hr) {
        // Decode JSON fields
        $hr->skills = json_decode($hr->skills, true);

        return $this->response([
            'status' => true,
            'data' => $hr
        ], 200);
    } else {
        return $this->response([
            'status' => false,
            'message' => 'HR record not found'
        ], 404);
    }
}

}
?>
