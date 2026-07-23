<?php

function nosql_path($collection) {
    $dir = APP_ROOT . '/data/nosql';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    return $dir . '/' . preg_replace('/[^a-z0-9_]/i', '', $collection) . '.json';
}

function nosql_all($collection) {
    $file = nosql_path($collection);
    if (!file_exists($file)) {
        return [];
    }
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function nosql_save($collection, $docs) {
    file_put_contents(nosql_path($collection), json_encode(array_values($docs), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function nosql_insert($collection, $doc) {
    $docs = nosql_all($collection);
    if (!isset($doc['_id'])) {
        $doc['_id'] = uniqid('doc_', true);
    }
    $doc['created_at'] = date('c');
    $docs[] = $doc;
    nosql_save($collection, $docs);
    return $doc;
}

function nosql_log_commande($menuId, $menuTitre, $total, $nbPersonnes) {
    nosql_insert('commandes_stats', [
        'menu_id' => (int)$menuId,
        'menu_titre' => $menuTitre,
        'total' => (float)$total,
        'nb_personnes' => (int)$nbPersonnes,
        'date' => date('Y-m-d'),
    ]);
}
