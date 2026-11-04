<?php
/**
 * Stockage document pour les stats admin.
 * - Si MONGO_URI + extension mongodb : vrai MongoDB (Docker)
 * - Sinon : fichiers JSON (dev USB rapide)
 */

function nosql_use_mongo() {
    return MONGO_URI !== '' && extension_loaded('mongodb');
}

function nosql_mongo_manager() {
    static $manager = null;
    if ($manager === null) {
        $manager = new MongoDB\Driver\Manager(MONGO_URI);
    }
    return $manager;
}

function nosql_ns($collection) {
    $col = preg_replace('/[^a-z0-9_]/i', '', $collection);
    return MONGO_DB . '.' . $col;
}

function nosql_path($collection) {
    $dir = APP_ROOT . '/data/nosql';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    return $dir . '/' . preg_replace('/[^a-z0-9_]/i', '', $collection) . '.json';
}

function nosql_backend_label() {
    return nosql_use_mongo() ? 'MongoDB' : 'JSON fichiers (fallback local)';
}

function nosql_all($collection) {
    if (nosql_use_mongo()) {
        $query = new MongoDB\Driver\Query([]);
        $cursor = nosql_mongo_manager()->executeQuery(nosql_ns($collection), $query);
        $out = [];
        foreach ($cursor as $doc) {
            $arr = (array)$doc;
            if (isset($arr['_id']) && is_object($arr['_id'])) {
                $arr['_id'] = (string)$arr['_id'];
            }
            $out[] = $arr;
        }
        return $out;
    }

    $file = nosql_path($collection);
    if (!file_exists($file)) {
        return [];
    }
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function nosql_save($collection, $docs) {
    // utilise seulement en mode JSON
    file_put_contents(nosql_path($collection), json_encode(array_values($docs), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function nosql_insert($collection, $doc) {
    if (!isset($doc['created_at'])) {
        $doc['created_at'] = date('c');
    }

    if (nosql_use_mongo()) {
        $bulk = new MongoDB\Driver\BulkWrite();
        $id = $bulk->insert($doc);
        nosql_mongo_manager()->executeBulkWrite(nosql_ns($collection), $bulk);
        $doc['_id'] = (string)$id;
        return $doc;
    }

    $docs = nosql_all($collection);
    if (!isset($doc['_id'])) {
        $doc['_id'] = uniqid('doc_', true);
    }
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
