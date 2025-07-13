<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/RestController.php';

use chriskacerguis\RestServer\RestController;

class Users extends RestController
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    // 📌 Get all users (excluding OTP and passwords)
    public function index_get()
    {
        $this->db->select("id, username, email, is_verified, created_at, role_id, role_name");
        $query = $this->db->get("users");
        $data = $query->result();
        $this->response($data, 200);
    }
    public function get_user_by_id_get($id = null)
{
    // Validate ID
    if ($id === null || !is_numeric($id)) {
        return $this->response(['status' => false, 'message' => 'Valid User ID is required'], 400);
    }

    // Query user by ID
    $this->db->select("id, username, email, is_verified, created_at, role_id, role_name");
    $this->db->from("users");
    $this->db->where("id", $id);
    $query = $this->db->get();
    $user = $query->row();

    // Check if user exists
    if ($user) {
        return $this->response(['status' => true, 'data' => $user], 200);
    } else {
        return $this->response(['status' => false, 'message' => 'User not found'], 404);
    }
}

    public function index_put()
{
    $input = json_decode(file_get_contents("php://input"), true);

    // Validate 'id' field
    if (!isset($input['id'])) {
        return $this->response(['status' => false, 'message' => 'User ID is required'], 400);
    }

    $id = $input['id'];

    // List of allowed fields to update
    $updatable_fields = ['username', 'email', 'is_verified', 'role_id', 'role_name'];
    $data_to_update = [];

    foreach ($updatable_fields as $field) {
        if (isset($input[$field])) {
            $data_to_update[$field] = $input[$field];
        }
    }

    // No valid fields to update
    if (empty($data_to_update)) {
        return $this->response(['status' => false, 'message' => 'No valid data provided for update'], 400);
    }

    // Perform the update
    $this->db->where('id', $id);
    $updated = $this->db->update('users', $data_to_update);

    if ($updated) {
        return $this->response(['status' => true, 'message' => 'User updated successfully'], 200);
    } else {
        return $this->response(['status' => false, 'message' => 'Update failed'], 500);
    }
}

    public function index_post()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        // Input validation
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

        // Check for existing email
        $query = $this->db->get_where("users", ["email" => $input['email']]);
        if ($query->num_rows() > 0) {
            $this->response(["success" => false, "message" => "Email already registered"], 400);
            return;
        }

        // Generate OTP
        $otp = rand(100000, 999999);
        $otp_expiry = date("Y-m-d H:i:s", strtotime("+5 minutes")); // OTP valid for 5 minutes

        // Hash the password
        $hashed_password = password_hash($input['password'], PASSWORD_BCRYPT);
        $role_id = isset($input['role_id']) ? (int) $input['role_id'] : 1;
        $role_name = isset($input['role_name']) ? trim($input['role_name']) : 'User';

        // Insert user
        $data = [
            "username" => $input['username'],
            "email" => $input['email'],
            "password" => $hashed_password,
            "otp" => $otp,
            "otp_expiry" => $otp_expiry,
            "is_verified" => 0,
            "role_id" => $role_id,
            "role_name" => $role_name
        ];

        $this->db->insert("users", $data);

        // Load email library
        $this->load->library('email');

        // Email config
        $config = [
            'protocol' => 'smtp',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_user' => 'ashekm2003@gmail.com',
            'smtp_pass' => 'mwdo xzrv lovj kppr', // App-specific password
            'smtp_crypto' => 'tls',
            'mailtype' => 'html',
            'charset' => 'utf-8',
            'newline' => "\r\n"
        ];
        $this->email->initialize($config);

        // Compose email
        $this->email->from('ashekm2003@gmail.com', 'Event App');
        $this->email->to($input['email']);
        $this->email->subject('Your OTP Code');
        $this->email->message("
            <p>Hello <strong>{$input['username']}</strong>,</p>
            <p>Your OTP code is: <strong>$otp</strong></p>
            <p>This code is valid for 5 minutes.</p>
        ");

        // Send email
        if ($this->email->send()) {
            $this->response([
                "success" => true,
                "message" => "User registered successfully. OTP sent to email.",
                "data" => ["email" => $input['email']]
            ], 201);
        } else {
            $this->response(["success" => false, "message" => "Failed to send OTP email."], 500);
        }
    }

    public function accept_user_put()
{
    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input['id'])) {
        return $this->response(['status' => false, 'message' => 'User ID is required'], 400);
    }

    $id = $input['id'];

    // Optionally: You can also validate whether the user exists
    $this->db->where('id', $id);
    $exists = $this->db->get('users')->row();

    if (!$exists) {
        return $this->response(['status' => false, 'message' => 'User not found'], 404);
    }

    // Update is_accepted to 1
    $this->db->where('id', $id);
    $updated = $this->db->update('users', ['is_accepted' => 1]);

    if ($updated) {
        return $this->response(['status' => true, 'message' => 'User accepted successfully'], 200);
    } else {
        return $this->response(['status' => false, 'message' => 'Failed to update user'], 500);
    }
}



    // 📌 Verify OTP
    public function verify_otp_post()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['email']) || !isset($input['otp'])) {
            $this->response(["success" => false, "message" => "Email and OTP are required"], 400);
            return;
        }

        // Check if user exists
        $query = $this->db->get_where("users", ["email" => $input['email']]);
        if ($query->num_rows() == 0) {
            $this->response(["success" => false, "message" => "Email not registered"], 400);
            return;
        }

        $user = $query->row();

        // Check if OTP is valid (Convert both to string)
        if (strval($user->otp) !== strval($input['otp'])) {
            $this->response(["success" => false, "message" => "Invalid OTP"], 400);
            return;
        }

        // Check if OTP is expired
        if (strtotime($user->otp_expiry) < time()) {
            $this->response(["success" => false, "message" => "OTP expired"], 400);
            return;
        }

        // Mark user as verified
        $this->db->where("email", $input['email']);
        $this->db->update("users", ["otp" => NULL, "otp_expiry" => NULL, "is_verified" => 1]);

        $this->response(["success" => true, "message" => "OTP verified successfully. Account activated."], 200);
    }

    public function resend_otp_post()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['email'])) {
            $this->response(["success" => false, "message" => "Email is required"], 400);
            return;
        }

        // Check if user exists
        $query = $this->db->get_where("users", ["email" => $input['email']]);
        if ($query->num_rows() == 0) {
            $this->response(["success" => false, "message" => "Email not registered"], 400);
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
            'protocol' => 'smtp',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_user' => 'ashekm2003@gmail.com',
            'smtp_pass' => 'mwdo xzrv lovj kppr', // Replace with app password
            'smtp_crypto' => 'tls',                    // Required for Gmail over port 587
            'mailtype' => 'html',
            'charset' => 'utf-8',
            'newline' => "\r\n"
        ];
        $this->email->initialize($config);

        // Compose and send the email
        $this->email->from('ashekm2003@gmail.com', 'Event App');
        $this->email->to($input['email']);
        $this->email->subject('Your New OTP Code');
        $this->email->message("
  <html>
  <head>
    <style>
      body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
      }
      .container {
        max-width: 600px;
        margin: 30px auto;
        background-color: #ffffff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
      }
      .header {
        text-align: center;
        padding-bottom: 20px;
        border-bottom: 1px solid #e0e0e0;
      }
      .header h2 {
        margin: 0;
        color: #333333;
      }
      .content {
        padding: 20px 0;
        font-size: 16px;
        line-height: 1.6;
        color: #555555;
      }
      .otp-box {
        display: inline-block;
        padding: 12px 20px;
        font-size: 24px;
        font-weight: bold;
        color: #ffffff;
        background-color: #4CAF50;
        border-radius: 6px;
        margin: 10px 0;
      }
      .footer {
        text-align: center;
        font-size: 14px;
        color: #999999;
        margin-top: 30px;
      }
    </style>
  </head>
  <body>
    <div class='container'>
      <div class='header'>
        <h2>OTP Verification</h2>
      </div>
      <div class='content'>
        <p>Hello <strong>{$user->username}</strong>,</p>
        <p>Your new OTP code is:</p>
        <div class='otp-box'>$otp</div>
        <p>Please use this code to complete your verification process. This code will expire in <strong>5 minutes</strong>.</p>
      </div>
      <div class='footer'>
        <p>If you did not request this, please ignore this message.</p>
      </div>
    </div>
  </body>
  </html>
");


        if ($this->email->send()) {
            $this->response(["success" => true, "message" => "New OTP sent to your email successfully."], 200);
        } else {
            $this->response(["success" => false, "message" => "Failed to send OTP email."], 500);
        }
    }
}
