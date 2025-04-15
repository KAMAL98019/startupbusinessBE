<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

class Login extends RestController {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

   

    public function login_post()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        // Validate inputs
        if (!isset($input['email']) || trim($input['email']) === "") {
            $this->response(["success" => false, "message" => "Email is required"], 400);
            return;
        } elseif (!isset($input['password']) || trim($input['password']) === "") {
            $this->response(["success" => false, "message" => "Password is required"], 400);
            return;
        }

        // Fetch user by email
        $query = $this->db->get_where("users", ["email" => $input['email']]);
        if ($query->num_rows() === 0) {
            $this->response(["success" => false, "message" => "Invalid email or password"], 401);
            return;
        }

        $user = $query->row_array();

        // Check if user is verified
        if ((int)$user['is_verified'] !== 1) {
            $this->response(["success" => false, "message" => "Account not verified. Please verify your OTP."], 403);
            return;
        }

        // Verify password
        if (!password_verify($input['password'], $user['password'])) {
            $this->response(["success" => false, "message" => "Invalid email or password"], 401);
            return;
        }

        // Login success
        $this->response([
            "success" => true,
            "message" => "Login successful",
            "user" => [
                "id" => $user['id'],
                "username" => $user['username'],
                "email" => $user['email']
            ]
        ], 200);
    }

    
    

  
    
}
?>
