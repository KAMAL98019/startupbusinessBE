<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/RestController.php';

use chriskacerguis\RestServer\RestController;

class Profile extends RestController {

    public function __construct() {
        parent::__construct();
        $this->load->model('Profile_model');
    }

    // POST - Create or update profile
    public function index_post() {
        $user_id = $this->post('user_id');
        
        if (!$user_id) {
            $this->response([
                'status' => FALSE,
                'message' => 'User ID is required'
            ], RestController::HTTP_BAD_REQUEST);
        }

        // Handle file upload if present
        $profile_picture = null;
        if (!empty($_FILES['profile_picture']['name'])) {
            $upload = $this->Profile_model->upload_profile_picture('profile_picture');
            
            if (isset($upload['error'])) {
                $this->response([
                    'status' => FALSE,
                    'message' => $upload['error']
                ], RestController::HTTP_BAD_REQUEST);
            }
            
            $profile_picture = file_get_contents($upload['file_data']['full_path']);
            unlink($upload['file_data']['full_path']); // Remove the temp file after getting contents
        }

        // Prepare data
        $data = array(
            'user_id' => $user_id,
            'phone_number' => $this->post('phone_number'),
            'role' => $this->post('role'),
            'skills' => json_encode($this->post('skills')), // Store skills as JSON array
            'bio' => $this->post('bio')
        );

        if ($profile_picture !== null) {
            $data['profile_picture'] = $profile_picture;
        }

        // Check if profile exists
        $existing_profile = $this->Profile_model->get_profile($user_id);

        if ($existing_profile) {
            // Update existing profile
            $result = $this->Profile_model->update_profile($user_id, $data);
            $message = 'Profile updated successfully';
        } else {
            // Create new profile
            $result = $this->Profile_model->create_profile($data);
            $message = 'Profile created successfully';
        }

        if ($result) {
            $this->response([
                'status' => TRUE,
                'message' => $message,
                'data' => $this->Profile_model->get_profile($user_id)
            ], RestController::HTTP_OK);
        } else {
            $this->response([
                'status' => FALSE,
                'message' => 'Failed to save profile'
            ], RestController::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // GET - Retrieve profile
    public function index_get($id = null) {
        if (!$id) {
            $this->response([
                'status' => FALSE,
                'message' => 'Profile ID is required'
            ], RestController::HTTP_BAD_REQUEST);
        }
    
        $this->load->model('Profile_model');
        $profile = $this->Profile_model->get_profile_by_id($id);
    
        if ($profile) {
            $profile->skills = $profile->skills ? json_decode($profile->skills) : [];
            $profile->profile_picture = $profile->profile_picture ? base64_encode($profile->profile_picture) : null;
    
            $this->response([
                'status' => TRUE,
                'data' => $profile
            ], RestController::HTTP_OK);
        } else {
            $this->response([
                'status' => FALSE,
                'message' => 'Profile not found'
            ], RestController::HTTP_NOT_FOUND);
        }
    }
    
}