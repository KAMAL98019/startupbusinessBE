<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

class PostIntern extends RestController {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // 📌 Add Intern Application
    public function add_post() {
        $data = [
            'fullname' => $this->post('fullname'),
            'email' => $this->post('email'),
            'phonenumber' => $this->post('phonenumber'),
            'portfoliolink' => $this->post('portfoliolink'),
            'resume' => $this->post('resume'), // file link
            'shortcoverletter' => $this->post('shortcoverletter'), // text or link
            'user_id' => $this->post('user_id'),
        ];

        if ($this->db->insert('postintern', $data)) {
            return $this->response(['status' => true, 'message' => 'Intern application submitted'], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Failed to submit application'], 500);
        }
    }

    // 📌 Get all intern applications
    public function all_get() {
        $query = $this->db->get('postintern');
        return $this->response([
            'status' => true,
            'message' => 'All applications fetched successfully',
            'data' => $query->result()
        ], 200);
    }

    // 📌 Get single intern application
    public function intern_get($id) {
        $intern = $this->db->get_where('postintern', ['id' => $id])->row();

        if ($intern) {
            return $this->response(['status' => true, 'data' => $intern], 200);
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
        if ($this->db->update('postintern', $input)) {
            return $this->response(['status' => true, 'message' => 'Intern application updated'], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Update failed'], 500);
        }
    }

    // 📌 Delete application
    public function delete_delete($id) {
        $this->db->where('id', $id);
        if ($this->db->delete('postintern')) {
            return $this->response(['status' => true, 'message' => 'Application deleted'], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Delete failed'], 500);
        }
    }

    // ✅ Approve application and notify user
    public function approve_post() {
        $input = json_decode(file_get_contents("php://input"), true);
    
        if (!isset($input['user_id'])) {
            return $this->response(['status' => false, 'message' => 'Intern ID is required'], 400);
        }
    
        $id = $input['user_id'];
    
        // Get intern application
        $intern = $this->db->get_where('postintern', ['user_id' => $id])->row();
        if (!$intern) {
            return $this->response(['status' => false, 'message' => 'Intern not found'], 404);
        }
    
        // Approve the intern
        $this->db->where('user_id', $id);
        $this->db->update('postintern', ['is_approved' => 1]);
    
        // Get the user
        $user = $this->db->get_where('users', ['id' => $intern->user_id])->row();
        if (!$user) {
            return $this->response(['status' => false, 'message' => 'User not found'], 404);
        }
    
        // Existing student_intern notifications
        $studentInternData = json_decode($user->student_intern ?? '[]', true);
    
        // Append new data with message and intern details
        $studentInternData[] = [
            'message' => 'Your internship application "' . $intern->fullname . '" has been approved.',
            'data' => [
                'id' => $intern->id,
                'fullname' => $intern->fullname,
                'email' => $intern->email,
                'phonenumber' => $intern->phonenumber,
                'portfoliolink' => $intern->portfoliolink,
                'resume' => $intern->resume,
                'shortcoverletter' => $intern->shortcoverletter,
                'approved_at' => date("Y-m-d H:i:s")
            ]
        ];
    
        // Update users table with new student_intern JSON
        $this->db->where('id', $user->id);
        $this->db->update('users', ['student_intern' => json_encode($studentInternData)]);
    
        return $this->response([
            'status' => true,
            'message' => 'Intern approved and user notified with data'
        ], 200);
    }
    
}
