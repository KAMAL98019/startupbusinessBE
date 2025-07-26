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
        $this->db->select("id, username, email, is_verified, created_at, role_id, role_name, is_accepted, notification_enabled,notification, student_intern, project_details, booksession, investor_book, legal_apply, incubation_book");
        $query = $this->db->get("users");
        $data = $query->result();
        $this->response($data, 200);
    }

    public function get_user_by_id_get($id = null)
    {
        if ($id === null || !is_numeric($id)) {
            return $this->response(['status' => false, 'message' => 'Valid User ID is required'], 400);
        }

        $this->db->select("id, username, email, is_verified, created_at, role_id, role_name, is_accepted ,notification_enabled, notification, student_intern, project_details, booksession, investor_book, legal_apply, incubation_book");
        $this->db->from("users");
        $this->db->where("id", $id);
        $query = $this->db->get();
        $user = $query->row();

        if ($user) {
            return $this->response(['status' => true, 'data' => $user], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'User not found'], 404);
        }
    }

    public function index_put()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['id'])) {
            return $this->response(['status' => false, 'message' => 'User ID is required'], 400);
        }

        $id = $input['id'];

        $updatable_fields = ['username', 'email', 'is_verified', 'role_id', 'role_name', 'notification_enabled'];
        $data_to_update = [];

        foreach ($updatable_fields as $field) {
            if (isset($input[$field])) {
                $data_to_update[$field] = $input[$field];
            }
        }

        if (empty($data_to_update)) {
            return $this->response(['status' => false, 'message' => 'No valid data provided for update'], 400);
        }

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

        $query = $this->db->get_where("users", ["email" => $input['email']]);
        if ($query->num_rows() > 0) {
            $this->response(["success" => false, "message" => "Email already registered"], 400);
            return;
        }

        $otp = rand(100000, 999999);
        $otp_expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        $hashed_password = password_hash($input['password'], PASSWORD_BCRYPT);
        $role_id = isset($input['role_id']) ? (int) $input['role_id'] : 1;
        $role_name = isset($input['role_name']) ? trim($input['role_name']) : 'User';
        $notification_enabled = isset($input['notification_enabled']) ? (int) $input['notification_enabled'] : 1;

        $data = [
            "username" => $input['username'],
            "email" => $input['email'],
            "password" => $hashed_password,
            "otp" => $otp,
            "otp_expiry" => $otp_expiry,
            "is_verified" => 0,
            "role_id" => $role_id,
            "role_name" => $role_name,
            "notification_enabled" => $notification_enabled
        ];

        $this->db->insert("users", $data);

        $this->load->library('email');
        $config = [
            'protocol' => 'smtp',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_user' => 'ashekm2003@gmail.com',
            'smtp_pass' => 'mwdo xzrv lovj kppr',
            'smtp_crypto' => 'tls',
            'mailtype' => 'html',
            'charset' => 'utf-8',
            'newline' => "\r\n"
        ];
        $this->email->initialize($config);

        $this->email->from('ashekm2003@gmail.com', 'Event App');
        $this->email->to($input['email']);
        $this->email->subject('Your OTP Code');
        $this->email->message("
            <p>Hello <strong>{$input['username']}</strong>,</p>
            <p>Your OTP code is: <strong>$otp</strong></p>
            <p>This code is valid for 5 minutes.</p>
        ");

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

        $this->db->where('id', $id);
        $exists = $this->db->get('users')->row();

        if (!$exists) {
            return $this->response(['status' => false, 'message' => 'User not found'], 404);
        }

        $this->db->where('id', $id);
        $updated = $this->db->update('users', ['is_accepted' => 1]);

        if ($updated) {
            return $this->response(['status' => true, 'message' => 'User accepted successfully'], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Failed to update user'], 500);
        }
    }

    public function verify_otp_post()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['email']) || !isset($input['otp'])) {
            $this->response(["success" => false, "message" => "Email and OTP are required"], 400);
            return;
        }

        $query = $this->db->get_where("users", ["email" => $input['email']]);
        if ($query->num_rows() == 0) {
            $this->response(["success" => false, "message" => "Email not registered"], 400);
            return;
        }

        $user = $query->row();

        if (strval($user->otp) !== strval($input['otp'])) {
            $this->response(["success" => false, "message" => "Invalid OTP"], 400);
            return;
        }

        if (strtotime($user->otp_expiry) < time()) {
            $this->response(["success" => false, "message" => "OTP expired"], 400);
            return;
        }

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

        $query = $this->db->get_where("users", ["email" => $input['email']]);
        if ($query->num_rows() == 0) {
            $this->response(["success" => false, "message" => "Email not registered"], 400);
            return;
        }

        $user = $query->row();
        $otp = rand(100000, 999999);
        $otp_expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        $this->db->where("email", $input['email']);
        $this->db->update("users", ["otp" => $otp, "otp_expiry" => $otp_expiry]);

        $this->load->library('email');
        $config = [
            'protocol' => 'smtp',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_user' => 'ashekm2003@gmail.com',
            'smtp_pass' => 'mwdo xzrv lovj kppr',
            'smtp_crypto' => 'tls',
            'mailtype' => 'html',
            'charset' => 'utf-8',
            'newline' => "\r\n"
        ];
        $this->email->initialize($config);

        $this->email->from('ashekm2003@gmail.com', 'Event App');
        $this->email->to($input['email']);
        $this->email->subject('Your New OTP Code');
        $this->email->message("
            <html>
            <head>
                <style>
                    body { font-family: Arial; background-color: #f4f4f4; }
                    .container { max-width: 600px; background: #fff; padding: 20px; margin: auto; border-radius: 10px; }
                    .otp-box { font-size: 22px; font-weight: bold; color: white; background: #4CAF50; padding: 10px; border-radius: 5px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>OTP Verification</h2>
                    <p>Hello <strong>{$user->username}</strong>,</p>
                    <p>Your new OTP code is:</p>
                    <div class='otp-box'>$otp</div>
                    <p>This code is valid for 5 minutes.</p>
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

    public function get_notifications_get($id = null)
{
    if ($id === null || !is_numeric($id)) {
        return $this->response(['status' => false, 'message' => 'Valid User ID is required'], 400);
    }

    $this->db->select('notification');
    $this->db->from('users');
    $this->db->where('id', $id);
    $query = $this->db->get();
    $row = $query->row();

    if (!$row) {
        return $this->response(['status' => false, 'message' => 'User not found'], 404);
    }

    $notifications = json_decode($row->notification ?? '[]', true);
    return $this->response(['status' => true, 'notifications' => $notifications], 200);
}


// public function add_notification_post()
// {
//     $input = json_decode(file_get_contents("php://input"), true);

//     if (!isset($input['id']) || !isset($input['message'])) {
//         return $this->response(['status' => false, 'message' => 'User ID and message are required'], 400);
//     }

//     $id = $input['id'];
//     $message = trim($input['message']);
//     $timestamp = date("Y-m-d H:i:s");

//     $user = $this->db->get_where('users', ['id' => $id])->row();

//     if (!$user) {
//         return $this->response(['status' => false, 'message' => 'User not found'], 404);
//     }

//     $notifications = json_decode($user->notification ?? '[]', true);
//     $notifications[] = ['message' => $message, 'time' => $timestamp];

//     $this->db->where('id', $id);
//     $this->db->update('users', ['notification' => json_encode($notifications)]);

//     return $this->response(['status' => true, 'message' => 'Notification added'], 200);
// }


public function delete_notifications_delete($id = null)
{
    if ($id === null || !is_numeric($id)) {
        return $this->response(['status' => false, 'message' => 'Valid User ID is required'], 400);
    }

    $user = $this->db->get_where('users', ['id' => $id])->row();

    if (!$user) {
        return $this->response(['status' => false, 'message' => 'User not found'], 404);
    }

    $this->db->where('id', $id);
    $this->db->update('users', ['notification' => json_encode([])]);

    return $this->response(['status' => true, 'message' => 'All notifications deleted'], 200);
}


}
