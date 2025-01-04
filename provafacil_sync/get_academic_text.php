<?php
// Definir o token da API
$apitoken = 'b37fa95a203ad53482fe40b0a26093bd8749536a'; 
$urlacademic = 'https://unisced.provafacilnaweb.com.br/unisced/api/v1/str/rest/api/academic/';
// Função para fazer requisições GET
function api_get_academic($apitoken, $urlacademic, $params = []) {
    // Inicializa o cURL
    $ch = curl_init();

    // Define as opções do cURL
    $headers = [
        "Authorization: token {$apitoken}",
        "Content-Type: application/json"
    ];

    // Adiciona os parâmetros na URL se houver
    if ($params) {
        $urlacademic .= '?' . http_build_query($params);
    }

    // Define a URL e os cabeçalhos
    curl_setopt($ch, CURLOPT_URL, $urlacademic);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    // Executa a requisição
    $response = curl_exec($ch);

    // Verifica se ocorreu algum erro
    if (curl_errno($ch)) {
        echo 'Erro cURL: ' . curl_error($ch);
    }

    // Fecha a conexão curlacademic
    curl_close($ch);

    // Retorna o resultado como um array
    return json_decode($response, true);
}

// Função para obter a chave academic
function get_academic_key($course_idnumber, $apitoken) {
    $urlacademic = 'https://unisced.provafacilnaweb.com.br/unisced/api/v1/str/rest/api/academic/';
    $params = ['legacy_key' => $course_idnumber];
    $result = api_get_academic($apitoken, $urlacademic , $params);

    if (!empty($result) && isset($result[0]['key'])) {
        return $result[0]['key'];
    }

    return null;
}

// Testar com um legacy_key específico do curso
$course_idnumber = 'ISCED31-CJURCFE033_CP'; // Substitua pelo idnumber do seu curso
$academic_key = get_academic_key($course_idnumber, $apitoken);

if ($academic_key) {
    echo 'Chave Academic: ' . $academic_key;
} else {
    echo 'Erro ao obter a chave academic.';
}
?>
