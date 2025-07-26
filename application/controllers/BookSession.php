<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

class BookSession extends RestController {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // 📌 Add a new session booking
    public function add_post() {
        $data = [
            'fullname' => $this->post('fullname'),
            'email' => $this->post('email'),
            'phonenumber' => $this->post('phonenumber'),
            'purposesession' => $this->post('purposesession'),
            'date' => $this->post('date'),
            'time' => $this->post('time'),
            'user_id' => $this->post('user_id'),
        ];

        if ($this->db->insert('booksession', $data)) {
            return $this->response(['status' => true, 'message' => 'Session booked successfully'], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Failed to book session'], 500);
        }
    }

    // 📌 Get all session bookings
    public function all_get() {
        $query = $this->db->get('booksession');
        return $this->response([
            'status' => true,
            'message' => 'All bookings fetched successfully',
            'data' => $query->result()
        ], 200);
    }

    // 📌 Get single session booking
    public function session_get($id) {
        $session = $this->db->get_where('booksession', ['id' => $id])->row();

        if ($session) {
            return $this->response(['status' => true, 'data' => $session], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Booking not found'], 404);
        }
    }

    // 📌 Update session booking
    public function update_put($id) {
        $input = json_decode(file_get_contents("php://input"), true);
        if (!$input) {
            return $this->response(['status' => false, 'message' => 'No input data'], 400);
        }

        $this->db->where('id', $id);
        if ($this->db->update('booksession', $input)) {
            return $this->response(['status' => true, 'message' => 'Booking updated'], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Update failed'], 500);
        }
    }

    // 📌 Delete session booking
    public function delete_delete($id) {
        $this->db->where('id', $id);
        if ($this->db->delete('booksession')) {
            return $this->response(['status' => true, 'message' => 'Booking deleted'], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Delete failed'], 500);
        }
    }

    // ✅ Approve session and notify user
    public function approve_post() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['user_id'])) {
            return $this->response(['status' => false, 'message' => 'User ID is required'], 400);
        }

        $id = $input['user_id'];

        // Get booking
        $session = $this->db->get_where('booksession', ['user_id' => $id])->row();
        if (!$session) {
            return $this->response(['status' => false, 'message' => 'Booking not found'], 404);
        }

        // Approve booking (optional: add is_approved column if needed)
        $this->db->where('user_id', $id);
        $this->db->update('booksession', ['is_approved' => 1]);

        // Get user
        $user = $this->db->get_where('users', ['id' => $session->user_id])->row();
        if (!$user) {
            return $this->response(['status' => false, 'message' => 'User not found'], 404);
        }

        // Existing notifications
        $projectDetails = json_decode($user->booksession ?? '[]', true);

        // Add new session approval notification
        $projectDetails[] = [
            'message' => 'Your session "' . $session->fullname . '" on ' . $session->date . ' has been approved.',
            'data' => [
                'id' => $session->id,
                'fullname' => $session->fullname,
                'email' => $session->email,
                'phonenumber' => $session->phonenumber,
                'purposesession' => $session->purposesession,
                'date' => $session->date,
                'time' => $session->time,
                'approved_at' => date("Y-m-d H:i:s")
            ]
        ];

        // Update user with new booksession
        $this->db->where('id', $user->id);
        $this->db->update('users', ['booksession' => json_encode($projectDetails)]);

        return $this->response([
            'status' => true,
            'message' => 'Session approved and user notified'
        ], 200);
    }
}
