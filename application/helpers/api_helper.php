<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('api_response')) {
    function api_response($success, $message, $data = null, $status_code = 200) {
        $ci =& get_instance();
        $response = [
            'success' => $success,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        $ci->output
            ->set_content_type('application/json')
            ->set_status_header($status_code)
            ->set_output(json_encode($response));
    }
}