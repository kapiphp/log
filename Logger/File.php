<?php

namespace Kapi\Log\Logger;

use Psr\Log\AbstractLogger;

class File extends AbstractLogger
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var mixed
     */
    private $path = 'log';

    /**
     * @var string
     */
    private $file;

    /**
     * @var mixed
     */
    private $size;

    public function __construct(array $config = [])
    {
        $this->setConfig($config);

        if (!empty($this->config['path'])) {
            $this->path = $this->config['path'];
        }
        if ($this->path && !is_dir($this->path)) {
            mkdir($this->path, 0775, true);
        }

        if (!empty($this->config['file'])) {
            $this->file = $this->config['file'];
            if (substr($this->file, -4) !== '.log') {
                $this->file .= '.log';
            }
        }

        if (!empty($this->config['size'])) {
            if (is_numeric($this->config['size'])) {
                $this->size = (int)$this->config['size'];
            } else {
                $this->size = $this->parseFileSize($this->config['size']);
            }
        }
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * Converts file size from human readable string to bytes
     *
     * @param string $size    Size in human readable string like '5MB', '5M', '500B', '50kb' etc.
     * @param mixed  $default Value to be returned when invalid size was used, for example 'Unknown type'
     * @return mixed Number of bytes as integer on success, `$default` on failure if not false
     * @throws \InvalidArgumentException On invalid Unit type.
     */
    private function parseFileSize($size, $default = false)
    {
        if (ctype_digit($size)) {
            return (int)$size;
        }
        $size = strtoupper($size);

        $l = -2;
        $i = array_search(substr($size, -2), ['KB', 'MB', 'GB', 'TB', 'PB']);
        if ($i === false) {
            $l = -1;
            $i = array_search(substr($size, -1), ['K', 'M', 'G', 'T', 'P']);
        }
        if ($i !== false) {
            $size = substr($size, 0, $l);

            return $size * pow(1024, $i + 1);
        }

        if (substr($size, -1) === 'B' && ctype_digit(substr($size, 0, -1))) {
            $size = substr($size, 0, -1);

            return (int)$size;
        }

        if ($default !== false) {
            return $default;
        }
        throw new \InvalidArgumentException('No unit type.');
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = [])
    {
        if (!(empty($this->config['levels']) || in_array($level, $this->config['levels']))) return;

        $message = $this->format($message, $context);
        $output = date('Y-m-d H:i:s') . ' ' . ucfirst($level) . ': ' . $message . "\n";
        $filename = $this->getFilename($level);
        if ($this->size) {
            $this->rotateFile($filename);
        }

        $pathname = $this->path . DIRECTORY_SEPARATOR . $filename;

        file_put_contents($pathname, $output, FILE_APPEND);
    }

    private function format($data, array $context = array())
    {
        $replace = array();

        foreach ($context as $placeholder => $value) {
            if (!is_array($value) && (!is_object($value) || method_exists($value, '__toString'))) {
                $replace['{' . $placeholder . '}'] = $value;
            }
        }

        if (is_object($data)) {
            if (method_exists($data, '__toString')) {
                $data = (string)$data;
            }
            if ($data instanceof \JsonSerializable) {
                $data = json_encode($data);
            }
        }

        if (!is_string($data)) {
            $data = print_r($data, true);
        }

        return strtr($data, $replace);
    }

    private function getFilename($level)
    {
        $debugTypes = ['notice', 'info', 'debug'];
        $errorTypes = ['emergency', 'alert', 'critical', 'error', 'warning'];
        return $this->file ? $this->file : in_array($level, $errorTypes) ? 'error.log' : in_array($level, $debugTypes) ? 'debug.log' : $level . '.log';
    }

    private function rotateFile($filename)
    {
        $file_path = $this->path . $filename;
        clearstatcache(true, $file_path);

        if (!file_exists($file_path) || filesize($file_path) < $this->size) {
            return null;
        }

        $rotate = $this->config['rotate'];
        if ($rotate === 0) {
            $result = unlink($file_path);
        } else {
            $result = rename($file_path, $file_path . '.' . time());
        }

        $files = glob($file_path . '.*');
        if ($files) {
            $filesToDelete = count($files) - $rotate;
            while ($filesToDelete > 0) {
                unlink(array_shift($files));
                $filesToDelete--;
            }
        }

        return $result;
    }
}