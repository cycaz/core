<?php
class TMC_Model_Cookie
{
    const XML_PATH_COOKIE_DOMAIN    = 'global/cookie/cookie_domain';
    const XML_PATH_COOKIE_PATH      = 'global/cookie/cookie_path';
    const XML_PATH_COOKIE_LIFETIME  = 'global/cookie/cookie_lifetime';
    const XML_PATH_COOKIE_HTTPONLY  = 'global/cookie/cookie_httponly';

    protected $_lifetime;

    protected function _getRequest()
    {
        return TMC::getRequest();
    }

    protected function _getResponse()
    {
        return TMC::getResponse();
    }

    public function getDomain()
    {
        $domain = $this->getConfigDomain();
        if (empty($domain)) {
            $domain = $this->_getRequest()->getHttpHost();
        }
        return $domain;
    }

    public function getConfigDomain()
    {
        return (string)TMC::getConfig(self::XML_PATH_COOKIE_DOMAIN);
    }

    public function getPath()
    {
        $path = TMC::getConfig(self::XML_PATH_COOKIE_PATH);
        if (empty($path)) {
            $path = $this->_getRequest()->getBasePath();
        }
        return $path;
    }

    public function getLifetime()
    {
        if (!is_null($this->_lifetime)) {
            $lifetime = $this->_lifetime;
        } else {
            $lifetime = TMC::getConfig(self::XML_PATH_COOKIE_LIFETIME);
        }
        if (!is_numeric($lifetime)) {
            $lifetime = 3600;
        }
        return $lifetime;
    }

    public function setLifetime($lifetime)
    {
        $this->_lifetime = (int)$lifetime;
        return $this;
    }

    public function getHttponly()
    {
        $httponly = TMC::getConfig(self::XML_PATH_COOKIE_HTTPONLY);
        if (is_null($httponly)) {
            return null;
        }
        return (bool)$httponly;
    }
    public function isSecure()
    {
        if ($this->getStore()->isAdmin()) {
            return $this->_getRequest()->isSecure();
        }
        return false;
    }

    /**
     * Set cookie
     *
     * @param string $name The cookie name
     * @param string $value The cookie value
     * @param int $period Lifetime period
     * @param string $path
     * @param string $domain
     * @param int|bool $secure
     * @return Mage_Core_Model_Cookie
     */
    public function set($name, $value, $period = null, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        /**
         * Check headers sent
         */
        if (!$this->_getResponse()->canSendHeaders(false)) {
            return $this;
        }

        if ($period === true) {
            $period = 3600 * 24 * 365;
        } elseif (is_null($period)) {
            $period = $this->getLifetime();
        }

        if ($period == 0) {
            $expire = 0;
        }
        else {
            $expire = time() + $period;
        }
        if (is_null($path)) {
            $path = $this->getPath();
        }
        if (is_null($domain)) {
            $domain = $this->getDomain();
        }
        if (is_null($secure)) {
            $secure = $this->isSecure();
        }
        if (is_null($httponly)) {
            $httponly = $this->getHttponly();
        }

        setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);

        return $this;
    }

    /**
     * Postpone cookie expiration time if cookie value defined
     *
     * @param string $name The cookie name
     * @param int $period Lifetime period
     * @param string $path
     * @param string $domain
     * @param int|bool $secure
     * @return Mage_Core_Model_Cookie
     */
    public function renew($name, $period = null, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        if (($period === null) && !$this->getLifetime()) {
            return $this;
        }
        $value = $this->_getRequest()->getCookie($name, false);
        if ($value !== false) {
            $this->set($name, $value, $period, $path, $domain, $secure, $httponly);
        }
        return $this;
    }

    /**
     * Retrieve cookie or false if not exists
     *
     * @param string $neme The cookie name
     * @return mixed
     */
    public function get($name = null)
    {
        return $this->_getRequest()->getCookie($name, false);
    }

    /**
     * Delete cookie
     *
     * @param string $name
     * @param string $path
     * @param string $domain
     * @param int|bool $secure
     * @param int|bool $httponly
     * @return Mage_Core_Model_Cookie
     */
    public function delete($name, $path = null, $domain = null, $secure = null, $httponly = null)
    {
        /**
         * Check headers sent
         */
        if (!$this->_getResponse()->canSendHeaders(false)) {
            return $this;
        }

        if (is_null($path)) {
            $path = $this->getPath();
        }
        if (is_null($domain)) {
            $domain = $this->getDomain();
        }
        if (is_null($secure)) {
            $secure = $this->isSecure();
        }
        if (is_null($httponly)) {
            $httponly = $this->getHttponly();
        }

        setcookie($name, null, null, $path, $domain, $secure, $httponly);
        return $this;
    }
}
