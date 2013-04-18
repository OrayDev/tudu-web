<?php
/**
 * Oray Framework
 *
 * LICENSE
 *
 *
 * @category   Oray
 * @package    Oray_Seccode
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id$
 */

/**
 * @see Oray_Seccode_Abstract
 */
require_once 'Oray/Seccode/Abstract.php';

/**
 * @category   Oray
 * @package    Oray_Seccode
 * @subpackage Voice
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Seccode_Voice extends Oray_Seccode_Abstract
{
    /**
     * 显示验证码
     *
     * @param string $code
     * @return void
     */
    public function display($code) {
        $this->_code = $code;
        $this->_audio();
    }

	function _audio() {
		header('Content-type: audio/mpeg');
		
		for($i = 0; $i < strlen($this->_code); $i++) {
			readfile($this->dataPath . '/sound/' . strtolower($this->_code{$i}) . '.mp3');
		}
	}
}
