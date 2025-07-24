<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

class PostJob extends RestController {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // 📌 Add a Job
    public function add_post() {
        $data = [
            'jobtitle' => $this->post('jobtitle'),
            'category' => $this->post('category'),
            'company_name' => $this->post('company_name'),
            'jobdescription' => $this->post('jobdescription'),
            'worktype' => $this->post('worktype'),
            'skills_expertise' => json_encode($this->post('skills_expertise')),
            'salary_from' => $this->post('salary_from'),
            'salary_to' => $this->post('salary_to'),
            'applicationdeadline' => $this->post('applicationdeadline'),
            'resumeupload' => $this->post('resumeupload'),
            'applylink' => $this->post('applylink'),
            'user_id' => $this->post('user_id'),
        ];

        if ($this->db->insert('post_job', $data)) {
            return $this->response(['status' => true, 'message' => 'Job Posted'], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Failed to post job'], 500);
        }
    }

    // 📌 Get all or single job
    // 📌 Get all or single job
    // public function index_get($id = null) {
    //     if ($id == null) {
    //         $query = $this->db->get('post_job');
    //         $result = $query->result();
    //     } else {
    //         $this->db->where('id', $id);
    //         $query = $this->db->get('post_job');
    //         $result = $query->row();
    //     }

    //     return $this->response(['status' => true, 'data' => $result], 200);
    // }

// 📌 Get all jobs
public function all_get() {
    $query = $this->db->get('post_job');
    $result = $query->result();

    return $this->response([
        'status' => true,
        'message' => 'All jobs fetched successfully',
        'data' => $result
    ], 200);
}


// 📌 Get a single job by ID
public function job_get($id) {
    $job = $this->db->get_where('post_job', ['id' => $id])->row();

    if ($job) {
        return $this->response([
            'status' => true,
            'message' => 'Job found',
            'data' => $job
        ], 200);
    } else {
        return $this->response([
            'status' => false,
            'message' => 'Job not found'
        ], 404);
    }
}

   
public function update_put($id) {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input) {
        return $this->response(['status' => false, 'message' => 'No input data'], 400);
    }

    if (isset($input['skills_expertise']) && is_array($input['skills_expertise'])) {
        $input['skills_expertise'] = json_encode($input['skills_expertise']);
    }

    $this->db->where('id', $id);
    if ($this->db->update('post_job', $input)) {
        return $this->response(['status' => true, 'message' => 'Job Updated'], 200);
    } else {
        return $this->response(['status' => false, 'message' => 'Update Failed'], 500);
    }
}



    // 📌 Delete a job
    public function delete_delete($id) {
        $this->db->where('id', $id);
        if ($this->db->delete('post_job')) {
            return $this->response(['status' => true, 'message' => 'Job Deleted'], 200);
        } else {
            return $this->response(['status' => false, 'message' => 'Delete Failed'], 500);
        }
    }

    // ✅ Approve job and send notification
    public function approve_post() {
        $input = json_decode(file_get_contents("php://input"), true);
        if (!isset($input['id'])) {
            return $this->response(['status' => false, 'message' => 'Job ID is required'], 400);
        }

        $id = $input['id'];

        // Check if job exists
        $job = $this->db->get_where('post_job', ['id' => $id])->row();
        if (!$job) {
            return $this->response(['status' => false, 'message' => 'Job not found'], 404);
        }

        // Approve the job
        $this->db->where('id', $id);
        $this->db->update('post_job', ['is_approved' => 1]);

        // Get the user
        $user = $this->db->get_where('users', ['id' => $job->user_id])->row();
        if (!$user) {
            return $this->response(['status' => false, 'message' => 'User not found'], 404);
        }

        // Append new notification
        $notifications = json_decode($user->notification ?? '[]', true);
        $notifications[] = [
            'message' => 'Your job post "' . $job->jobtitle . '" has been approved.',
            'time' => date("Y-m-d H:i:s")
        ];

        // Save updated notifications
        $this->db->where('id', $user->id);
        $this->db->update('users', ['notification' => json_encode($notifications)]);

        return $this->response(['status' => true, 'message' => 'Job approved and user notified'], 200);
    }
    

}
