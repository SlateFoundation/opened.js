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

$teacher_username = 'sparkmeritteacher';
$envelope = [
    'role' => 'teacher',
    'first_name' => 'Spark',
    'last_name' => 'Merit Teacher',
    'password' => 'SparkPoint2015'
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

$test_class = $client->createClass($class_name, $grade_range);

print_r($test_class);

print "Class created\n";
print "Retrieving all classes for teacher...\n";

$my_classes = $client->getClasses();

print_r($my_classes);