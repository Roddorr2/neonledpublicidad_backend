<?php
$url = "http://127.0.0.1:8000/api/login";
$data = json_encode([
    "email" => "test_block@example.com",
    "password" => "wrongpassword",
    "turnstile_token" => "1x00000000000000000000AA"
]);

for ($i=1; $i<=7; $i++) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "Attempt $i: HTTP $httpcode - $response\n";
    curl_close($ch);
}
