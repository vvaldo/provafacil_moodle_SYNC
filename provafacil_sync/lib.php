<?php

defined('MOODLE_INTERNAL') || die();

/**
 * Retorna a estrutura de categorias e cursos.
 *
 * @return array
 */
function local_provafacil_sync_get_course_structure() {
    global $DB;

    // Obter todas as categorias
    $categories = $DB->get_records('course_categories', null, 'path ASC');

    // Montar estrutura hierárquica
    $tree = [];
    foreach ($categories as $category) {
        $parent = $category->parent;
        if ($parent == 0) {
            $tree[$category->id] = [
                'name' => $category->name,
                'children' => [],
                'courses' => []
            ];
        } else {
            if (isset($tree[$parent])) {
                $tree[$parent]['children'][$category->id] = [
                    'name' => $category->name,
                    'courses' => []
                ];
            }
        }
    }

    // Adicionar cursos às categorias correspondentes
    $courses = $DB->get_records('course');
    foreach ($courses as $course) {
        if (isset($tree[$course->category])) {
            $tree[$course->category]['courses'][] = [
                'id' => $course->id,
                'name' => $course->fullname
            ];
        }
    }

    return $tree;
}

/**
 * Armazena os dados da API no banco de dados.
 *
 * @param stdClass $response
 */
function local_provafacil_sync_store_api_response($response) {
    global $DB;

    $record = new stdClass();
    $record->key = $response->key;
    $record->name = $response->name;
    $record->short_name = $response->short_name;
    $record->email = $response->email;
    $record->document_id = $response->document_id;
    $record->enrollment_code = $response->enrollment_code;
    $record->status = $response->status;

    $DB->insert_record('local_provafacil_sync', $record);
}
function save_sync_data_from_api($data) {
    global $DB;

    // Criar o objeto com os dados
    $record = new stdClass();
    $record->key = $data['key'];
    $record->last_update = $data['last_update'];
    $record->updated_by = $data['updated_by'];
    $record->legacy_key = $data['legacy_key'];
    $record->creation_date = $data['creation_date'];
    $record->name = $data['name'];
    $record->short_name = $data['short_name'];
    $record->document_id = $data['document_id'];
    $record->ref_code = $data['ref_code'];
    $record->enrollment_code = $data['enrollment_code'];
    $record->email = $data['email'];
    $record->client = $data['client'];
    $record->user = $data['user'];

    // Verificar se o registro já existe
    $existing_record = $DB->get_record('local_provafacil_sync', ['key' => $data['key']]);

    if ($existing_record) {
        // Atualiza o registro
        $record->id = $existing_record->id;
        $DB->update_record('local_provafacil_sync', $record);
    } else {
        // Insere um novo registro
        $DB->insert_record('local_provafacil_sync', $record);
    }
}
function local_provafacil_get_key($url, $legacy_key, $apitoken) {
    $curl = new curl();
    $response = $curl->get($url . '?legacy_key=' . urlencode($legacy_key), [
        'CURLOPT_HTTPHEADER' => [
            'Authorization: Token ' . $apitoken,
            'Content-Type: application/json',
        ]
    ]);

    // Decodifica e valida a resposta
    $data = json_decode($response, true);

    if (!$data) {
        error_log("Erro ao decodificar JSON da resposta: {$response}");
        return null;
    }

    if (isset($data['key'])) {
        return $data['key'];
    }

    error_log("A chave 'key' não foi encontrada na resposta: " . print_r($data, true));
    return null;
}
/**
 * Função para inscrever estudante na disciplina via API.
 */
function local_provafacil_enroll_student($candidate_key, $academic_key, $apitoken) {
    $url = 'https://unisced.provafacilnaweb.com.br/unisced/api/v1/str/rest/api/academicxcandidate/';
    $data = [
        'candidate' => $candidate_key,
        'academic' => $academic_key,
    ];
    $json_data = json_encode($data);
    $curl = new curl();
    $response = $curl->post($url, $json_data, [
        'CURLOPT_HTTPHEADER' => [
            'Authorization: Token ' . $apitoken,
            'Content-Type: application/json',
        ],
    ]);
    return json_decode($response, true);
}