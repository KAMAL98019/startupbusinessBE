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


$route['api/login']['POST'] = 'api/login/login';
$route['api/users/getusers']['GET'] = 'api/users/index_get';  // Get all users
$route['api/users/register']['POST'] = 'api/users/index_post';  // Register user (with auto OTP)
$route['api/users/verify-otp']['POST'] = 'api/users/verify_otp_post';  // Verify OTP
$route['api/users/resend-otp']['POST'] = 'api/users/resend_otp_post';  // Resend OTP
// $route['api/users/update_user/(:num)']['PUT'] = 'api/Users/update_user_put/$1';
$route['api/users']['put'] = 'api/users/index_put';
$route['api/users/(:num)']['get'] = 'api/users/get_user_by_id_get/$1';
$route['api/users/accept_user']['put'] = 'api/users/accept_user_put';
$route['api/users/notifications/(:num)'] = 'api/users/get_notifications/$1';
// $route['api/users/add-notification'] = 'api/users/add_notification';
$route['api/users/delete-notifications/(:num)'] = 'api/users/delete_notifications/$1';


$route['api/startup']['GET'] = 'api/startup/index_get';  // Get all startups
$route['api/startup']['POST'] = 'api/startup/index_post';  // Register a new startup
$route['api/startup/(:num)']['get'] = 'api/startup/get_startup_by_id/$1';

$route['api/investor']['GET'] = 'api/investor/index_get';  // Get all investors
$route['api/investor']['POST'] = 'api/investor/index_post';  // Register a new investor
$route['api/investor/(:num)']['get'] = 'api/investor/get_investor_by_id/$1';


$route['api/student']['GET'] = 'api/student/index_get';  // Get all students
$route['api/student']['POST'] = 'api/student/index_post';  // Register a new student
$route['api/student/(:num)']['get'] = 'api/student/get_student_by_id/$1';



$route['api/freelancer']['GET'] = 'api/freelancer/index_get';
$route['api/freelancer']['POST'] = 'api/freelancer/index_post';
$route['api/freelancer/(:num)']['get'] = 'api/freelancer/get_freelancer_by_id/$1';



$route['api/mentor']['GET'] = 'api/mentor/index_get';
$route['api/mentor']['POST'] = 'api/mentor/index_post';
$route['api/mentor/(:num)']['get'] = 'api/mentor/get_mentor_by_id/$1';



$route['api/hr']['GET'] = 'api/hr/index_get';
$route['api/hr']['POST'] = 'api/hr/index_post';
$route['api/hr/(:num)']['get'] = 'api/hr/get_hr_by_id/$1';



$route['api/incubation']['GET'] = 'api/incubation/index_get';
$route['api/incubation']['POST'] = 'api/incubation/index_post';
$route['api/incubation/(:num)']['get'] = 'api/incubation/get_incubation_by_id/$1';


$route['api/legal']['GET'] = 'api/legal/index_get';
$route['api/legal']['POST'] = 'api/legal/index_post';
$route['api/legal/(:num)']['get'] = 'api/legal/get_legal_by_id/$1';


$route['api/auth/forgot_password']['post'] = 'api/auth/forgot_password';
$route['api/auth/verify_otp']['post']      = 'api/auth/verify_otp';
$route['api/auth/reset_password']['post']  = 'api/auth/reset_password';
$route['api/auth/resend_otp']['post'] = 'api/auth/resend_otp';

$route['profile']['post'] = 'ProfileApi/create_profile';
$route['profile/(:num)']['get'] = 'ProfileApi/get_profile/$1';
$route['profile']['get'] = 'ProfileApi/get_profile';
$route['profile/(:num)']['put'] = 'ProfileApi/update_profile/$1';


$route['profile_pic/insert'] = 'profile_pic/insert';
$route['profile_pic/update/(:num)']['post'] = 'Profile_pic/update/$1';
$route['profile_pic/delete/(:num)'] = 'profile_pic/delete/$1';
$route['profile_pic/user/(:num)']['get'] = 'profile_pic/user/$1';
$route['profile_pic/fetch']['get'] = 'profile_pic/fetch';


// Profile API DASH Routes
$route['profileapidash/create_profile']['post']      = 'ProfileApiDash/create_profile';
$route['profileapidash/get_profile']['get']          = 'ProfileApiDash/get_profile';
$route['profileapidash/get_profile/(:num)']['get']   = 'ProfileApiDash/get_profile/$1';
$route['profileapidash/update_profile/(:num)']['put'] = 'ProfileApiDash/update_profile/$1';


$route['postjob']['post'] = 'PostJob/add';
$route['postjob']['GET'] = 'PostJob/all';
$route['postjob/(:num)']['get'] = 'PostJob/job/$1';
$route['postjob/update/(:num)']['post'] = 'PostJob/update_post/$1';
$route['postjob/delete/(:num)']['delete'] = 'PostJob/delete_delete/$1';
$route['postjob/approve']['POST'] = 'postjob/approve';


// --- PostProject Routes ---
$route['project/add']['post'] = 'PostProject/add';
$route['project/all']['get'] = 'PostProject/all';
$route['project/(:num)']['get'] = 'PostProject/project/$1';
$route['project/update/(:num)']['put'] = 'PostProject/update/$1';
$route['project/delete/(:num)']['delete'] = 'PostProject/delete/$1';
$route['project/approve']['post'] = 'PostProject/approve';



