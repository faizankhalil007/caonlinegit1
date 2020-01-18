<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('student/login');
});

Auth::routes();
Auth::routes(['verify' => true]);
Route::get('/home', 'HomeController@index')->name('home')->middleware('verified');
Route::post('/send_verification_code','ClassesController@send_mobile_code')->name('send_verification_code');
Route::post('/verify_mobile_code','ClassesController@verifymobilecode')->name('verify_mobile_code');
/* route for classes start */
Route::get('/classes','ClassesController@index')->name('classes');
Route::get('/add_class','ClassesController@create')->name('add_class');
Route::post('/save_class','ClassesController@store')->name('save_class');
Route::get('/edit_class/{id}','ClassesController@edit')->name('edit_class');
Route::post('/update_class','ClassesController@update')->name('update_class');
Route::post('/delete_class','ClassesController@destroy')->name('delete_class');
/* route for classes start */
Route::get('/courses-list','CoursesController@index')->name('courses-list');
Route::get('/courses-by-module/{class_id}','CoursesController@get_course_by_module_id')->name('courses-by-module');
Route::get('/add_courses','CoursesController@create')->name('add_courses');
Route::post('/save_courses','CoursesController@store')->name('save_courses');
Route::get('/edit_course/{id}','CoursesController@edit')->name('edit_course');
Route::post('/update_courses','CoursesController@update')->name('update_courses');
Route::post('/delete_course','CoursesController@destroy')->name('delete_course');
/* route for quiz start */
Route::get('/quiz_list','QuizController@index')->name('quiz_list');
Route::get('/add_quiz','QuizController@create')->name('add_quiz');
Route::post('/save_quiz','QuizController@store')->name('save_quiz');
Route::get('/edit_quiz/{id}','QuizController@edit')->name('edit_quiz');
Route::post('/update_quiz','QuizController@update')->name('update_quiz');
Route::post('/delete_quiz/{id}','QuizController@destroy')->name('delete_quiz');
/* route for save course registration start */
Route::get('/add_class_course','StudentCourseRegistrationController@index')->name('add_class_course');
//Route::get('/save_registration_info','StudentCourseRegistrationController@create')->name('save_registration_info');
Route::post('/save_registration_info','StudentCourseRegistrationController@store')->name('save_registration_info');
//Route::get('/edit_quiz/{id}','StudentCourseRegistrationController@edit')->name('edit_quiz');
//Route::post('/update_quiz','StudentCourseRegistrationController@update')->name('update_quiz');
//Route::post('/delete_quiz/{id}','StudentCourseRegistrationController@destroy')->name('delete_quiz');
/* route to get the courses by class id */
Route::get('/courses_by_class_id/{class_id}','StudentCourseRegistrationController@get_course_by_class_id')->name('courses_by_class_id');
Route::get('/get_quiz_questions/{quiz_id}','QuizController@quiz_questions')->name('get_quiz_questions');
/* get quiz list for student */
Route::get('/student-quiz-list','QuizController@get_quiz_by_student_id')->name('student-quiz-list');
/* open pop up for confirmation */
//Route::get('/student-quiz-list','QuizController@get_quiz_by_student_id')->name('student-quiz-list');
Route::get('/registred-course','StudentCourseRegistrationController@registred_course_information')->name('registred-course');
/* route for student list */
Route::get('students_list','HomeController@regitered_students')->name('students_list');
Route::post('students_list','HomeController@regitered_students')->name('students_list');
Route::post('delete_student','HomeController@delete_student')->name('delete_student');
Route::get('edit_student','HomeController@edit_student')->name('edit_student');
Route::post('update_student','HomeController@update_student')->name('update_student');
Route::get('student_detail','HomeController@student_detail')->name('student_detail');
/* route for admin schedule */
Route::get('schedule_list','TestScheduleController@index')->name('schedule_list');
Route::get('add_schedule','TestScheduleController@create')->name('add_schedule');
Route::post('save_schedule','TestScheduleController@store')->name('save_schedule');
Route::get('edit_schedule/{id}','TestScheduleController@edit')->name('edit_schedule');
Route::post('update_schedule','TestScheduleController@update')->name('update_schedule');
Route::post('delete_schedule','TestScheduleController@destroy')->name('delete_schedule');
Route::get('set_cron_job','SendSMSController@set_cron_job_for_reminder')->name('set_cron_job');
/* Student quiz submission start */
Route::get('quiz-answer','StudentQuizAnswerController@index')->name('quiz-answer');
Route::get('start-student-quiz/{quiz_id}','StudentQuizAnswerController@start_quiz')->name('start-student-quiz');
Route::post('submit-quiz-answer','StudentQuizAnswerController@store')->name('submit-quiz-answer');
Route::get('answer_by_quiz_id/{quiz_id}','StudentQuizAnswerController@show')->name('answer_by_quiz_id');
Route::post('search-student','StudentQuizAnswerController@search_student')->name('search-student');
Route::get('student_quiz_answers','StudentQuizAnswerController@display_answers')->name('student_quiz_answers');
Route::get('add_quiz_marks','StudentQuizAnswerController@add_quiz_marks_and_review')->name('add_quiz_marks');
Route::post('save_quiz_marks','StudentQuizAnswerController@save_quiz_marks_and_remarks')->name('save_quiz_marks');
Route::get('display-student-result','StudentQuizAnswerController@quiz_result')->name('display-student-result');
/* download students answers */
Route::get('download-answer-by-quiz/{question_id}/{student_id}','StudentQuizAnswerController@downloadAnswerImage')->name('download-answer-by-quiz');

/* quiz solutions start */
Route::get('get-quiz-for-result','QuizAnswerController@index')->name('get-quiz-for-result');
Route::get('add-quiz-solution/{quiz_id}','QuizAnswerController@create')->name('add-quiz-solution');
Route::post('save-quiz-solution','QuizAnswerController@store')->name('save-quiz-solution');
Route::get('display-quiz-result','QuizAnswerController@display_result')->name('display-quiz-result');
/* quiz request start */
Route::get('quiz-request','TestRequestController@index')->name('quiz-request');
Route::get('add_test_request','TestRequestController@create')->name('add_test_request');
Route::post('save-test-request','TestRequestController@store')->name('save-test-request');
Route::get('action_test_request','TestRequestController@edit')->name('action_test_request');
Route::post('update_test_request_status','TestRequestController@update')->name('update_test_request_status');
/* for clear cache */
Route::get('/cache-clear', function() {
    $status = Artisan::call('cache:clear');
    return '<h1>Cache cleared</h1>'; die();
});
