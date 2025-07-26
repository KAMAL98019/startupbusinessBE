<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

class Investor_Book extends RestController {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // 📌 Add Investor Booking
    public function add_post() {
        $data = [
            'fullname'        => $this->post('fullname'),
            'email'           => $this->post('email'),
            'phonenumber'     => $this->post('phonenumber'),
            'purposesession'  => $this->post('purposesession'),
            'uploadfile'      => $this->post('uploadfile'), // optional file link
            'date'            => $this->post('date'),
            'time'            => $this->post('time'),
            'user_id'         => $this->post('user_id'),
        ];

        if ($this->db->insert('investor_book', $data)) {
            return $this->response(['status' => true, 'message' => 'Investor booking submitted'], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Failed to submit booking'], 500);
        }
    }

    // 📌 Get all bookings
    public function all_get() {
        $query = $this->db->get('investor_book');
        return $this->response([
            'status'  => true,
            'message' => 'All investor bookings fetched successfully',
            'data'    => $query->result()
        ], 200);
    }

    // 📌 Get single booking
    public function booking_get($id) {
        $booking = $this->db->get_where('investor_book', ['id' => $id])->row();

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
        if ($this->db->update('investor_book', $input)) {
            return $this->response(['status' => true, 'message' => 'Booking updated'], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Update failed'], 500);
        }
    }

    // 📌 Delete booking
    public function delete_delete($id) {
        $this->db->where('id', $id);
        if ($this->db->delete('investor_book')) {
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

        $id = $input['user_id'];

        // Get booking
        $booking = $this->db->get_where('investor_book', ['user_id' => $id])->row();
        if (!$booking) {
            return $this->response(['status' => false, 'message' => 'Booking not found'], 404);
        }

        // Approve booking
        $this->db->where('user_id', $id);
        $this->db->update('investor_book', ['is_approved' => 1]);

        // Get user
        $user = $this->db->get_where('users', ['id' => $booking->user_id])->row();
        if (!$user) {
            return $this->response(['status' => false, 'message' => 'User not found'], 404);
        }

        // Existing notifications
        $investorNotifications = json_decode($user->investor_book ?? '[]', true);

        // Add new notification
        $investorNotifications[] = [
            'message' => 'Your investor session booking "' . $booking->fullname . '" has been approved.',
            'data' => [
                'id'             => $booking->id,
                'fullname'       => $booking->fullname,
                'email'          => $booking->email,
                'phonenumber'    => $booking->phonenumber,
                'purposesession' => $booking->purposesession,
                'uploadfile'     => $booking->uploadfile,
                'date'           => $booking->date,
                'time'           => $booking->time,
                'approved_at'    => date("Y-m-d H:i:s")
            ]
        ];

        // Update user notification column
        $this->db->where('id', $user->id);
        $this->db->update('users', ['investor_book' => json_encode($investorNotifications)]);

        return $this->response([
            'status' => true,
            'message' => 'Booking approved and user notified'
        ], 200);
    }
}
