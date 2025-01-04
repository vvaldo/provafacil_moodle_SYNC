<?php

require_once(__DIR__ . '/../../../config.php');
require_login();

$studentid = required_param('studentid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);

require_sesskey(); // Verifica a sessão.

$context = context_course::instance($courseid);
require_capability('moodle/course:enrolreview', $context);

$student = $DB->get_record('user', ['id' => $studentid]);
$course = $DB->get_record('course', ['id' => $courseid]);

if (!$student || !$course) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

// API Prova Fácil.
$api_base_url = 'https://unisced.provafacilnaweb.com.br/unisced/api/v1';
$token = 'SEU_TOKEN_DE_AUTENTICACAO';

function call_api($url, $method = 'GET', $data = null, $token = '') {
    $curl = curl_init($url);

    $headers = [
        "Authorization: Bearer $token",
        "Content-Type: application/json",
    ];

    if ($method === 'POST') {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    }

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response, true);
}

// Obter keys de estudante e disciplina.
$candidate_url = "$api_base_url/tm/rest/candidate/?legacy_key={$student->idnumber}";
$candidate_response = call_api($candidate_url, 'GET', null, $token);
$candidate_key = $candidate_response['data']['key'] ?? null;

$academic_url = "$api_base_url/str/rest/api/academic/?legacy_key={$course->idnumber}";
$academic_response = call_api($academic_url, 'GET', null, $token);
$academic_key = $academic_response['data']['key'] ?? null;

if (!$candidate_key || !$academic_key) {
    echo json_encode(['success' => false, 'message' => 'Falha ao obter dados da API.']);
    exit;
}

// Enviar inscrição.
$enroll_url = "$api_base_url/str/rest/api/academicxcandidate/";
$enroll_data = ['candidate' => $candidate_key, 'academic' => $academic_key];
$enroll_response = call_api($enroll_url, 'POST', $enroll_data, $token);

if (isset($enroll_response['success']) && $enroll_response['success'] === true) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro na inscrição na API.']);
}
