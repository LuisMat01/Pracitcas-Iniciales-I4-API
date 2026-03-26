<?php
define('API_BASE_URL', 'http://localhost:3000');

function api_request($endpoint, $method = "GET", $data = null, $token = false) {
    $url = API_BASE_URL . $endpoint;
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));

    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($data) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        return ['success' => false, 'error' => $err];
    }

    $decoded = json_decode($response, true);
    if ($decoded === null) {
        return ['success' => false, 'error' => 'Respuesta inválida del backend', 'raw' => $response];
    }

    return $decoded;
}