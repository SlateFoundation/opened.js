<?php


require_once('./OpenEd/ApiClient.php');
require_once('./OpenEd/SignedServerRequest.php');


$config = json_decode(file_get_contents('./config.json'), true);

if (!(isset($config['client_id']) && isset($config['client_secret']))) {
    die("You must set your client_id and client_secret in config.json!\n");
}

$client = new OpenEd\ApiClient($config['client_id'], $config['client_secret'], $config['username'], $config['password']);
$signed_server_request = new OpenEd\SignedServerRequest($config['client_id'], $config['client_secret'], '260027801484');
$access_token = $client->getAccessToken();

print "Got an OAuth access token: $access_token\n";

$teacher_username = 'herbertgarrison';

$envelope = [
    'role' => 'teacher',
    'first_name' => 'Herbert',
    'last_name' => 'Garrison',
    'password' => 'mrtwig123'
];


$signed_server_request = $signed_server_request->generateSignedRequest($teacher_username, $envelope);

print "Generating a signed server request for username: $teacher_username with the following envelope:\n" . json_encode($envelope, JSON_PRETTY_PRINT) . "\n";
print "Signed server request: " . $signed_server_request;

$new_teacher_access_token = $client->postRaw($signed_server_request)['access_token'];

print "Issued signed server request and got an access token for new teacher: $new_teacher_access_token\n";

$client->useAccessToken($new_teacher_access_token);

$class_name = 'Test class';
$grade_range = '5-6';

print "Using the new access token, attempting to create a class $class_name with the grade range $grade_range...\n";

function createClass($class_name, $grade_range) {
    global $client;

    $test_class = $client->createClass($class_name, $grade_range);

    $test_class_id = $test_class['class']['id'];

    print "Created class $class_name ($grade_range); id: $test_class_id\n";

    print "Verifying that class id: $test_class_id appears in the teacher's list of classes\n";

    $my_classes = $client->getClasses();

    print json_encode($my_classes, JSON_PRETTY_PRINT);

    $my_class_objects = $my_classes['classes'];

    $found_class = false;

    foreach($my_class_objects as $class) {
        if ($class['id'] == $test_class_id) {
            $found_class = true;
            break;
        }
    }

    if ($found_class) {
        print "PASS: Class was found in list of classes\n";
    } else {
        die('FAIL: Class was not found in list of classes');
    }

    return $test_class;
}

$test_class = createClass($class_name, $grade_range);
$test_class_id = $test_class['class']['id'];

print "Trying to add a new student directly to a class at student creation-time\n";

$directly_added_student = $client->createStudent([
    'first_name' => 'Directly',
    'last_name'  => 'Added',
    'username'   => 'directly_added',
    'password'   => 'password123',
    'class_ids'  => [$test_class_id]
]);

$directly_added_student_id = $directly_added_student['student']['id'];

print "Trying to add an existing student to a newly created class\n";

$class_name = 'Test class #2';
$grade_range = '8';
$new_class = createClass($class_name, $grade_range);
$new_class_id = $new_class['class']['id'];

print "Trying to add students $directly_added_student_id to class $new_class_id\n";

$client->addStudentToClass($directly_added_student_id, $new_class_id);

print "Verifying that student $directly_added_student_id exists in $new_class_id and $test_class_id\n";

$student = $client->getStudent($directly_added_student_id);

$student_class_ids = $student['student']['class_ids'];

$missing_classes = array_intersect([$new_class_id, $test_class_id], $student_class_ids);

if (count($missing_classes)) {
    print "PASS: student was in expected classes: " . implode(', ', $student_class_ids) . "\n";
} else {
    die("FAIL: student was not in: " . implode(', ', $missing_classes));
}

print "Deleting class $new_class_id and verifying it no longer exists for the teacher and student\n";
$client->deleteClass($new_class_id);

$student = $client->getStudent($directly_added_student_id);
$student_class_ids = $student['student']['class_ids'];

if (in_array($new_class_id, $student_class_ids)) {
    die("FAIL: After deleting $new_class_id it still is associated with the student $directly_added_student_id");
} else {
    print "PASS: Deleting $new_class_id removed it from student $directly_added_student_id's class_ids\n";
}

$my_classes = $client->getClasses();
$my_class_objects = $my_classes['classes'];
$found_class = false;

foreach($my_class_objects as $class) {
    if ($class['id'] == $new_class_id) {
        $found_class = true;
        break;
    }
}

if ($found_class) {
    die("FAIL: After deleting $new_class_id it still is associated with the teacher");
} else {
    print "PASS: Deleting $new_class_id removed it from the teacher's classes\n";
}

print "Attempting to cleanup remaining entities...\n";
$client->deleteStudent($directly_added_student_id);
$client->deleteClass($test_class_id);
