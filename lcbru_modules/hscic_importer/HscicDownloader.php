<?php

interface IHscicDownloader {
    public function getUrl();
    public function getExtractDirectoryName();
    public function download();
    public function zipHash();
    public function unzip();
    public function cleanup();
}

class HscicDownloader implements IHscicDownloader
{
    private $temporaryName;
    private $zipFilename;
    private $extractDirectoryName;
    private $url;

    public function __construct($url) {
        $this->temporaryName = tempnam(sys_get_temp_dir(), 'Hscic_');
        $this->zipFilename = "{$this->temporaryName}.zip";
        $this->extractDirectoryName = "{$this->temporaryName}_extracted";
        $this->url = $url;
    }

    public function getUrl() {
        return $this->url;
    }

    public function getExtractDirectoryName() {
        return $this->extractDirectoryName;
    }

    public function download() {
        try {
            $content = file_get_contents($this->url);
            file_put_contents($this->zipFilename, $content);
        } catch (Exception $e) {
            drupal_set_message($e->getMessage());
            return false;
        }

        return true;
    }

    public function zipHash() {
        return hash_file('sha256', $this->zipFilename);
    }

    public function unzip() {
        $zip = new ZipArchive;
        $res = $zip->open($this->zipFilename);

        if ($res === TRUE) {
          $zip->extractTo($this->extractDirectoryName);
          $zip->close();
          return TRUE;
        } else {
          return FALSE;
        }
    }

    public function cleanup() {
        unlink($this->temporaryName);
        unlink($this->zipFilename);
        $this->rrmdir($this->extractDirectoryName);
    }

    // Recursively removes unempty directories
    private function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                   if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
}
