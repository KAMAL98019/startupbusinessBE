<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

class Users extends RestController {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // 📌 Get all users (excluding OTP and passwords)
    public function index_get() {
        $this->db->select("id, username, email, is_verified, created_at");
        $query = $this->db->get("users");
        $data = $query->result();
        $this->response($data, 200);
    }

    public function index_post() {
        $input = json_decode(file_get_contents("php://input"), true);
    
        if (!isset($input['username']) || trim($input['username']) === "") {
            $this->response(["success" => false, "message" => "Username is required"], 400);
            return;
        } elseif (!isset($input['email']) || trim($input['email']) === "") {
            $this->response(["success" => false, "message" => "Email is required"], 400);
            return;
        } elseif (!isset($input['password']) || trim($input['password']) === "") {
            $this->response(["success" => false, "message" => "Password is required"], 400);
            return;
        }
        
    
        // Check if email already exists
        $query = $this->db->get_where("users", ["email" => $input['email']]);
        if ($query->num_rows() > 0) {
            $this->response(["success"=>false,"message" => "Email already registered"], 400);
            return;
        }
    
        // Generate OTP
        $otp = rand(100000, 999999);
        $otp_expiry = date("Y-m-d H:i:s", strtotime("+5 minutes")); // OTP valid for 5 minutes
    
        // Hash the password
        $hashed_password = password_hash($input['password'], PASSWORD_BCRYPT);
    
        // Insert user data
        $data = [
            "username" => $input['username'],
            "email" => $input['email'],
            "password" => $hashed_password,
            "otp" => $otp,
            "otp_expiry" => $otp_expiry,
            "is_verified" => 0
        ];
        $this->db->insert("users", $data);
    
        // Load email library and configure
        $this->load->library('email');
    
        $config = [
            'protocol'    => 'smtp',
            'smtp_host'   => 'smtp.gmail.com',
            'smtp_port'   => 587,
            'smtp_user'   => 'ashekm2003@gmail.com',
            'smtp_pass'   => 'mwdo xzrv lovj kppr', // Replace with app password
            'smtp_crypto' => 'tls',                    // Required for Gmail over port 587
            'mailtype'    => 'html',
            'charset'     => 'utf-8',
            'newline'     => "\r\n"
        ];        
        $this->email->initialize($config);
    
        // Compose the email
        $this->email->from('ashekm2003@gmail.com', 'Event App');
        $this->email->to($input['email']);
        $this->email->subject('Your OTP Code');
        $this->email->message("<p>Hello <strong>{$input['username']}</strong>,</p>
            <p>Your OTP code is: <strong>$otp</strong></p>
            <p>This code is valid for 5 minutes.</p>");
    
        if ($this->email->send()) {
            $this->response(["success"=>true,"message" => "User registered successfully. OTP sent to email."], 201);
        } else {
            $this->response(["success"=>false,"message" => "Failed to send OTP email."], 500);
        }
    }
    

    // 📌 Verify OTP
    public function verify_otp_post() {
        $input = json_decode(file_get_contents("php://input"), true);
    
        if (!isset($input['email']) || !isset($input['otp'])) {
            $this->response(["success"=>false,"message" => "Email and OTP are required"], 400);
            return;
        }
    
        // Check if user exists
        $query = $this->db->get_where("users", ["email" => $input['email']]);
        if ($query->num_rows() == 0) {
            $this->response(["success"=>false,"message" => "Email not registered"], 400);
            return;
        }
    
        $user = $query->row();
    
        // Check if OTP is valid (Convert both to string)
        if (strval($user->otp) !== strval($input['otp'])) {
            $this->response(["success"=>false,"message" => "Invalid OTP"], 400);
            return;
        }
    
        // Check if OTP is expired
        if (strtotime($user->otp_expiry) < time()) {
            $this->response(["success"=>false,"message" => "OTP expired"], 400);
            return;
        }
    
        // Mark user as verified
        $this->db->where("email", $input['email']);
        $this->db->update("users", ["otp" => NULL, "otp_expiry" => NULL, "is_verified" => 1]);
    
        $this->response(["success"=>true,"message" => "OTP verified successfully. Account activated."], 200);
    }

    public function login_post() {
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
    

    // 📌 Login using username/email and password (only if verified)
// public function login_post() {
//     $input = json_decode(file_get_contents("php://input"), true);

//     if (!isset($input['identifier']) || !isset($input['password'])) {
//         $this->response(["success"=>false,"message" => "Username/Email and Password are required"], 400);
//         return;
//     }

//     $identifier = $input['identifier'];
//     $password = $input['password'];

//     // Search by username or email
//     $this->db->where('username', $identifier);
//     $this->db->or_where('email', $identifier);
//     $query = $this->db->get('users');

//     if ($query->num_rows() === 0) {
//         $this->response(["success"=>false,"message" => "User not found"], 404);
//         return;
//     }

//     $user = $query->row();

//     // Verify password
//     if (!password_verify($password, $user->password)) {
//         $this->response(["success"=>false,"message" => "Incorrect password"], 401);
//         return;
//     }

//     // Check if verified
//     if ((int)$user->is_verified !== 1) {
//         $this->response(["success"=>false,"message" => "Account not verified. Please verify OTP first."], 403);
//         return;
//     }

//     // Successful login
//     $this->response(["success"=>true,
//         "message" => "Login successful",
//         "user" => [
//             "id" => $user->id,
//             "username" => $user->username,
//             "email" => $user->email
//         ]
//     ], 200);
// }

    

    public function resend_otp_post() {
        $input = json_decode(file_get_contents("php://input"), true);
    
        if (!isset($input['email'])) {
            $this->response(["success"=>false,"message" => "Email is required"], 400);
            return;
        }
    
        // Check if user exists
        $query = $this->db->get_where("users", ["email" => $input['email']]);
        if ($query->num_rows() == 0) {
            $this->response(["success"=>false,"message" => "Email not registered"], 400);
            return;
        }
    
        $user = $query->row();
    
        // Generate new OTP
        $otp = rand(100000, 999999);
        $otp_expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));
    
        // Update OTP in database
        $this->db->where("email", $input['email']);
        $this->db->update("users", ["otp" => $otp, "otp_expiry" => $otp_expiry]);
    
        // Load and configure email
        $this->load->library('email');
    
        $config = [
            'protocol'    => 'smtp',
            'smtp_host'   => 'smtp.gmail.com',
            'smtp_port'   => 587,
            'smtp_user'   => 'ashekm2003@gmail.com',
            'smtp_pass'   => 'mwdo xzrv lovj kppr', // Replace with app password
            'smtp_crypto' => 'tls',                    // Required for Gmail over port 587
            'mailtype'    => 'html',
            'charset'     => 'utf-8',
            'newline'     => "\r\n"
        ];  
        $this->email->initialize($config);
    
        // Compose and send the email
        $this->email->from('ashekm2003@gmail.com', 'Event App');
        $this->email->to($input['email']);
        $this->email->subject('Your New OTP Code');
        $this->email->message("<p>Hello <strong>{$user->username}</strong>,</p>
            <p>Your new OTP code is: <strong>$otp</strong></p>
            <p>This code will expire in 5 minutes.</p>");
    
        if ($this->email->send()) {
            $this->response(["success"=>true,"message" => "New OTP sent to your email successfully."], 200);
        } else {
            $this->response(["success"=>false,"message" => "Failed to send OTP email."], 500);
        }
    }
    
}
?>
