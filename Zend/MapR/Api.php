<?php
/**
* @author Robin Verlangen
*/
class Zend_MapR_Api
{
    /**
    * Api endpoint
    * 
    * @var string
    */
    protected $_apiUrl;
    
    /**
    * Api username
    * 
    * @var string
    */
    protected $_apiUser;
    
    /**
    * Api password
    * 
    * @var string
    */
    protected $_apiPassword;
    
    /**
    * Instance
    * 
    * @var Zend_MapR_Api
    */
    private static $_instance;
    
    private function __construct()
    {
        // No direct instance creation: use getInstance();
    }
    
    private function __clone()
    {
        // No direct instance creation: use getInstance();
    }
    
    /**
    * Get instance
    * @return Zend_MapR_Api
    */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new Zend_MapR_Api();
        }
        return self::$_instance;
    }
    
    /**
    * Set API url 
    * 
    * @param mixed $url
    * @return Zend_Mapr_Api
    */
    public function setApiUrl($url)
    {
        $this->_apiUrl = $url;
        return $this;
    }
    
    /**
    * Set API authentication
    * 
    * @param mixed $user
    * @param mixed $password
    * @return Zend_Mapr_Api
    */
    public function setAuth($user, $password)
    {
        $this->_apiUser = $user;
        $this->_apiPassword = $password;
        return $this;
    }
    
    /**
    * Perform a get request and decode it contents
    * 
    * @param mixed $url
    * @param mixed $data
    * @param mixed $headers
    * @return array
    */
    public function apiGet($url, $data = null, $headers = null)
    {
        $client = new Zend_Http_Client($this->_apiUrl . $url);
        
        // Log
        Logs::log(Logs::DEBUG, 'Mapr', 'API call to "' . $url . '". Data = ' . print_r($data, true) . '. Headers = ' . print_r($headers, true));
        
        // Auth
        $client->setAuth($this->_apiUser, $this->_apiPassword);
        
        // Data
        if ($data !== null && is_array($data)) {
            foreach ($data as $k => $v) {
                $client->setParameterGet($k, $v);
            }
        }
        
        // Headers
        if ($headers !== null && is_array($headers)) {
            foreach ($headers as $k => $v) {
                $client->setHeaders($k, $v);
            }
        }
        
        // Get
        $response = $client->request('GET');
        $body = $response->getBody();
        
        // Decode
        $responseData = Zend_Json::decode($body);
        if (!isset($responseData['status']) || $responseData['status'] != 'OK') {
            Logs::log(Logs::ERR, 'Mapr', 'API call failed. ' . print_r($responseData, true));
            return false;
        }
        return $responseData;
    }
    
    /**
    * Create volume
    * 
    * @param string $linuxUser
    * @param int $quota In gigabytes!
    * @return mixed
    */
    public function createVolume($linuxUser, $quota)
    {
        $responseData = $this->apiGet('/volume/create',
            array(
                'user'            => $linuxUser . ':fc',
                'name'            => $linuxUser,
                'path'            => '/user/' . $linuxUser,
                'quota'           => ($quota + 1) . 'G',
                'advisoryquota'   => $quota . 'G'
            )
        );
        return $responseData;
    }
}