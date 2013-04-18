<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Api
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Api.php 1998 2012-07-17 02:41:07Z web_op $
 */

/**
 * @category   Tudu
 * @package    Tudu_Api
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Api
{
	
	const CODE_SUCCESS = 100;
	
	/**
	 * 
	 * @var array
	 */
	private $_configs = array();
	
	/**
	 * Construct
	 * 
	 * @param array $config
	 */
	public function __construct($config = null)
	{
		if (is_array($config) && !empty($config)) {
			$this->setConfig($config);
		}
	}
	
	/**
	 * 
	 * @param array $config
	 */
	public function setConfig(array $config)
	{
		$this->_config = $config;
		
		return $this;
	}
	
	/**
	 * 
	 * @param string $ip
	 * @return string
	 */
	public function getLocation($ip = null)
	{
		if (empty($this->_config['url'])) {
			throw new Exception('invalid api url');
		}
		
		$params = array();
		
		if ($ip) {
			$params['ip'] = $ip;
		}
		
		if ($params) {
			$params = '?' . http_build_query($params);
		}
		
		$url = $this->_config['url'] . '/weather/local' . $params;
		
		$data = $this->_request($url);
		
		$xml = @simplexml_load_string($data);
		
		if (!$xml) {
			return null;
		}
		
		if ($xml->code == self::CODE_SUCCESS) {
			$item = $xml->xpath('//response//data//item[@name="code"]');
			return (string) $item[0];
		}
		
		return null;
	}
	
	/**
	 * 
	 * @param string $language
	 * @param string $location
	 * @return array
	 */
	public function getWeather($language, $location)
	{
        if (empty($this->_config['url'])) {
            throw new Exception('invalid api url');
        }
        
		if (!$location) {
			return null;
		}
		
		$params = array();
		$ret    = array();
		
		$params['weather'] = $location;
		
		if ($language) {
			$params['hl'] = $language;
		}
		
		$params = '?' . http_build_query($params);
		
		$url = $this->_config['url'] . '/weather' . $params;
		
		$data = $this->_request($url);
		
		$xml = simplexml_load_string($data);
		
		if (!$xml) {
			return null;
		}
		
		$ret['city'] = (string) $xml->weather->forecast_information->city['data'];
		$ret['postal_code'] = (string) $xml->weather->forecast_information->postal_code['data'];
		
		$current = $xml->weather->current_conditions[0];
		if ($current) {
			foreach ($current as $key => $item) {
				$ret['current'][$key] = (string) $item['data'];
			}
		}
		
		$ret['forecast'] = array();
		$forecast = $xml->weather->forecast_conditions;
			if ($forecast) {
			foreach ($forecast as $condition) {
				$arr = array();
				foreach ($condition as $key => $item) {
					$arr[$key] = (string) $item['data'];
				}
				
				$ret['forecast'][] = $arr;
			}
		}
		
		return $ret;
	}
	
	/**
	 * 
	 * @param string $url
	 * @param mixed $headers
	 * @param array $data
	 * @return string
	 */
	private function _request($url, $content = '', $headers = null)
	{
		return Oray_Function::httpRequest($url, $content, $headers);
	}
}