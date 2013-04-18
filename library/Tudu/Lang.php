<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Lang
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Lang.php 16 2010-07-13 10:00:58Z gxx $
 */

/**
 * @category   Tudu
 * @package    Tudu_Lang
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Lang
{
	/**
	 * 当前语言
	 * 
	 * @var string
	 */
    private $_language = 'zh_CN';
    
    private $_packs;
    
    private $_commonPack = 'common';
    
	/**
     * Singleton instance
     *
     * @var Lang
     */
    protected static $_instance = null;
    
    /**
     * Returns an instance of Lang
     *
     * Singleton pattern implementation
     *
     * @return Lang Provides a fluent interface
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
    
    public function load($packs, $language = null)
    {
        if (is_string($packs)) {
            $packs = array($packs);
        }
        array_unshift($packs, $this->_commonPack);
        $langs = array();
        
        foreach ($packs as $pack) {
            $langs = array_merge($langs, $this->getPack($pack, $language));
        }
        
        return $langs;
    }

    public function getPack($pack, $language)
    {
        if (null === $language) {
            $language = $this->_language;
        }
        if (!isset($this->_packs[$language][$pack])) {
            $this->_packs[$language][$pack] = $this->_getPack($pack, $language);
        }
        return $this->_packs[$language][$pack];
    }
    
    private function _getPack($pack, $language)
    {
        $file = LANG_PATH . '/' . $language . '/' . $pack . '.inc';
        $lang = require $file;
        if (!is_array($lang)) {
            return array();
        }
        return $lang;
    }
    
    public function setLanguage($language)
    {
        $this->_language = $language;
    }
}