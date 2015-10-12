<?php

namespace OpenEd;


class ApiClient
{
    private $client_id;
    private $client_secret;
    private $username;
    private $password;
    private $curl;
    private $access_token;
    private $trigger_errors = true;
    private $verbose = true;

    const BASE_URL = 'https://api.opened.io';
    const PARTNER_BASE_URL = 'https://partner.opened.com';
    const TOKEN_PATH = '/oauth/token';

    public function __construct($client_id, $client_secret, $username = null, $password = null, $access_token = null)
    {
        $this->client_secret = $client_secret;
        $this->client_id = $client_id;
        $this->username = $username;
        $this->password = $password;
        $this->access_token = $access_token;

        $this->curl = curl_init();

        curl_setopt_array($this->curl, [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true
        ]);
    }

    private function generateVerboseError($method, $url, $headers, $fields, $response, $response_code)
    {
        $curl_args = [];

        if ($method != 'GET') {
            $curl_args[] = '-X ' . $method;
        }

        foreach ($headers as $header) {
            $curl_args[] = '-H "' . $header . '"';
        }

        if (count($fields) > 0) {
            $curl_args[] = "-d '" . json_encode($fields, JSON_UNESCAPED_SLASHES) . "'";
        }

        $curl = 'curl ' . implode(' ', $curl_args) . ' ' . $url;

        $error  = "[HTTP $response_code] $method $url\n"
                    . "HEADERS:\n" . json_encode($headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
                    . "BODY\n" . json_encode($fields, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
                    . "RESPONSE:\n" . $response . "\n"
                    . "CURL:\n$curl\n";

        return $error;
    }

    public function getAccessToken($username = null, $password = null, $use_token = true)
    {
        $username = $username ?: $this->username;
        $password = $password ?: $this->password;

        if (!($username && $password)) {
            throw new \InvalidArgumentException('username and password are required parameters if they were not set in ApiClient');
        }

        $response = $this->request('POST', self::TOKEN_PATH, [
            'username' => $username,
            'password' => $password,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'password'
        ]);

        if ($use_token) {
            $this->access_token = $response['access_token'];
        }

        return $response['access_token'];
    }

    public function useAccessToken($access_token)
    {
        $this->access_token = $access_token;
    }

    public function request($method, $path = '/', $params = [], $headers = [], $fields = [], $base_url = self::BASE_URL, $no_auth = false)
    {
        if ($base_url === self::BASE_URL && strpos($path, '/teachers') === 0) {
            $base_url = self::PARTNER_BASE_URL;
        }

        $url = $base_url . $path;

        if (count($params) > 0) {
            $url .= '?' . http_build_query($params);
        }

        $implicit_headers = [];

        if (!$no_auth && $this->access_token) {
            $implicit_headers[] = 'Authorization: Bearer ' . $this->access_token;
        }

        if (!$no_auth && $method == 'POST') {
            $implicit_headers[] = 'Content-Type: application/json';
        }

        $headers = array_merge($headers, $implicit_headers);

        curl_setopt_array($this->curl, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POST => $method != 'GET',
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $method == 'POST' ? json_encode($fields) : null
        ]);

        $response = curl_exec($this->curl);
        $response_code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        if ($response_code >= 400) {
            $this->outputVerboseError($method, $url, $headers, $fields, $response, $response_code);
            throw new \ErrorException("$method $url failed with HTTP $response_code: $response");
        }

        $curl_error = curl_errno($this->curl);

        if ($curl_error) {
            throw new \ErrorException(curl_error($this->curl));
        }

        return json_decode($response, true);
    }


    public function get($path = '/', $params = [], $headers = [], $fields = [])
    {
        return $this->request('GET', $path, $params, $headers, $fields);
    }


    public function put($path = '/', $params = [], $headers = [], $fields = [])
    {
        return $this->request('PUT', $path, $params, $headers, $fields);
    }


    public function post($path = '/', $params = [], $headers = [], $fields = [])
    {
        return $this->request('POST', $path, $params, $headers, $fields);
    }


    public function postRaw($body = '')
    {
        $url = self::BASE_URL . '/oauth/silent_login';

        curl_setopt_array($this->curl, [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POST => true,
            CURLOPT_URL => $url,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => ['Content-Type: text/plain', 'Content-Length: ' . strlen($body)]
        ]);

        $response = curl_exec($this->curl);
        $response_code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);


        print "BODY:\n\n\n$body\n\n\n";

        if ($response_code >= 400) {
            throw new \ErrorException("POST $url failed with HTTP $response_code: $response");
        }



        return json_decode($response, true);
    }


    public function delete($path = '/', $params = [], $headers = [], $fields = [])
    {
        return $this->request('DELETE', $path, $params, $headers, $fields);
    }


    public function getStandardsGroups()
    {
        return $this->get('/standard_groups.json');
    }


    public function getResources($params = [])
    {
        $valid_parameters = [
            'descriptive','limit','offset','standard_group','category','standard','area', 'subject','grade',
            'grade_group','contribution_name','resource_types'
        ];

        $invalid_parameters = array_diff(array_keys($params), $valid_parameters);

        if (count($invalid_parameters) > 0) {
           throw new \InvalidArgumentException('Invalid parameter(s) passed: ' . implode(', ', $invalid_parameters) .
               '; valid parameters are: ' . implode(', ', $valid_parameters));
        }

        return $this->get('/resources.json', $params);
    }

    public function getResource($id)
    {
        return $this->get("/resources/$id.json");
    }

