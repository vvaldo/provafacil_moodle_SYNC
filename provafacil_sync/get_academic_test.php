<?php
$apitoken = 'b37fa95a203ad53482fe40b0a26093bd8749536a'; 

function api_get($url, $params = [], $apitoken) {
    $ch = curl_init();

    $headers = [
        "Authorization: token {$apitoken}",
        "Content-Type: application/json"
    ];

    if ($params) {
        // Certifique-se de que os parâmetros sejam um array
        $query_params = http_build_query($params);
        $url = $url . '?' . $query_params;
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Erro cURL: ' . curl_error($ch);
    }

    curl_close($ch);

    return json_decode($response, true);
}

// Função para obter a chave candidate
function get_candidate_key($user_idnumber, $apitoken) {
    $url = 'https://unisced.provafacilnaweb.com.br/unisced/api/v1/tm/rest/candidate/';
    $params = ['legacy_key' => $user_idnumber];
    $result = api_get($url, $params, $apitoken);

    if (!empty($result) && isset($result[0]['key'])) {
        return $result[0]['key'];
    }

    return null;
}

// Testando
$user_idnumber = '21170120'; 
$candidate_key = get_candidate_key($user_idnumber, $apitoken);

if ($candidate_key) {
    echo 'Chave Candidate: ' . $candidate_key;
} else {
    echo 'Erro ao obter a chave candidate.';
}

?>
