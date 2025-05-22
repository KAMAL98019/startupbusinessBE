<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/RestController.php';

use chriskacerguis\RestServer\RestController;

class Auth extends RestController {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('email');
        $this->load->config('email'); // Load email config
    }

    // 1. Send OTP to email
    public function forgot_password_post() {
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
        $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

        // Update user's OTP and expiry
        $this->db->where('email', $email)->update('users', [
            'otp_code' => $otp,
            'otp_expiration' => $expiry
        ]);

        // Send OTP via email
        $this->email->from('ashekm2003@gmail.com', 'Event'); // change sender
        $this->email->to($email);
        $this->email->subject('Your OTP Code');
        $this->email->message("Your OTP is: <strong>$otp</strong><br><br>This OTP is valid for 15 minutes.");

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
    public function verify_otp_post() {
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

    // 3. Reset Password
    public function reset_password_post() {
        $email = $this->post('email');
        $otp = $this->post('otp');
        $new_password = $this->post('password');

        if (!$email || !$otp || !$new_password) {
            return $this->response(['status' => false, 'message' => 'Email, OTP, and password are required'], 400);
        }

        $user = $this->db->get_where('users', ['email' => $email])->row();

        if (!$user) {
            return $this->response(['status' => false, 'message' => 'User not found'], 404);
        }

        if ($user->otp_code == $otp && $user->otp_expiration > date("Y-m-d H:i:s")) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            $this->db->where('email', $email)->update('users', [
                'password' => $hashed_password,
                'otp_code' => null,
                'otp_expiration' => null
            ]);

            return $this->response(['status' => true, 'message' => 'Password reset successful']);
        } else {
            return $this->response(['status' => false, 'message' => 'Invalid or expired OTP'], 400);
        }
    }
}
