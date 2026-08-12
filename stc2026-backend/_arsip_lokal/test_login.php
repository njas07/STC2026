<?php

$url = "http://localhost/stc2026-backend/auth/login.php";

$data = [
    "email" => "test@stc2026.com",
    "password" => "123456"
];

$options = [
    "http" => [
        "header"  => "Content-Type: application/x-www-form-urlencoded\r\n",
        "method"  => "POST",
        "content" => http_build_query($data)
    ]
];

$context = stream_context_create($options);

$result = file_get_contents($url, false, $context);

echo $result;

?>