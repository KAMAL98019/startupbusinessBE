<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries/RestController.php';
use chriskacerguis\RestServer\RestController;

class Profile_pic extends RestController {

    public function __construct() {
        parent::__construct();
        $this->load->model('Profile_pic_model');
        $this->load->helper('url'); // Ensure base_url works
    }

    // INSERT
    public function insert_post() {
        $user_id = $this->post('user_id');

        // File upload config
        $config['upload_path']   = './uploads/profile/';
        $config['allowed_types'] = 'jpg|jpeg|png|gif';
        $config['max_size']      = 2048;
        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('profile')) {
            $error = strip_tags($this->upload->display_errors());
            $this->response([
                'status' => false,
                'message' => 'Upload failed: ' . $error
            ], 400); // BAD REQUEST
        } else {
            $upload_data = $this->upload->data();
            $file_name = $upload_data['file_name'];

            $data = [
                'user_id' => $user_id,
                'profile' => $file_name
            ];

            $insert = $this->Profile_pic_model->insert_profile_pic($data);

            if ($insert) {
                $this->response([
                    'status' => true,
                    'message' => 'Profile uploaded successfully.',
                    'image_url' => base_url('uploads/profile/' . $file_name)
                ], 200); // OK
            } else {
                $this->response([
                    'status' => false,
                    'message' => 'Database insert failed.'
                ], 500); // SERVER ERROR
            }
        }
    }

    // UPDATE
    public function update_put($id) {
        $data = [
            'user_id' => $this->put('user_id'),
            'profile' => $this->put('profile')
        ];

        $update = $this->Profile_pic_model->update_profile_pic($id, $data);
        if ($update) {
            $this->response(['status' => true, 'message' => 'Profile picture updated.'], 200);
        } else {
            $this->response(['status' => false, 'message' => 'Update failed.'], 400);
        }
    }

    // DELETE
    public function delete_delete($id) {
        $delete = $this->Profile_pic_model->delete_profile_pic($id);
        if ($delete) {
            $this->response(['status' => true, 'message' => 'Profile picture deleted.'], 200);
        } else {
            $this->response(['status' => false, 'message' => 'Delete failed.'], 400);
        }
    }

    // FETCH ALL
    public function fetch_get() {
        $result = $this->Profile_pic_model->get_all_profiles();
        if ($result) {
            foreach ($result as &$row) {
                $row->image_url = base_url('uploads/profile/' . $row->profile);
            }
            $this->response([
                'status' => true,
                'data' => $result
            ], 200);
        } else {
            $this->response([
                'status' => false,
                'message' => 'No profiles found.'
            ], 404);
        }
    }

    // FETCH SINGLE USER
    public function user_get($user_id) {
        $result = $this->Profile_pic_model->get_by_user_id($user_id);
        if ($result) {
            $result->image_url = base_url('uploads/profile/' . $result->profile);
            $this->response([
                'status' => true,
                'data' => $result
            ], 200);
        } else {
            $this->response([
                'status' => false,
                'message' => 'Profile not found.'
            ], 404);
        }
    }
}
