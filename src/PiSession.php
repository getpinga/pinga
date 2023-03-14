<?php

namespace App;

use Pinga\Cache\Cache;

class PiSession {
    
    private $cache;
    private $sessionId;
    private $sessionData;
    
    public function __construct() {
        $this->cache = new Cache(new \Pinga\Cache\Adapter\Filesystem('/cache'));
        $this->sessionId = null;
        $this->sessionData = [];
    }
    
    public function start() {
        if ($this->sessionId !== null) {
            return;
        }
        $this->sessionId = $this->generateSessionId();
        $sessionData = $this->cache->load($this->sessionId,'10');
        if ($sessionData !== null) {
            $this->sessionData = $sessionData;
        }
        register_shutdown_function([$this, 'save']);
    }
    
    public function set($key, $value) {
        $this->sessionData[$key] = $value;
    }
    
    public function get($key, $default = null) {
        return isset($this->sessionData[$key]) ? $this->sessionData[$key] : $default;
    }
    
    public function save() {
        if ($this->sessionId !== null) {
            $this->cache->save($this->sessionId, $this->sessionData);
        }
    }
    
    private function generateSessionId() {
        return bin2hex(random_bytes(16));
    }
}
