<?php

namespace Kapi\Log\Logger;

use Psr\Log\AbstractLogger;

class File extends AbstractLogger
{
    public function __construct()
    {
        
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = [])
    {
        if (!(empty($this->_config['levels']) || in_array($level, $this->_config['levels']))) return;

        $message = $this->format($message, $context);
        $output = date('Y-m-d H:i:s') . ' ' . ucfirst($level) . ': ' . $message . "\n";
        $filename = $this->getFilename($level);
        if ($this->_size) {
            $this->rotateFile($filename);
        }

        $pathname = $this->_path . DS . $filename;

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
        return $this->_file ? $this->_file : in_array($level, $errorTypes) ? 'error.log' : in_array($level, $debugTypes) ? 'debug.log' : $level . '.log';
    }

    private function rotateFile($filename)
    {
        $file_path = $this->_path . $filename;
        clearstatcache(true, $file_path);

        if (!file_exists($file_path) || filesize($file_path) < $this->_size) {
            return null;
        }

        $rotate = $this->_config['rotate'];
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