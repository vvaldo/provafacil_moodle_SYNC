<?php
require_once(__DIR__ . '/../../config.php'); // Configuração principal do Moodle
require_once($CFG->libdir . '/filelib.php'); // Biblioteca para usar a classe curl

function sync_students_to_provafacil($courseid) {
    global $DB;

    // Obter configurações do plugin
    $apiurl = get_config('local_provafacil_sync', 'apiurl');
    $apitoken = get_config('local_provafacil_sync', 'apitoken');

    if (!$apiurl || !$apitoken) {
        debugging('URL da API ou Token não configurados!', DEBUG_DEVELOPER);
        return false;
    }

    // Obter estudantes inscritos na disciplina
    $students = $DB->get_records_sql("
        SELECT u.id, u.username, u.firstname, u.lastname, u.email, u.idnumber
        FROM {user} u
        JOIN {user_enrolments} ue ON ue.userid = u.id
        JOIN {enrol} e ON e.id = ue.enrolid
        WHERE e.courseid = :courseid
    ", ['courseid' => $courseid]);

    if (!$students) {
        debugging("Nenhum estudante encontrado no curso com ID {$courseid}.", DEBUG_DEVELOPER);
        return false;
    }
    foreach ($students as $student) {
        $result = send_to_provafacil($student);

        if ($result['http_code'] === 200 || $result['http_code'] === 201) {
            // Sucesso
            echo "Estudante sincronizado com sucesso: " . $student->email . "\n";
            echo "Resposta da API: " . $result['response'] . "\n";
        } else {
            // Falha
            echo "Erro ao sincronizar estudante: " . $student->email . "\n";
            echo "HTTP Code: " . $result['http_code'] . "\n";
            echo "Resposta da API: " . $result['response'] . "\n";
        }
    }
    /**foreach ($students as $student) {
        $data = [
            'username' => $student->email,
            'password' => $student->idnumber,
            'name' => $student->firstname . ' ' . $student->lastname,
            'short_name' => $student->lastname,
            'legacy_key' => $student->idnumber,
            'email' => $student->email,
            'enrollment_code' => $student->idnumber,
            'notification' => true,
        ];

        $response = send_to_provafacil($apiurl, $apitoken, $data);
        if (!$response) {
            debugging("Erro ao enviar dados do usuário {$student->email}.", DEBUG_DEVELOPER);
        }
    } **/

    return true;
}

function send_to_provafacil($student) {
    global $CFG;

    require_once($CFG->libdir . '/filelib.php'); // Garante que a biblioteca 'curl' está carregada

    $url = 'https://unisced.provafacilnaweb.com.br/unisced/api/v2/tm/candidate/';
    $apitoken = 'b37fa95a203ad53482fe40b0a26093bd8749536a';

    // Dados do estudante
    $data = [
        'username' => $student->email,
        'password' => $student->idnumber,
        'name' => $student->firstname . ' ' . $student->lastname,
        'short_name' => $student->lastname,
        'legacy_key' => $student->idnumber,
        'email' => $student->email,
        'document_id' => $student->idnumber,
        'enrollment_code' => $student->idnumber,
        'notification' => true,
        'status' => '1',
    ];

    // Configurar JSON e cabeçalhos
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
//debugging("HTTP Code: {$http_code}", DEBUG_DEVELOPER);
//debugging("Response: {$response}", DEBUG_DEVELOPER);

// Retornar a resposta e o código HTTP
return [
    'http_code' => $http_code,
    'response' => $response,
];


    // Enviar a requisição
   // $curl = new curl();
   // $response = $curl->post($url, $json_data, ['CURLOPT_HTTPHEADER' => $headers]);
    //
    // Verificar a resposta
   // $http_code = $curl->get_info()['http_code'];
  //  if ($http_code !== 200 && $http_code !== 201) {
  //      debugging("Erro ao enviar para Prova Fácil. Código HTTP: {$http_code}, Resposta: {$response}", DEBUG_DEVELOPER);
  //      return false;
 //   }

 //   debugging("Estudante enviado com sucesso! Resposta: {$response}", DEBUG_DEVELOPER);
//    return true;
}
