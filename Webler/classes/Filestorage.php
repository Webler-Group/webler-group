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
            $filepath = $filepath . '/';
        }
        return sha1($filepath . $filename);
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

    public function createDraftfile($filename, $pathname) {
        $dirname = $this->generateRandomDirName();
        $filerecord = [
            "filepath" => "/draft/${dirname}",
            "filename" => $filename
        ];
        $filerecord['timecreated'] = date('Y-m-d H:i:s', time());

        list($filerecord['contenthash'], $filerecord['filesize'], $newfile) = $this->filesystem->addFileFromPath($pathname, true);

        $filerecord['mimetype'] = $this->filesystem->getMimeTypeFromHash($filerecord['contenthash']);
        $filerecord['pathnamehash'] = $this->getPathnameHash($filerecord['filepath'], $filerecord['filename']);

        return $this->createFile($filerecord);
    }

    public function createFileFromPath($filerecord, $pathname)
    {
        $filerecord['timecreated'] = date('Y-m-d H:i:s', time());

        list($filerecord['contenthash'], $filerecord['filesize'], $newfile) = $this->filesystem->addFileFromPath($pathname);

        $filerecord['mimetype'] = empty($filerecord['mimetype']) ? $this->filesystem->getMimeTypeFromHash($filerecord['contenthash']) : $filerecord['mimetype'];
        $filerecord['pathnamehash'] = $this->getPathnameHash($filerecord['filepath'], $filerecord['filename']);

        return $this->createFile($filerecord);
    }

    public function createFileFromString($filerecord, $content)
    {
        $filerecord['timecreated'] = date('Y-m-d H:i:s', time());

        list($filerecord['contenthash'], $filerecord['filesize'], $newfile) = $this->filesystem->addFileFromString($content);

        $filerecord['mimetype'] = empty($filerecord['mimetype']) ? $this->filesystem->getMimeTypeFromHash($filerecord['contenthash']) : $filerecord['mimetype'];
        $filerecord['pathnamehash'] = $this->getPathnameHash($filerecord['filepath'], $filerecord['filename']);

        return $this->createFile($filerecord);
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

    public function getFileById($fileid) {
        global $DB;

        try {
            return $DB->select_one('files', '*', ['id' => $fileid]);
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

    public function getImageInfo($filerecord) {
        if ($this->isImage($filerecord)) {
            list($width, $height, $type, $attr) = getimagesize($this->filesystem->getLocalPathFromHash($filerecord['contenthash']));
            return [
                'width' => $width,
                'height' => $height
            ];
        }
        return null;
    }

    public function convertImage($newFilerecord, $filerecord, $newWidth, $newHeight, $quality = null) {
        // Get the source image path
        $sourcePath = $this->filesystem->getLocalPathFromHash($filerecord['contenthash']);
        
        // Create the source image based on its MIME type
        switch ($filerecord['mimetype']) {
            case 'image/gif':
                $img = imagecreatefromgif($sourcePath);
                break;
    
            case 'image/jpeg':
                $img = imagecreatefromjpeg($sourcePath);
                break;
    
            case 'image/png':
                $img = imagecreatefrompng($sourcePath);
                break;
    
            default:
                throw new Exception('Unsupported mime type: ' . $filerecord['mimetype']);
        }
    
        // Resize the image
        $img = imagescale($img, $newWidth, $newHeight);
    
        ob_start();
        switch ($newFilerecord['mimetype']) {
            case 'image/gif':
                imagegif($img);
                break;
    
            case 'image/jpeg':
                if (is_null($quality)) {
                    imagejpeg($img);
                } else {
                    imagejpeg($img, NULL, $quality);
                }
                break;
    
            case 'image/png':
                $quality = (int)$quality;
    
                // Adjust quality for PNG (0-9, with 0 being no compression)
                $quality = $quality > 9 ? (int)(max(1.0, (float)$quality / 100.0) * 9.0) : $quality;
                imagepng($img, null, $quality, PNG_NO_FILTER);
                break;
    
            default:
                throw new Exception('Unsupported mime type');
        }
    
        $content = ob_get_contents();
        ob_end_clean();
        imagedestroy($img);
        
        // Store the new image as a file
        $this->createFileFromString($newFilerecord, $content);
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

        return $DB->insert_one('files', $record);
    }

    private function generateRandomDirName($length = 10) {
        $characters = '0123456789';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
}
