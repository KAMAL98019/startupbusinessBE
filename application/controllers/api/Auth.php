<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/RestController.php';

use chriskacerguis\RestServer\RestController;

class Auth extends RestController
{

  public function __construct()
  {
    parent::__construct();
    $this->load->database();
    $this->load->library('email');
    $this->load->config('email'); // Load email config
  }

  // 1. Send OTP to email
  public function forgot_password_post()
{
    $email = $this->post('email');

    if (!$email) {
        return $this->response(['status' => false, 'message' => 'Email is required'], 400);
    }

    $user = $this->db->get_where('users', ['email' => $email])->row();

    if (!$user) {
        return $this->response(['status' => false, 'message' => 'Email not found'], 404);
    }

    // Generate OTP and expiry
    $otp = rand(100000, 999999);
    $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes")); // Changed from 15 to 5

    // Update user's OTP and expiry
    $this->db->where('email', $email)->update('users', [
        'otp_code' => $otp,
        'otp_expiration' => $expiry
    ]);

    $this->load->library('email');

    $config = [
        'protocol'    => 'smtp',
        'smtp_host'   => 'smtp.gmail.com',
        'smtp_port'   => 587,
        'smtp_user'   => 'ashekm2003@gmail.com',
        'smtp_pass'   => 'mwdo xzrv lovj kppr', // Replace with app password
        'smtp_crypto' => 'tls', // Required for Gmail over port 587
        'mailtype'    => 'html',
        'charset'     => 'utf-8',
        'newline'     => "\r\n"
    ];
    $this->email->initialize($config);

    // Send OTP via email
    $this->email->from('ashekm2003@gmail.com', 'Event');
    $this->email->to($email);
    $this->email->subject('Your OTP Code');
    $this->email->message("Your OTP is: <strong>$otp</strong><br><br>This OTP is valid for <strong>5 minutes</strong>.");

    if ($this->email->send()) {
        return $this->response(['status' => true, 'message' => 'OTP sent to your email']);
    } else {
        return $this->response([
            'status' => false,
            'message' => 'Failed to send OTP',
            'debug' => $this->email->print_debugger(['headers'])
        ], 500);
    }
}


  // 2. Verify OTP
  public function verify_otp_post()
  {
    $email = $this->post('email');
    $otp = $this->post('otp');

    if (!$email || !$otp) {
      return $this->response(['status' => false, 'message' => 'Email and OTP are required'], 400);
    }

    $user = $this->db->get_where('users', ['email' => $email])->row();

    if (!$user) {
      return $this->response(['status' => false, 'message' => 'User not found'], 404);
    }

    if ($user->otp_code == $otp && $user->otp_expiration > date("Y-m-d H:i:s")) {
      return $this->response(['status' => true, 'message' => 'OTP verified']);
    } else {
      return $this->response(['status' => false, 'message' => 'Invalid or expired OTP'], 400);
    }
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
    $this->db->update("users", ["otp_code" => $otp, "otp_expiration" => $otp_expiry]);

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
  // 3. Reset Password
  public function reset_password_post()
{
    $email = $this->post('email');
    $new_password = $this->post('password');

    if (!$email || !$new_password) {
        return $this->response(['status' => false, 'message' => 'Email and password are required'], 400);
    }

    $user = $this->db->get_where('users', ['email' => $email])->row();

    if (!$user) {
        return $this->response(['status' => false, 'message' => 'User not found'], 404);
    }

    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

    $this->db->where('email', $email)->update('users', [
        'password' => $hashed_password,
        'otp_code' => null,
        'otp_expiration' => null
    ]);

    return $this->response(['status' => true, 'message' => 'Password reset successful']);
}

}
