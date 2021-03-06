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

    const BASE_URL = 'http://api-staging.opened.com';
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

        if (is_array($fields)) {
            $fields = json_encode($fields, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        if (strlen($fields) > 0) {
            $curl_args[] = "-d '" . $fields . "'";
        }

        $curl = 'curl ' . implode(' ', $curl_args) . ' ' . $url;

        $error  = "[HTTP $response_code] $method $url\n"
                . "HEADERS:\n" . json_encode($headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
                . "BODY:\n$fields\n"
                . "RESPONSE:\n" . $response . "\n"
                . "CURL:\n$curl\n";

        // The error will be truncated before the curl output is visible, sorry but not sorry
        $error_length = strlen($error);

        if ($error_length > ini_get('log_errors_max_len')) {
            ini_set('log_errors_max_len', $error_length);
            trigger_error("[OpenEd] log_errors_max_len increased to $error_length to output a large error; get ready!");
        }

        return $error;
    }

    public function getAccessToken($username = null, $password = null, $use_token = true)
    {
        $username = $username ?: $this->username;
        $password = $password ?: $this->password;

        if (!($username && $password)) {
            throw new \InvalidArgumentException('username and password are required parameters if they were not set in ApiClient');
        }

        list($error, $response, $response_code) = $this->request('POST', self::TOKEN_PATH, [
            'username' => $username,
            'password' => $password,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'password'
        ]);

        if ($error || $response_code != 200) {
            throw new \ErrorException("[OpenEd] Unable to get access token: $error");
        }

        if ($use_token) {
            $this->access_token = $response['access_token'];
        }

        return $response['access_token'];
    }

    public function useAccessToken($access_token)
    {
        $this->access_token = $access_token;
    }

    public function request($method, $path = '/', $params = [], $headers = [], $fields = [])
    {
        $error = null;

        $url = self::BASE_URL . $path;

        // If the path already contains query parameters, merge them with $params if passed
        $question_pos = strpos('?', $path);
        if ($question_pos && count($params) > 0) {
            $params = array_merge($params, parse_str(substr($path, $question_pos)));
            $path = substr($path, $question_pos);
        }

        if (count($params) > 0) {
            $url .= '?' . http_build_query($params);
        }

        $implicit_headers = [];

        if ($this->access_token) {
            $implicit_headers[] = 'Authorization: Bearer ' . $this->access_token;
        }

        $has_body = count($fields) > 0;

        if ($has_body) {
            $implicit_headers[] = 'Content-Type: application/json';
        }

        $headers = array_merge($headers, $implicit_headers);

        curl_setopt_array($this->curl, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POST => $method !== 'GET',
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $has_body ? json_encode($fields) : null
        ]);

        $response = curl_exec($this->curl);
        $response_code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        if ($response_code >= 400) {
            $error = 'HTTP $response_code';
            if ($this->trigger_errors) {
                if ($this->verbose) {
                    trigger_error($this->generateVerboseError($method, $url, $headers, $fields, $response, $response_code), E_USER_ERROR);
                } else {
                    trigger_error("[OpenEd] $method $url returned $error");
                }
            }
        }

        if ($curl_error = curl_errno($this->curl)) {
            $error = new \ErrorException(curl_error($this->curl));
        }

        $response_is_json = strpos(curl_getinfo($this->curl, CURLINFO_CONTENT_TYPE), 'json') !== false;

        // returns error, response, response_code
        return [
            $error,
            $response_is_json ? json_decode($response, true) : $response,
            $response_code
        ];
    }


    public function get($path = '/', $params = [], $headers = [], $fields = [], $success_code = 200)
    {
        list($error, $response, $response_code) = $this->request('GET', $path, $params, $headers, $fields);

        if ($error) {
            trigger_error("[OpenEd] GET $path: $error", E_USER_ERROR);
        }

        if ($response_code != $success_code) {
            trigger_error("[OpenEd] GET $path: expected HTTP $success_code got $response_code", E_USER_NOTICE);
        }

        return $response;
    }


    public function put($path = '/', $params = [], $headers = [], $fields = [], $success_code = 201)
    {
        list($error, $response, $response_code) = $this->request('PUT', $path, $params, $headers, $fields);

        if ($error) {
            trigger_error("[OpenEd] PUT $path: $error", E_USER_ERROR);
        }

        if ($response_code != $success_code) {
            trigger_error("[OpenEd] PUT $path: expected HTTP $success_code got $response_code", E_USER_NOTICE);
        }

        return $response;
    }


    public function post($path = '/', $params = [], $headers = [], $fields = [], $success_code = 200)
    {
        list($error, $response, $response_code) = $this->request('POST', $path, $params, $headers, $fields);

        if ($error) {
            trigger_error("[OpenEd] POST $path: $error", E_USER_ERROR);
        }

        if ($response_code != $success_code) {
            trigger_error("[OpenEd] POST $path: expected HTTP $success_code got $response_code", E_USER_NOTICE);
        }

        return $response;
    }


    public function delete($path = '/', $params = [], $headers = [], $fields = [], $success_code = 204)
    {
        list($error, $response, $response_code) = $this->request('DELETE', $path, $params, $headers, $fields);

        if ($error) {
            trigger_error("[OpenEd] DELETE $path: $error", E_USER_ERROR);
        }

        if ($response_code != $success_code) {
            trigger_error("[OpenEd] DELETE $path: expected HTTP $success_code got $response_code", E_USER_NOTICE);
        }

        return $response;
    }


    public function postRaw($body = '')
    {

        $url = self::BASE_URL . '/oauth/silent_login';

        $headers = ['Content-Type: text/plain', 'Content-Length: ' . strlen($body)];

        curl_setopt_array($this->curl, [
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POST => true,
            CURLOPT_URL => $url,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_HTTPHEADER => $headers
        ]);

        $response = curl_exec($this->curl);
        $response_code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        if ($curl_error = curl_errno($this->curl)) {
            trigger_error('[OpenEd] ' . curl_error($this->curl), E_USER_ERROR);
        }

        if ($response_code >= 400) {
            trigger_error($this->generateVerboseError('POST', $url, $headers, $body, $response, $response_code), E_USER_ERROR);
        }

        return json_decode($response, true);
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

        return $this->get('/teachers/classes', $params);
    }


    public function getClass($id) {
        return $this->get('/teachers/classes/' . $id);
    }


    private static function validateGradeRange($str)
    {
        return preg_match("/^\\d\\-?\\d?$/um", $str);
    }


    public function createClass($title, $grades_range = null)
    {
        $fields = [];

        if (is_string($title)) {
            $fields['title'] = $title;
        } else {
            throw new \ErrorException('Title is required.');
        }

        if ($grades_range) {
            if ($this->validateGradeRange($grades_range)) {
                $fields['grades_range'] = $grades_range;
            } else {
                throw new Error('Invalid grade range, valid formats are: 5, 5-6');
            }
        }

        return $this->post('/teachers/classes', [], [], ['class' => $fields], '201');
    }


    public function updateClass($class_id, $title, $grades_range = null)
    {
        $fields = [];

        if (!is_numeric($class_id)) {
            throw new \ErrorException('Expecting a numeric class_id, instead got: ' . $class_id);
        }

        if ($grades_range) {
            if ($this->validateGradeRange($grades_range)) {
                $params['grades_range'] = $grades_range;
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

        return $this->put('/teachers/classes/' . $class_id, [], [], ['class' => $fields]);
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
        $missing_fields = array_diff($required_fields, $student_keys);
        $invalid_fields = array_diff($student_keys, $required_fields, $optional_fields);

        if (count($invalid_fields) > 0) {
            throw new \InvalidArgumentException('Invalid fields(s) passed for student: ' . implode(', ', $invalid_fields) .
                '; valid fields are: ' . implode(', ', $required_fields)  . implode(', ', $optional_fields));
        }

        if (count($missing_fields) > 0) {
            throw new \InvalidArgumentException('Missing required fields(s) for student: ' . implode(', ', $missing_fields) .
                '; required fields: ' . implode(', ', $required_fields)  . ' optional fields: ' . implode(', ', $optional_fields));
        }

        return $this->post('/teachers/students', [], [], ['student' => $student], '201');
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

        return $this->put('/teachers/students/' . $student_id, [], [], ['student' => $student]);
    }


    public function getStudent($student_id)
    {
        if (!is_numeric($student_id)) {
            throw new \ErrorException('Expecting a numeric student_id, instead got: ' . $student_id);
        }

        return $this->get('/teachers/students/' . $student_id);
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
