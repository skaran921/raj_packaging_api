<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['jwt_key'] = 'SHUBHDHIMAN_RAJ';
$config['jwt_algorithm'] = 'HS256';
$config['jwt_token_timeout'] = 3600; // 1 hour
$config['jwt_access_token_timeout'] = 900; // 15 minutes
$config['jwt_refresh_token_timeout'] = 604800; // 7 days