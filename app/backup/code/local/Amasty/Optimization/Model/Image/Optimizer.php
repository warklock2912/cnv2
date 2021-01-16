<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Optimization
 */


class Amasty_Optimization_Model_Image_Optimizer
{
    const TMP_PREFIX = '.amopttmp';

    protected $logger;
    protected $baseDir;
    protected $backupRoot;

    protected $totalSrc;
    protected $totalDest;
    protected $optimizedFiles;

    protected $commands = array();
    protected $skippedPaths = array(
        'media/catalog/product',
    );

    public function __construct(array $args = array())
    {
        $logger = isset($args['logger']) ? $args['logger'] : null;

        $this->logger = $logger ? $logger : new Zend_Log();
        $this->baseDir = Mage::getBaseDir();
        $this->backupRoot = Mage::getBaseDir('var') . DS . 'amoptimization_backup';

        foreach (array('png', 'jpeg', 'gif') as $extension) {
            $command = Mage::getStoreConfig("amoptimization/images/{$extension}_cmd");

            if (!$command) {
                throw new Exception("No command specified for image type '$extension'");
            }

            if (false === strpos($command, '%f')) {
                throw new Exception("File placeholder '%f' is not found in command for image type  '$extension'");
            }

            $this->commands[$extension] = $command;
        }
    }

    public function optimizeImages()
    {
        $this->logger->info('Starting image optimization...');

        $files = $this->getFileList();

        $this->optimizedFiles = 0;
        $this->totalDest = $this->totalSrc = 0;

        foreach ($files as $path) {
            $this->optimizeImage($path);
        }

        if ($this->optimizedFiles > 0) {
            $saved = $this->totalSrc - $this->totalDest;
            $this->logger->info("Total {$this->optimizedFiles} files optimized. $saved bytes saved.");
            $this->logger->info("All optimized files were copied to {$this->backupRoot}");
        }

        $this->logger->info('Optimization process complete.');
    }

    public function optimizeImage($path)
    {
        $dirName = dirname($path);

        if (!is_writable($dirName)) {
            throw new Exception("No write permissions for directory $dirName");
        }

        $relativePath = ltrim(substr($path, strlen($this->baseDir)), '/');

        $this->logger->info("Optimizing $relativePath...");

        switch (substr($path, -4)) {
            case '.png':
                $type = 'png';
                break;
            case '.jpg':
            case '.jpeg':
                $type = 'jpeg';
                break;
            case '.gif':
                $type = 'gif';
                break;
            default:
                $type = false;
        }

        if ($type) {
            $tmpFile = $path . self::TMP_PREFIX;
            copy($path, $tmpFile);

            $command = $this->commands[$type];
            $command = str_replace('%f', "'$tmpFile'", $command);

            $result = shell_exec($command);

            if (preg_match('#(error:|\[error\])#is', $result)) {
                $this->logger->err('An error occurred during attempt of image optimization');
                $this->logger->err($result);
            }
            else {
                $srcSize = filesize($path);
                $destSize = filesize($tmpFile);

                if ($srcSize <= $destSize) {
                    $this->logger->info(' No optimization required');
                    unlink($tmpFile);
                }
                else {
                    $backupFile = $this->backupRoot . DS . $relativePath;
                    $backupDir = dirname($backupFile);

                    if (!is_dir($backupDir)) {
                        mkdir($backupDir, 0777, true);
                    }

                    if (!is_writable($backupDir)) {
                        unlink($tmpFile);
                        throw new Exception("Couldn't write into $backupDir. Not enough permissions");
                    }

                    $perms = fileperms($path);
                    $owner = fileowner($path);

                    rename($path, $backupFile);
                    rename($tmpFile, $path);

                    chmod($path, $perms);
                    chown($path, $owner);

                    $optimizationPercent = ($srcSize - $destSize) / $srcSize * 100;
                    $this->logger->info(
                        sprintf(
                            " %d --> %d bytes (%.2f%%), optimized.",
                            $srcSize,
                            $destSize,
                            $optimizationPercent
                        )
                    );

                    $this->optimizedFiles++;
                    $this->totalSrc += $srcSize;
                    $this->totalDest += $destSize;
                }
            }
        }
    }

    public function getFileList()
    {
        $types = 'jpg|jpeg|png|gif';

        $baseDirs = array(
            Mage::getBaseDir('skin'),
            Mage::getBaseDir('media')
        );

        $result = array();

        foreach ($baseDirs as $baseDir) {
            $directoryIterator = new RecursiveDirectoryIterator(
                $baseDir, RecursiveDirectoryIterator::SKIP_DOTS
            );

            $skippedPaths = array();

            foreach ($this->skippedPaths as $path) {
                $skippedPaths []= $this->baseDir . DS . $path;
            }

            $iterator = new RecursiveIteratorIterator($directoryIterator);

            $regexIterator = new RegexIterator(
                $iterator, "/^.+\\.($types)$/", RecursiveRegexIterator::GET_MATCH
            );

            foreach ($regexIterator as $file) {
                foreach ($skippedPaths as $skippedPath) {
                    if (0 === strpos($file[0], $skippedPath)) {
                        continue 2;
                    }
                }

                $result []= $file[0];
            }
        }

        return $result;
    }
}
