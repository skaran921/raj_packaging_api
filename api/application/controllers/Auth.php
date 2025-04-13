<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        header('Content-Type: application/json');

        $this->load->database(); // ✅ This line fixes the error
        $this->load->model('User_model');
        $this->config->load('jwt');
    }


    // ✅ Register
    public function register()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['name'], $input['email'], $input['password'], $input['user_type'])) {
            echo json_encode(['status' => false, 'message' => 'All fields are required (name, email, password, user_type)']);
            return;
        }


        $existing = $this->User_model->getUserByEmail($input['email']);
        if ($existing) {
            echo json_encode(['status' => false, 'message' => 'Email already exists']);
            return;
        }

        $data = [
            'USER_NAME'     => $input['name'],
            'USER_EMAIL'    => $input['email'],
            'USER_PASS'     => password_hash($input['password'], PASSWORD_DEFAULT),
            'USER_TYPE'     => strtoupper($input['user_type']), // convert to uppercase if needed
            'USER_STATUS'   => 1,
            'USER_CREATE_AT' => date('Y-m-d H:i:s'),
            'USER_CREATE_BY' => 'api'
        ];

        $userId = $this->User_model->createUser($data);

        echo json_encode(['status' => true, 'message' => 'User registered', 'user_id' => $userId]);
    }

    // ✅ Login
    public function login()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!isset($input['email']) || !isset($input['password'])) {
            echo json_encode(['status' => false, 'message' => 'Email and password required']);
            return;
        }

        $user = $this->User_model->getUserByEmail($input['email']);

        if (!$user || !password_verify($input['password'], $user['USER_PASS'])) {
            echo json_encode(['status' => false, 'message' => 'Invalid credentials']);
            return;
        }

        $payload = [
            'iss' => base_url(),
            'iat' => time(),
            'exp' => time() + $this->config->item('jwt_token_timeout'),
            'uid' => $user['USER_ID'],
            'email' => $user['USER_EMAIL'],
            'type' => $user['USER_TYPE']
        ];

        $token = JWT::encode($payload, $this->config->item('jwt_key'), $this->config->item('jwt_algorithm'));

        echo json_encode([
            'status' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'user_id' => $user['USER_ID'],
                'name' => $user['USER_NAME'],
                'email' => $user['USER_EMAIL'],
                'type' => $user['USER_TYPE']
            ]
        ]);
    }

    // ✅ Logout
    public function logout()
    {
        echo json_encode(['status' => true, 'message' => 'Client should delete the token (JWT is stateless)']);
    }

    // ✅ Protected Profile Endpoint
    public function profile()
    {
        $auth = validate_jwt_token();

        if (!$auth['status']) {
            echo json_encode(['status' => false, 'message' => $auth['message']]);
            return;
        }

        $user = $auth['data'];
        echo json_encode([
            'status' => true,
            'message' => 'Authenticated user',
            'user' => [
                'id' => $user->uid,
                'email' => $user->email,
                'type' => $user->type
            ]
        ]);
    }
}