    public function getGradeGroups($standards_group = null)
    {
        return $this->get('/grade_groups.json', $standards_group ? ['standards_group' => $standards_group] : []);
    }

    public function getClasses($ids = [])
    {
        $params = [];

        if (count($ids)) {
            $params['ids'] = $ids;
        }

        return $this->request('GET', '/teachers/classes', $params);
    }

    public function getClass($id) {
        return $this->request('GET', '/teachers/classes/' . $id);
    }

    private static function validateGradeRange($str)
    {
        return preg_match("/^\\d\\-?\\d?$/um", $str);
    }

    public function createClass($title, $grade_range = null)
    {
        $fields = [];

        if (is_string($title)) {
            $fields['title'] = $title;
        } else {
            throw new \ErrorException('Title is required.');
        }

        if ($grade_range) {
            if ($this->validateGradeRange($grade_range)) {
                $fields['grade_range'] = $grade_range;
            } else {
                throw new Error('Invalid grade range, valid formats are: 5, 5-6');
            }
        }

        return $this->post('/teachers/classes', [], [], $fields);
    }

    public function updateClass($class_id, $title, $grade_range = null)
    {
        $fields = [];

        if (!is_numeric($class_id)) {
            throw new \ErrorException('Expecting a numeric class_id, instead got: ' . $class_id);
        }

        if ($grade_range) {
            if ($this->validateGradeRange($grade_range)) {
                $params['grade_range'] = $grade_range;
            } else {
                throw new Error('Invalid grade range, valid formats are: 5, 5-6');
            }
        }

        if ($title) {
            $fields['title'] = $title;
        }

        // Do not issue a no-op to the server
        if (count($fields) === 0) {
            return;
        }

        return $this->put('/teachers/classes/' . $class_id, [], [], $fields);
    }

    public function deleteClass($class_id)
    {
        if (!$class_id) {
            throw new \ErrorException('Class id is required.');
        }

        return $this->delete('/teachers/classes/' . $class_id);
    }

    public function createStudent($student)
    {
        $required_fields = ['first_name', 'last_name', 'username', 'password'];
        $optional_fields = ['class_ids'];
        $student_keys = array_keys($student);
        $missing_fields = array_diff($student_keys, $required_fields);
        $invalid_fields = array_diff($student_keys, $required_fields, $optional_fields);

        if (count($invalid_fields) > 0) {
            throw new \InvalidArgumentException('Invalid fields(s) passed for student: ' . implode(', ', $invalid_fields) .
                '; valid fields are: ' . implode(', ', $required_fields)  . implode(', ', $optional_fields));
        }

        if (count($missing_fields) > 0) {
            throw new \InvalidArgumentException('Missing required fields(s) for student: ' . implode(', ', $missing_fields) .
                '; required fields: ' . implode(', ', $required_fields)  . ' optional fields: ' . implode(', ', $optional_fields));
        }

        return $this->post('/teachers/students', [], [], $student);
    }

    public function updateStudent($student_id, $student)
    {
        if (!is_numeric($student_id)) {
            throw new \ErrorException('Expecting a numeric student_id, instead got: ' . $student_id);
        }

        $required_fields = ['first_name', 'last_name', 'username', 'password'];
        $optional_fields = ['class_ids'];
        $student_keys = array_keys($student);
        $invalid_fields = array_diff($student_keys, $required_fields, $optional_fields);

        if (count($invalid_fields) > 0) {
            throw new \InvalidArgumentException('Invalid fields(s) passed for student: ' . implode(', ', $invalid_fields) .
                '; valid fields are: ' . implode(', ', $required_fields)  . implode(', ', $optional_fields));
        }

        return $this->put('/teachers/students/' . $student_id, [], [], $student);
    }

    public function deleteStudent($student_id)
    {
        if (!is_numeric($student_id)) {
            throw new \ErrorException('Expecting a numeric student_id, instead got: ' . $student_id);
        }

        return $this->delete('/teachers/students/' . $student_id);
    }

    public function addStudentsToClass($student_ids, $class_id)
    {
        $fields = [];

        if (is_array($student_ids)) {
            $fields['student_ids'] = $student_ids;
        } else if (is_numeric($student_ids)) {
            $fields['student_ids'] = [$student_ids];
        } else {
            throw new \InvalidArgumentException('student_ids should be an array of numbers or a number');
        }

        if (!is_numeric($class_id)) {
            throw new \ErrorException('Expecting a numeric class_id, instead got: ' . $class_id);
        }

        return $this->post("/teachers/classes/$class_id/add_students", [], [], $fields);
    }

    public function addStudentToClass($student_id, $class_id) {
        return $this->addStudentsToClass([$student_id], $class_id);
    }

    public function removeStudentsFromClass($student_ids, $class_id)
    {
        $fields = [];

        if (is_array($student_ids)) {
            $fields['student_ids'] = $student_ids;
        } else if (is_numeric($student_ids)) {
            $fields['student_ids'] = [$student_ids];
        } else {
            throw new \InvalidArgumentException('student_ids should be an array of numbers or a number');
        }

        if (!is_numeric($class_id)) {
            throw new \ErrorException('Expecting a numeric class_id, instead got: ' . $class_id);
        }

        return $this->post("/teachers/classes/$class_id/remove_students", [], [], $fields);
    }

    public function removeStudentFromClass($student_id, $class_id) {
        return $this->removeStudentsFromClass([$student_id], $class_id);
    }
}