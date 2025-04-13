<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (!function_exists('validate_jwt_token')) {
    function validate_jwt_token()
    {
        $CI = &get_instance();
        $CI->config->load('jwt');

        $headers = $CI->input->request_headers();
        $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : (isset($headers['authorization']) ? $headers['authorization'] : null);

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return ['status' => false, 'message' => 'Token not found'];
        }

        $token = $matches[1];

        try {
            $decoded = JWT::decode($token, new Key($CI->config->item('jwt_key'), $CI->config->item('jwt_algorithm')));
            return ['status' => true, 'data' => $decoded];
        } catch (Exception $e) {
            return ['status' => false, 'message' => 'Invalid or expired token: ' . $e->getMessage()];
        }
    }
}


if (!function_exists('require_roles')) {
    function require_roles($allowedRoles = [])
    {
        $auth = validate_jwt_token();
        if (!$auth['status']) {
            return ['status' => false, 'message' => $auth['message']];
        }

        $user = $auth['data'];
        if (!in_array($user->type, $allowedRoles)) {
            return ['status' => false, 'message' => 'Access denied. Role not allowed.'];
        }

        return ['status' => true, 'data' => $user];
    }
}
