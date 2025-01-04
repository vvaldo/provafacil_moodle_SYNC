<?php
require_once(__DIR__ . '/../../config.php'); // Configuração principal do Moodle
require_once($CFG->libdir . '/filelib.php'); // Biblioteca do Moodle para curl

$url = 'https://unisced.provafacilnaweb.com.br/unisced/api/v2/tm/candidate/';
$apitoken = '#apitoken';

$data = [
    'username' => 'ltamele1@isced.ac.mz',
    'password' => 'isced12345',
    'name' => 'Leia João Tamele',
    'document_id' => '8888888B',
    'legacy_key' => '888888888',
    'email' => 'ltamele1@isced.ac.mz',
    'enrollment_code' => '88888888',
    'notification' => true
];

$json_data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

$headers = [
    'Authorization: Token ' . $apitoken,
    'Content-Type: application/json',
];

// Iniciando o curl do Moodle
$curl = new curl();
$response = $curl->post($url, $json_data, ['CURLOPT_HTTPHEADER' => $headers]);

// Verificando a resposta
$http_code = $curl->get_info()['http_code'];
echo "HTTP Code: {$http_code}\n";
echo "Response: {$response}\n";
