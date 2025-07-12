<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

class Student extends RestController {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // 📌 Get all student registrations
    public function index_get() {
        $query = $this->db->get("student_register");
        $data = $query->result();
        $this->response($data, 200);
    }

    // 📌 Register a new student
    public function index_post() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['full_name']) || !isset($input['email']) || !isset($input['phone_number']) || 
            !isset($input['skills']) || !isset($input['professional_profiles']) || 
            !isset($input['work_mode']) || !isset($input['compensation']) || 
            !isset($input['availability']) || !isset($input['agree_terms'])) {
            $this->response(["error" => "All required fields must be provided"], 400);
            return;
        }

        // Insert student data
        $data = [
            "full_name" => $input['full_name'],
            "email" => $input['email'],
            "phone_number" => $input['phone_number'],
            "skills" => json_encode($input['skills']), // JSON Encoding for multiple skills
            "professional_profiles" => json_encode($input['professional_profiles']), // JSON Encoding for multiple profiles
            "work_mode" => $input['work_mode'],
            "compensation" => $input['compensation'],
            "availability" => $input['availability'],
            "resume" => isset($input['resume']) ? $input['resume'] : NULL,
            "agree_terms" => (bool) $input['agree_terms']
        ];

        $this->db->insert("student_register", $data);
        $this->response(["success" => true, "message" => "Student registered successfully."], 201);
    }

    public function get_student_by_id_get($id = null)
{
    // Validate ID
    if ($id === null || !is_numeric($id)) {
        return $this->response([
            'status' => false,
            'message' => 'Valid student ID is required'
        ], 400);
    }

    // Fetch student by ID
    $this->db->select("*");
    $this->db->from("student_register");
    $this->db->where("id", $id);
    $query = $this->db->get();
    $student = $query->row();

    if ($student) {
        // Decode JSON fields
        $student->skills = json_decode($student->skills, true);
        $student->professional_profiles = json_decode($student->professional_profiles, true);

        return $this->response([
            'status' => true,
            'data' => $student
        ], 200);
    } else {
        return $this->response([
            'status' => false,
            'message' => 'Student not found'
        ], 404);
    }
}

}
?>
