<?php
class HscicDownloadProcessor {

    const LAST_HASH_CODE_PREFIX = 'HSCIC_FILE_HASH_CODE_';

    private $logger;
    private $checkChanges;
    
    public function __construct(IHscicLogger $logger, $checkChanges) {
        $this->logger = $logger;
        $this->checkChanges = $checkChanges;
    }

    public function processFile(IHscicDownloader $downloader, $filename, IHscicUpdater $updater) {
        $previousHashCodeVarName = self::LAST_HASH_CODE_PREFIX . $downloader->getUrl();
        
        $previousHashCode = variable_get($previousHashCodeVarName, '');

        $this->logger->log("Downloading file {$downloader->getUrl()}");

        try {

            if (!$downloader->download()) {
                $this->logger->log("Download failed for {$downloader->getUrl()}");
                return false;
            }

            if ($previousHashCode === $downloader->zipHash() && $this->checkChanges) {
                $this->logger->log("{$downloader->getUrl()} has not changed");
                return false;
            }
            
            if (!$downloader->unzip()) {
                $this->logger->log("Unzipping failed for {$downloader->getUrl()}");
                return false;
            }

            $this->importFile("{$downloader->getExtractDirectoryName()}/{$filename}", $updater);

            $updater->complete();

            variable_set($previousHashCodeVarName, $downloader->zipHash());

        } catch (Exception $e) {
            $this->logger->log("---EXCEPTION---: Processing file '$filename'".PHP_EOL . $e->getMessage());
        } finally {
            $downloader->cleanup();
        }
    }

    private function importFile($filename, IHscicUpdater $updater) {
        watchdog(__METHOD__, "Importing file $filename");

        $csvFile = fopen($filename, "r");

        if ($csvFile === FALSE) {
            throw new Exception("Could not open file $filename", 1);
            return;
        }

        try {
            while (($data = fgetcsv($csvFile)) !== FALSE) {
                try {
                    $cnt++;
                    $updater->update($data);
                } catch (Exception $e) {
                    $this->logger->log("EXCEPTION: Processing file '$filename'".PHP_EOL . $e->getMessage().PHP_EOL."Data: " . print_r($data, true));
                    pp($e, 'EXCEPTION');
                }
            }
        } finally {
            fclose($csvFile);
        }

        watchdog(__METHOD__, "Import completed for file $filename");
    }
}
