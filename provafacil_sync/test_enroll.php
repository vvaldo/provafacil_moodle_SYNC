<?php
require_once('../../config.php'); // Inclua a configuração do Moodle, se necessário.
require_once($CFG->libdir . '/filelib.php'); // Biblioteca para usar a classe curl
$apitoken = '#apitoken';

require_login();
$candidate_key = optional_param('candidate_key', null, PARAM_RAW);
$academic_key = optional_param('academic_key', null, PARAM_RAW);
$apitoken = optional_param('apitoken', null, PARAM_RAW);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['enrollments'])) {
    $headers = [
        'Authorization: Token ' . $apitoken,
        'Content-Type: application/json',
    ];
    $enrollments = array_map('json_decode', $_POST['enrollments']);
    foreach ($enrollments as $enrollment) {
        echo '<pre>';
        var_dump("Dados decodificados: " . print_r($enrollment, true));
        //print_r('estou aqui 1' .$enrollment.);
        echo '</pre>';
        
        //debugging("Dados enviados: " . $json_data, DEBUG_DEVELOPER);
        // Chamar a função de inscrição para cada estudante
        $result = local_provafacil_enroll_student($enrollment->candidate, $enrollment->academic);

        echo '<pre>';
        print_r($result);
        echo '</pre>';

       
    }

} else {
    echo 'Nenhum dado recebido.';
}





function local_provafacil_enroll_student($candidate_key, $academic_key) {
    $url = 'https://unisced.provafacilnaweb.com.br/unisced/api/v1/str/rest/api/academicxcandidate/';
    $apitoken = '#apitoken';
    $data = [
        'candidate' => $candidate_key,
        'academic' => $academic_key,
    ];

    $json_data = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $headers = [
        'Authorization: Token ' . $apitoken,
        'Content-Type: application/json',
    ];

// Enviar a requisição
$curl = new curl();
$response = $curl->post($url, $json_data, ['CURLOPT_HTTPHEADER' => $headers]);

// Verificar o código HTTP e capturar a resposta
$http_code = $curl->get_info()['http_code'];
// debugging("HTTP Code: {$http_code}", DEBUG_DEVELOPER);
// debugging("Response: {$response}", DEBUG_DEVELOPER);
    
   

    return [
        'http_code' => $http_code,
        'response' => $response,
    ];
}


