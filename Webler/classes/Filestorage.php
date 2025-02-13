<?php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Filesystem.php';

class Filestorage
{
    private static $instance = null;

    private $filesystem;

    private function __construct()
    {
        $this->filesystem = new Filesystem();
    }

    public static function get(): Filestorage
    {
        if (self::$instance == null) {
            self::$instance = new Filestorage();
        }
        return self::$instance;
    }

    public static function getPathnameHash($filepath, $filename)
    {
        if (substr($filepath, 0, 1) != '/') {
            $filepath = '/' . $filepath;
        }
        if (substr($filepath, -1) != '/') {
            $filepath . '/';
        }
        return sha1($filepath . $filename);
    }

    public static function getMimeType($filePath)
    {
        if (file_exists($filePath)) {
            // Create a new finfo resource
            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            // Get the MIME type for the specified file
            $mimeType = finfo_file($finfo, $filePath);

            // Close the finfo resource
            finfo_close($finfo);

            return $mimeType;
        }
        return 'document/unknown';
    }

    private static function isRemovable($contenthash)
    {
        global $DB;

        if ($DB->count('files', ['contenthash' => $contenthash])) {
            return false;
        }

        return true;
    }

    public function getFilesystem()
    {
        return $this->filesystem;
    }

    public function createFileFromPath($filerecord, $pathname)
    {
        $filerecord['timecreated'] = date('Y-m-d H:i:s', time());

        list($filerecord['contenthash'], $filerecord['filesize'], $newfile) = $this->filesystem->addFileFromPath($pathname);

        $filerecord['mimetype'] = empty($filerecord['mimetype']) ? self::getMimeType($pathname) : $filerecord['mimetype'];
        $filerecord['pathnamehash'] = $this->getPathnameHash($filerecord['filepath'], $filerecord['filename']);

        $this->createFile($filerecord);
    }

    public function createFileFromString($filerecord, $content)
    {
        $filerecord['timecreated'] = date('Y-m-d H:i:s', time());

        list($filerecord['contenthash'], $filerecord['filesize'], $newfile) = $this->filesystem->addFileFromString($content);

        $filerecord['mimetype'] = empty($filerecord['mimetype']) ? self::getMimeType($filerecord['contenthash']) : $filerecord['mimetype'];
        $filerecord['pathnamehash'] = $this->getPathnameHash($filerecord['filepath'], $filerecord['filename']);

        $this->createFile($filerecord);
    }

    public function deleteFile($filerecord)
    {
        global $DB;

        $contenthash = $filerecord['contenthash'];

        $DB->delete('files', ['id' => $filerecord['id']]);

        if (self::isRemovable($contenthash)) {
            $this->filesystem->removeFile($contenthash);
        }
    }

    public function fileExistsByHash($pathnamehash)
    {
        global $DB;

        return $DB->count('files', ['pathnamehash' => $pathnamehash]) > 0;
    }

    public function fileExists($filepath, $filename)
    {
        $pathnamehash = self::getPathnameHash($filepath, $filename);
        return $this->fileExistsByHash($pathnamehash);
    }

    public function getFileByHash($pathnamehash)
    {
        global $DB;

        try {
            return $DB->select_one('files', '*', ['pathnamehash' => $pathnamehash]);
        } catch(PDOException $exception) {
            return null;
        }
    }

    public function getFile($filepath, $filename)
    {
        $pathnamehash = self::getPathnameHash($filepath, $filename);
        return $this->getFileByHash($pathnamehash);
    }

    public function isImage($filerecord)
    {
        if ($filerecord['filesize'] == 0) {
            return false;
        }
        if (!preg_match('|^image/|', $filerecord['mimetype'])) {
            return false;
        }
        return true;
    }

    public function readFile($filerecord)
    {
        $path = $this->filesystem->getLocalPathFromHash($filerecord['contenthash']);
        readfile($path);
    }

    public function getDirectoryFiles($filepath)
    {
        global $DB;

        $filerecords = $DB->select_many('files', '*', ['filepath' => $filepath]);

        $result = [];

        foreach($filerecords as $filerecord) {
            $result[$filerecord['pathnamehash']] = $filerecord;
        }

        return $result;
    }

    private function createFile($record)
    {
        global $DB;

        $DB->insert_one('files', $record);
    }
}
