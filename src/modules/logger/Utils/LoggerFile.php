<?php

namespace pff\modules\Utils;
use pff\modules\Abs\ALogger;
use pff\modules\Exception\LoggerException;

/**
 * Implementa log su file
 *
 * @author stonedz
 */
class LoggerFile extends ALogger {

    /**
     * Logs directory
     *
     * @var string
     */
    private $LOG_DIR;

    /**
     * File resource
     *
     * @var resource
     */
    private $_fp;


    /**
     * @param bool $debugActive True to activate debugmode
     * @throws \pff\modules\LoggerException
     */
    public function __construct($debugActive = false) {
        parent::__construct($debugActive);

        $this->_fp = null;
    }

    /**
     * Closes file resource when unsetting the logger
     */
    public function __destruct() {
        if ($this->_fp) {
            fclose($this->_fp);
        }
    }

    /**
     * Opens log file only if it's not already open
     *
     * @throws \pff\modules\LoggerException
     * @return null|resource
     */
    public function getLogFile() {

        if ($this->_fp === null) {
            $this->LOG_DIR = ROOT .DS. 'app' . DS .  'logs';
            $filename      = $this->LOG_DIR . DS . date("Y-m-d");
            $this->_fp     = fopen($filename, 'a');
            if ($this->_fp === false) {
                throw new LoggerException('Cannot open log file: ' . $filename);
            }
            chmod($filename, 0774);
        }

        return $this->_fp;

    }

    /**
     * Logs a message
     *
     * @param string $message Message to log
     * @param int $level Log level
     * @return bool
     * @throws \pff\modules\LoggerFileException
     */
    public function logMessage($message, $level = 0) {
        $this->getLogFile();
        if (!flock($this->_fp, LOCK_EX)) {
            throw new LoggerFileException('Can\'t obtain file lock for: ');
        }
        $text = '[' . date("H:i:s") . ']' . $this->_levelNames[$level] . " " . $message . "\n"; // Log message
        if (fwrite($this->_fp, $text)) {
            flock($this->_fp, LOCK_UN);
            return true;
        } else {
            flock($this->_fp, LOCK_UN);
            throw new LoggerFileException('Can\'t write to logfile!');
        }
    }

    /**
     * Returns file pointer
     *
     * @return resource
     */
    public function getFp() {
        return $this->_fp;
    }
}
