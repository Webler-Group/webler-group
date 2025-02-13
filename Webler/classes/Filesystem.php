<?php

require_once __DIR__ . '/../../config.php';

class Filesystem
{
    private $filedir;

    public function __construct()
    {
        global $CFG;

        $this->filedir = $CFG->dataroot . '/filedir';

        if (!is_dir($this->filedir)) {
            mkdir($this->filedir, 0755, true);  // Create directories recursively
        }
    }

    public static function getHashFromString($content)
    {
        return sha1($content);
    }

    public static function getHashFromPath($filepath)
    {
        return sha1_file($filepath);
    }

    private static function checkFileExistsAndGetSize($hashfile) {
        if(!file_exists($hashfile)) {
            return null;
        }

        $hashsize = filesize($hashfile);
        return $hashsize;
    }

    public function removeFile($contenthash) {
        $hashfile = $this->getLocalPathFromHash($contenthash);

        unlink($hashfile);
    }

    public function addFileFromPath($pathname) {
        $contenthash = self::getHashFromPath($pathname);
        $filesize = filesize($pathname);
        
        $hashpath = $this->getFullDirFromHash($contenthash);
        $hashfile = $this->getLocalPathFromHash($contenthash);

        $hashsize = self::checkFileExistsAndGetSize($hashfile);
        if($hashsize != null) {
            return array($contenthash, $filesize, false);
        }

        if(!is_dir($hashpath)) {
            mkdir($hashpath, 0755, true);
        }

        copy($pathname, $hashfile);

        return array($contenthash, $filesize, true);
    }

    public function addFileFromString($content) {
        $contenthash = self::getHashFromString($content);
        $filesize = strlen($content);

        $hashpath = $this->getFullDirFromHash($contenthash);
        $hashfile = $this->getLocalPathFromHash($contenthash);

        $hashsize = self::checkFileExistsAndGetSize($hashfile);
        if($hashsize != null) {
            return array($contenthash, $filesize, false);
        }

        if(!is_dir($hashpath)) {
            mkdir($hashpath, 0755, true);
        }

        file_put_contents($hashfile, $content);

        return array($contenthash, $filesize, true);
    }

    public function getContentDirFromHash($contenthash) {
        $l1 = $contenthash[0] . $contenthash[1];
        $l2 = $contenthash[2] . $contenthash[3];
        return "$l1/$l2";
    }

    public function getFullDirFromHash($contenthash) {
        return $this->filedir . '/' . $this->getContentDirFromHash($contenthash);
    }

    public function getLocalPathFromHash($contenthash) {
        return $this->getFullDirFromHash($contenthash) . '/' . $contenthash;
    }
}