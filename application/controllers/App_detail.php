<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

class App_detail extends RestController {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // 📌 Add new app detail
    public function add_post() {
        $data = [
            'fullname' => $this->post('fullname'),
            'email' => $this->post('email'),
            'phonenumber' => $this->post('phonenumber'),
            'message' => $this->post('message'),
            'uploadfile' => $this->post('uploadfile'), // should be a link
            'user_id' => $this->post('user_id'),
        ];

        if ($this->db->insert('app_details', $data)) {
            return $this->response(['status' => true, 'message' => 'Application submitted'], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Submission failed'], 500);
        }
    }

    // 📌 Get all applications
    public function all_get() {
        $query = $this->db->get('app_details');
        return $this->response([
            'status' => true,
            'message' => 'All applications fetched successfully',
            'data' => $query->result()
        ], 200);
    }

    // 📌 Get single application
    public function application_get($id) {
        $app = $this->db->get_where('app_details', ['id' => $id])->row();

        if ($app) {
            return $this->response(['status' => true, 'data' => $app], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Application not found'], 404);
        }
    }

    // 📌 Update application
    public function update_put($id) {
        $input = json_decode(file_get_contents("php://input"), true);
        if (!$input) {
            return $this->response(['status' => false, 'message' => 'No input data'], 400);
        }

        $this->db->where('id', $id);
        if ($this->db->update('app_details', $input)) {
            return $this->response(['status' => true, 'message' => 'Application updated'], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Update failed'], 500);
        }
    }

    // 📌 Delete application
    public function delete_delete($id) {
        $this->db->where('id', $id);
        if ($this->db->delete('app_details')) {
            return $this->response(['status' => true, 'message' => 'Application deleted'], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Delete failed'], 500);
        }
    }

    // ✅ Approve and notify user
    public function approve_post() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['user_id'])) {
            return $this->response(['status' => false, 'message' => 'User ID is required'], 400);
        }

        $userId = $input['user_id'];

        // Get application
        $app = $this->db->get_where('app_details', ['user_id' => $userId])->row();
        if (!$app) {
            return $this->response(['status' => false, 'message' => 'Application not found'], 404);
        }

        // Mark as approved (optional: you can add an 'is_approved' column if needed)
        $this->db->where('user_id', $userId);
        $this->db->update('app_details', ['is_approved' => 1]);

        // Get user
        $user = $this->db->get_where('users', ['id' => $userId])->row();
        if (!$user) {
            return $this->response(['status' => false, 'message' => 'User not found'], 404);
        }

        // Existing project_details
        $projectData = json_decode($user->project_details ?? '[]', true);

        // Append new entry
        $projectData[] = [
            'message' => 'Your project application "' . $app->fullname . '" has been approved.',
            'data' => [
                'id' => $app->id,
                'fullname' => $app->fullname,
                'email' => $app->email,
                'phonenumber' => $app->phonenumber,
                'message' => $app->message,
                'uploadfile' => $app->uploadfile,
                'approved_at' => date("Y-m-d H:i:s")
            ]
        ];

        // Save to users table
        $this->db->where('id', $userId);
        $this->db->update('users', ['project_details' => json_encode($projectData)]);

        return $this->response([
            'status' => true,
            'message' => 'Application approved and user notified'
        ], 200);
    }
}
