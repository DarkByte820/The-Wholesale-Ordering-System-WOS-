<?php
/**
 * Application Logger
 */

class Logger {
    private static $log_file = __DIR__ . '/../logs/application.log';
    
    public static function log($level, $message, $data = []) {
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[$timestamp] [$level] $message";
        
        if (!empty($data)) {
            $log_message .= " | Data: " . json_encode($data);
        }
        
        $log_message .= PHP_EOL;
        error_log($log_message, 3, self::$log_file);
    }
    
    public static function info($message, $data = []) {
        self::log('INFO', $message, $data);
    }
    
    public static function error($message, $data = []) {
        self::log('ERROR', $message, $data);
    }
    
    public static function warning($message, $data = []) {
        self::log('WARNING', $message, $data);
    }
    
    public static function debug($message, $data = []) {
        self::log('DEBUG', $message, $data);
    }
}

?>