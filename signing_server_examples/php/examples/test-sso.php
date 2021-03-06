<?php


require_once('../OpenEd/ApiClient.php');
require_once('../OpenEd/SignedServerRequest.php');


$config = json_decode(file_get_contents('../config.json'), true);

if (!(isset($config['client_id']) && isset($config['client_secret']))) {
    die("You must set your client_id and client_secret in config.json!\n");
}

$client = new OpenEd\ApiClient($config['client_id'], $config['client_secret'], $config['username'], $config['password']);
$signed_server_request = new OpenEd\SignedServerRequest($config['client_id'], $config['client_secret']);
$access_token = $client->getAccessToken();

print "Got an OAuth access token: $access_token\n";

$teacher_username = 'directly_added';

$envelope = [
    'password' => 'password123'
];

$signed_server_request = $signed_server_request->generateSignedRequest($teacher_username, $envelope);

print "Generating a signed server request for username: $teacher_username with the following envelope:\n"
      . json_encode($envelope, JSON_PRETTY_PRINT) . "\n";
print "Signed server request: " . $signed_server_request;

$new_teacher_access_token = $client->postRaw($signed_server_request)['access_token'];

print "Issued signed server request and got an access token for new teacher: $new_teacher_access_token\n";
