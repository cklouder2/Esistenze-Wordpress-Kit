<?php
/**
 * Singleton Trait
 * Common singleton pattern implementation for all modules
 */

if (!defined('ABSPATH')) {
    exit;
}

trait EsistenzeSingleton {
    
    /**
     * Instance storage
     * @var self|null
     */
    private static $instance = null;
    
    /**
     * Get singleton instance
     * @return static
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new static();
        }
        return self::$instance;
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup() {}
} 