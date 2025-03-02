<?php

require_once __DIR__ . '/classes/Filestorage.php';

$relativePath = $_GET['path'];

$fs = Filestorage::get();

$file = $fs->getFileByHash(sha1($relativePath));
if($file == null) {
    header('HTTP/1.0 404 not found');
    exit();
}

$mimetype = $file['mimetype'];
$filesize = $file['filesize'];

if ($mimetype === 'text/plain') {
    header('Content-Type: text/plain; charset=utf-8');
} else {
    header('Content-Type: '.$mimetype);
}

header('Content-Length: ' . $filesize);

$fs->readFile($file);