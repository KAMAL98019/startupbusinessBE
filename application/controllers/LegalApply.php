<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

class LegalApply extends RestController {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // 📌 Add Legal Apply
    public function add_post() {
        $data = [
            'fullname'        => $this->post('fullname'),
            'email'           => $this->post('email'),
            'phonenumber'     => $this->post('phonenumber'),
            'purposesession'  => $this->post('purposesession'),
            'uploadfile'      => $this->post('uploadfile'),
            'date'            => $this->post('date'),
            'time'            => $this->post('time'),
            'user_id'         => $this->post('user_id'),
        ];

        if ($this->db->insert('legal_apply', $data)) {
            return $this->response(['status' => true, 'message' => 'Legal application submitted'], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Failed to submit application'], 500);
        }
    }

    // 📌 Get all legal applications
    public function all_get() {
        $query = $this->db->get('legal_apply');
        return $this->response([
            'status'  => true,
            'message' => 'All legal applications fetched successfully',
            'data'    => $query->result()
        ], 200);
    }

    // 📌 Get single application
    public function application_get($id) {
        $application = $this->db->get_where('legal_apply', ['id' => $id])->row();

        if ($application) {
            return $this->response(['status' => true, 'data' => $application], 200);
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
        if ($this->db->update('legal_apply', $input)) {
            return $this->response(['status' => true, 'message' => 'Application updated'], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Update failed'], 500);
        }
    }

    // 📌 Delete application
    public function delete_delete($id) {
        $this->db->where('id', $id);
        if ($this->db->delete('legal_apply')) {
            return $this->response(['status' => true, 'message' => 'Application deleted'], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Delete failed'], 500);
        }
    }

    // ✅ Approve application and notify user
    public function approve_post() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['user_id'])) {
            return $this->response(['status' => false, 'message' => 'User ID is required'], 400);
        }

        $id = $input['user_id'];

        // Get application
        $application = $this->db->get_where('legal_apply', ['user_id' => $id])->row();
        if (!$application) {
            return $this->response(['status' => false, 'message' => 'Application not found'], 404);
        }

        // Approve application
        $this->db->where('user_id', $id);
        $this->db->update('legal_apply', ['is_approved' => 1]);

        // Get user
        $user = $this->db->get_where('users', ['id' => $application->user_id])->row();
        if (!$user) {
            return $this->response(['status' => false, 'message' => 'User not found'], 404);
        }

        // Existing notifications
        $legalNotifications = json_decode($user->legal_apply ?? '[]', true);

        // Add new notification
        $legalNotifications[] = [
            'message' => 'Your legal consultation booking "' . $application->fullname . '" has been approved.',
            'data' => [
                'id'             => $application->id,
                'fullname'       => $application->fullname,
                'email'          => $application->email,
                'phonenumber'    => $application->phonenumber,
                'purposesession' => $application->purposesession,
                'uploadfile'     => $application->uploadfile,
                'date'           => $application->date,
                'time'           => $application->time,
                'approved_at'    => date("Y-m-d H:i:s")
            ]
        ];

        // Update user notification column
        $this->db->where('id', $user->id);
        $this->db->update('users', ['legal_apply' => json_encode($legalNotifications)]);

        return $this->response([
            'status' => true,
            'message' => 'Application approved and user notified'
        ], 200);
    }
}
