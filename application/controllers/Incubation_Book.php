<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

class Incubation_Book extends RestController {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // 📌 Add incubator booking
    public function add_post() {
        $data = [
            'user_id' => $this->post('user_id'),
            'startupname' => $this->post('startupname'),
            'foundername' => $this->post('foundername'),
            'email' => $this->post('email'),
            'phonenumber' => $this->post('phonenumber'),
            'industry_domain' => $this->post('industry_domain'), // dropdown value
            'teamsize' => $this->post('teamsize'),
            'currentslag' => $this->post('currentslag'), // radio button value
            'pitchsummary' => $this->post('pitchsummary'),
            'pitchdeck' => $this->post('pitchdeck'), // file upload link
        ];

        if ($this->db->insert('incubator_book', $data)) {
            return $this->response(['status' => true, 'message' => 'Incubator booking submitted'], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Failed to submit booking'], 500);
        }
    }

    // 📌 Get all incubator bookings
    public function all_get() {
        $query = $this->db->get('incubator_book');
        return $this->response([
            'status' => true,
            'message' => 'All bookings fetched successfully',
            'data' => $query->result()
        ], 200);
    }

    // 📌 Get single incubator booking by id
    public function booking_get($id) {
        $booking = $this->db->get_where('incubator_book', ['id' => $id])->row();

        if ($booking) {
            return $this->response(['status' => true, 'data' => $booking], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Booking not found'], 404);
        }
    }

    // 📌 Update booking
    public function update_put($id) {
        $input = json_decode(file_get_contents("php://input"), true);
        if (!$input) {
            return $this->response(['status' => false, 'message' => 'No input data'], 400);
        }

        $this->db->where('id', $id);
        if ($this->db->update('incubator_book', $input)) {
            return $this->response(['status' => true, 'message' => 'Booking updated'], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Update failed'], 500);
        }
    }

    // 📌 Delete booking
    public function delete_delete($id) {
        $this->db->where('id', $id);
        if ($this->db->delete('incubator_book')) {
            return $this->response(['status' => true, 'message' => 'Booking deleted'], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Delete failed'], 500);
        }
    }

    // ✅ Approve booking and notify user
    public function approve_post() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['user_id'])) {
            return $this->response(['status' => false, 'message' => 'User ID is required'], 400);
        }

        $user_id = $input['user_id'];

        // Get incubator booking by user_id
        $booking = $this->db->get_where('incubator_book', ['user_id' => $user_id])->row();
        if (!$booking) {
            return $this->response(['status' => false, 'message' => 'Booking not found'], 404);
        }

        // Approve the booking (add an 'is_approved' column to your table if needed)
        $this->db->where('user_id', $user_id);
        $this->db->update('incubator_book', ['is_approved' => 1]);

        // Get the user
        $user = $this->db->get_where('users', ['id' => $user_id])->row();
        if (!$user) {
            return $this->response(['status' => false, 'message' => 'User not found'], 404);
        }

        // Existing incubation_book notifications (JSON)
        $incubationBookData = json_decode($user->incubation_book ?? '[]', true);

        // Append new notification
        $incubationBookData[] = [
            'message' => 'Your incubator booking for startup "' . $booking->startupname . '" has been approved.',
            'data' => [
                'id' => $booking->id,
                'startupname' => $booking->startupname,
                'foundername' => $booking->foundername,
                'email' => $booking->email,
                'phonenumber' => $booking->phonenumber,
                'industry_domain' => $booking->industry_domain,
                'teamsize' => $booking->teamsize,
                'currentslag' => $booking->currentslag,
                'pitchsummary' => $booking->pitchsummary,
                'pitchdeck' => $booking->pitchdeck,
                'approved_at' => date("Y-m-d H:i:s")
            ]
        ];

        // Update users table with new incubation_book JSON data
        $this->db->where('id', $user->id);
        $this->db->update('users', ['incubation_book' => json_encode($incubationBookData)]);

        return $this->response([
            'status' => true,
            'message' => 'Booking approved and user notified'
        ], 200);
    }
}
