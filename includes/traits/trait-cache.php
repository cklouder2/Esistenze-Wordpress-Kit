<?php
/**
 * Cache Trait
 * Common caching functionality for performance optimization
 */

if (!defined('ABSPATH')) {
    exit;
}

trait EsistenzeCache {
    
    /**
     * Cache group prefix
     * @var string
     */
    private string $cacheGroup = 'esistenze_wp_kit';
    
    /**
     * Default cache expiration (12 hours)
     * @var int
     */
    private int $defaultExpiration = 43200;
    
    /**
     * Get cached data
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function getCache(string $key, $default = null) {
        $cached = wp_cache_get($key, $this->cacheGroup);
        return $cached !== false ? $cached : $default;
    }
    
    /**
     * Set cache data
     * @param string $key
     * @param mixed $data
     * @param int $expiration
     * @return bool
     */
    protected function setCache(string $key, $data, int $expiration = null): bool {
        $expiration = $expiration ?? $this->defaultExpiration;
        return wp_cache_set($key, $data, $this->cacheGroup, $expiration);
    }
    
    /**
     * Delete cache data
     * @param string $key
     * @return bool
     */
    protected function deleteCache(string $key): bool {
        return wp_cache_delete($key, $this->cacheGroup);
    }
    
    /**
     * Flush all cache for this group
     * @return bool
     */
    protected function flushCache(): bool {
        return wp_cache_flush();
    }
    
    /**
     * Get or set cache with callback
     * @param string $key
     * @param callable $callback
     * @param int $expiration
     * @return mixed
     */
    protected function remember(string $key, callable $callback, int $expiration = null) {
        $cached = $this->getCache($key);
        
        if ($cached !== null) {
            return $cached;
        }
        
        $data = $callback();
        $this->setCache($key, $data, $expiration);
        
        return $data;
    }
    
    /**
     * Generate cache key from array
     * @param array $params
     * @return string
     */
    protected function generateCacheKey(array $params): string {
        return md5(serialize($params));
    }
    
    /**
     * Clear cache on settings update
     * @param string $option_name
     */
    protected function clearCacheOnSettingsUpdate(string $option_name): void {
        add_action("update_option_{$option_name}", function() {
            $this->flushCache();
        });
    }
} 