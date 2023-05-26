<?php
namespace Eligmaltd\GoCryptoPay\Lib;

class GoCryptoLogger
{
    /* @var string $logFile*/
    private $logFile;

    /**
     * It creates a new instance of the class and sets the log file to the pay.log file in the logs
     * directory.
     */
    public function __construct()
    {
        $this->logFile = dirname(__FILE__) . '/logs/pay.log';
    }

    /**
     * It writes a line to a log file
     * 
     * @param s The string to be written to the log file.
     */
    public function writeLog($s) {
        $line = '*** ' . gmdate('r') . ' ' . $s . '\n';
        file_put_contents($this->logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
