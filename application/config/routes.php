<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = 'custom404';
$route['translate_uri_dashes'] = FALSE;


$route['user-types']['GET'] = 'api/UserTypes/index_get';
$route['user-types']['POST'] = 'api/UserTypes/index_post';
$route['user-types/(:num)']['PUT'] = 'api/UserTypes/index_put/$1';
$route['user-types/(:num)']['DELETE'] = 'api/UserTypes/index_delete/$1';

$route['api/users']['GET'] = 'api/Users/index_get';  // Get all users
$route['api/users']['POST'] = 'api/Users/index_post';  // Register user (with auto OTP)
$route['api/users/verify-otp']['POST'] = 'api/Users/verify_otp_post';  // Verify OTP
$route['api/users/resend-otp']['POST'] = 'api/Users/resend_otp_post';  // Resend OTP

$route['api/startup']['GET'] = 'api/startup/index_get';  // Get all startups
$route['api/startup']['POST'] = 'api/startup/index_post';  // Register a new startup


$route['api/investor']['GET'] = 'api/investor/index_get';  // Get all investors
$route['api/investor']['POST'] = 'api/investor/index_post';  // Register a new investor

$route['api/student']['GET'] = 'api/student/index_get';  // Get all students
$route['api/student']['POST'] = 'api/student/index_post';  // Register a new student


$route['api/freelancer']['GET'] = 'api/freelancer/index_get';
$route['api/freelancer']['POST'] = 'api/freelancer/index_post';


$route['api/mentor']['GET'] = 'api/mentor/index_get';
$route['api/mentor']['POST'] = 'api/mentor/index_post';
