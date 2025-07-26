<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

class PostProject extends RestController {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // 📌 Add a new project
    public function add_post() {
        $data = [
            'projecttitle' => $this->post('projecttitle'),
            'projectdescription' => $this->post('projectdescription'),
            'requiredtech' => json_encode($this->post('requiredtech')),
            'projectbudget' => $this->post('projectbudget'),
            'projectfile' => $this->post('projectfile'),
            'projectthumbnail' => $this->post('projectthumbnail'),
            'projecttimelinefrom' => $this->post('projecttimelinefrom'),
            'projecttimelineto' => $this->post('projecttimelineto'),
            'user_id' => $this->post('user_id'),
            'is_approved' => 0
        ];

        if ($this->db->insert('postproject', $data)) {
            return $this->response(['status' => true, 'message' => 'Project Posted'], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Failed to post project'], 500);
        }
    }

    // 📌 Get all projects
    public function all_get() {
        $query = $this->db->get('postproject');
        $result = $query->result();

        return $this->response([
            'status' => true,
            'message' => 'All projects fetched successfully',
            'data' => $result
        ], 200);
    }

    // 📌 Get a single project
    public function project_get($id) {
        $project = $this->db->get_where('postproject', ['id' => $id])->row();

        if ($project) {
            return $this->response([
                'status' => true,
                'message' => 'Project found',
                'data' => $project
            ], 200);
        } else {
            return $this->response([
                'status' => false,
                'message' => 'Project not found'
            ], 404);
        }
    }

    // 📌 Update project
    public function update_put($id) {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!$input) {
            return $this->response(['status' => false, 'message' => 'No input data'], 400);
        }

        if (isset($input['requiredtech']) && is_array($input['requiredtech'])) {
            $input['requiredtech'] = json_encode($input['requiredtech']);
        }

        $this->db->where('id', $id);
        if ($this->db->update('postproject', $input)) {
            return $this->response(['status' => true, 'message' => 'Project Updated'], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Update Failed'], 500);
        }
    }

    // 📌 Delete project
    public function delete_delete($id) {
        $this->db->where('id', $id);
        if ($this->db->delete('postproject')) {
            return $this->response(['status' => true, 'message' => 'Project Deleted'], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Delete Failed'], 500);
        }
    }

    // 📌 Approve project and notify user
    public function approve_post() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['user_id'])) {
            return $this->response(['status' => false, 'message' => 'Project ID is required'], 400);
        }

        $id = $input['user_id'];

        // Check if project exists
        $project = $this->db->get_where('postproject', ['user_id' => $id])->row();
        if (!$project) {
            return $this->response(['status' => false, 'message' => 'Project not found'], 404);
        }

        // Approve the project
        $this->db->where('user_id', $id);
        $this->db->update('postproject', ['is_approved' => 1]);

        // Get the user
        $user = $this->db->get_where('users', ['id' => $project->user_id])->row();
        if (!$user) {
            return $this->response(['status' => false, 'message' => 'User not found'], 404);
        }

        // Append new notification
        $notifications = json_decode($user->notification ?? '[]', true);
        $notifications[] = [
            'message' => 'Your project "' . $project->projecttitle . '" has been approved.',
            'time' => date("Y-m-d H:i:s")
        ];

        // Update user notification column
        $this->db->where('id', $user->id);
        $this->db->update('users', ['notification' => json_encode($notifications)]);

        return $this->response([
            'status' => true,
            'message' => 'Project approved and user notified'
        ], 200);
    }
}
