<?php
include_once '../config/core.php';
$hdrs = getallheaders();
if (! isset($hdrs['api_key']) || ($hdrs['api_key'] != API_KEY)) {
    header('HTTP/1.1 401 Unauthorized');
    exit("Access Denied: Username and password required.");
}
?>