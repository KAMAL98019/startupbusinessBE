<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH .'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;
class UserTypes extends RestController {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Get all user types
    public function index_get() {
        $query = $this->db->get("user_types");
        $data = $query->result();
        $this->response($data, 200);
    }

    // Add a new user type
    public function index_post() {
        $input = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($input['user_type']) || empty($input['user_type'])) {
            $this->response(["error" => "User type is required"], 400);
        } else {
            $this->db->insert("user_types", ["user_type" => $input['user_type']]);
            $this->response(["message" => "User type added successfully"], 201);
        }
    }

    // Update user type
    public function index_put($id) {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['user_type']) || empty($input['user_type'])) {
            $this->response(["error" => "User type is required"], 400);
        } else {
            $this->db->where("id", $id);
            $this->db->update("user_types", ["user_type" => $input['user_type']]);
            $this->response(["message" => "User type updated successfully"], 200);
        }
    }

    // Delete user type
    public function index_delete($id) {
        $this->db->where("id", $id);
        $this->db->delete("user_types");
        $this->response(["message" => "User type deleted successfully"], 200);
    }
    
}
 
?>