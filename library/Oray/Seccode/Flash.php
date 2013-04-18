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
 * @subpackage Flash
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Seccode_Flash
{
    /**
     * 显示验证码
     *
     * @param string $code
     * @return void
     */
    public function display($code) {
        $this->_code = $code;
        $this->_flash();
    }

    private function _flash() {
        $spacing = 5;
        $codewidth = ($this->width - $spacing * 5) / 4;
        $strforswdaction = '';
        for($i = 0; $i <= 3; $i++) {
            $strforswdaction .= $this->swfcode($codewidth, $spacing, $this->_code[$i], $i+1);
        }

        ming_setScale(20.00000000);
        ming_useswfversion(6);
        $movie = new SWFMovie();
        $movie->setDimension($this->width, $this->height);
        $movie->setBackground(255, 255, 255);
        $movie->setRate(31);

        $fontcolor = '0x'.(sprintf('%02s', dechex (mt_rand(0, 255)))).(sprintf('%02s', dechex (mt_rand(0, 128)))).(sprintf('%02s', dechex (mt_rand(0, 255))));
        $strAction = "
		_root.createEmptyMovieClip ( 'triangle', 1 );
		with ( _root.triangle ) {
		lineStyle( 3, $fontcolor, 100 );
		$strforswdaction
		}
		";
        $movie->add(new SWFAction( str_replace("\r", "", $strAction) ));
        header('Content-type: application/x-shockwave-flash');
        $movie->output();
    }

    function swfcode($width, $d, $code, $order) {
        $str = '';
        $height = $this->height - $d * 2;
        $x_0 = ($order * ($width + $d) - $width);
        $x_1 = $x_0 + $width / 2;
        $x_2 = $x_0 + $width;
        $y_0 = $d;
        $y_2 = $y_0 + $height;
        $y_1 = $y_2 / 2;
        $y_0_5 = $y_2 / 4;
        $y_1_5 = $y_1 + $y_0_5;
        switch($code) {
            case 'B':$str .= 'moveTo('.$x_1.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_1.', '.$y_2.');lineTo('.$x_2.', '.$y_1_5.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_0_5.');lineTo('.$x_1.', '.$y_0.');moveTo('.$x_0.', '.$y_1.');lineTo('.$x_1.', '.$y_1.');';break;
            case 'C':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_2.', '.$y_2.');';break;
            case 'E':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_2.', '.$y_2.');moveTo('.$x_0.', '.$y_1.');lineTo('.$x_1.', '.$y_1.');';break;
            case 'F':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');moveTo('.$x_0.', '.$y_1.');lineTo('.$x_1.', '.$y_1.');';break;
            case 'G':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_2.', '.$y_1.');lineTo('.$x_1.', '.$y_1.');';break;
            case 'H':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');moveTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');moveTo('.$x_0.', '.$y_1.');lineTo('.$x_2.', '.$y_1.');';break;
            case 'J':$str .= 'moveTo('.$x_1.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_0.', '.$y_1_5.');';break;
            case 'K':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_0.', '.$y_1.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');moveTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_2.');';break;
            case 'M':$str .= 'moveTo('.$x_0.', '.$y_2.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');';break;
            case 'P':$str .= 'moveTo('.$x_0.', '.$y_1.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_0_5.');lineTo('.$x_1.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');';break;
            case 'Q':$str .= 'moveTo('.$x_2.', '.$y_2.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_1.', '.$y_1.');';break;
            case 'R':$str .= 'moveTo('.$x_0.', '.$y_1.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_0_5.');lineTo('.$x_1.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');moveTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_2.');';break;
            case 'T':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');moveTo('.$x_1.', '.$y_0.');lineTo('.$x_1.', '.$y_2.');';break;
            case 'V':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_1.', '.$y_2.');lineTo('.$x_2.', '.$y_0.');';break;
            case 'W':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_2.', '.$y_0.');';break;
            case 'X':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');moveTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');';break;
            case 'Y':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_1.', '.$y_1.');lineTo('.$x_2.', '.$y_0.');moveTo('.$x_1.', '.$y_1.');lineTo('.$x_1.', '.$y_2.');';break;
            case '2':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_1.');lineTo('.$x_0.', '.$y_1.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_2.', '.$y_2.');';break;
            case '3':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_0.', '.$y_2.');moveTo('.$x_0.', '.$y_1.');lineTo('.$x_2.', '.$y_1.');';break;
            case '4':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');moveTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_1.');lineTo('.$x_2.', '.$y_1.');';break;
            case '6':$str .= 'moveTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_2.', '.$y_1.');lineTo('.$x_0.', '.$y_1.');';break;
            case '7':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');';break;
            case '8':$str .= 'moveTo('.$x_0.', '.$y_0.');lineTo('.$x_0.', '.$y_2.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_0.', '.$y_0.');moveTo('.$x_0.', '.$y_1.');lineTo('.$x_2.', '.$y_1.');';break;
            case '9':$str .= 'moveTo('.$x_2.', '.$y_1.');lineTo('.$x_0.', '.$y_1.');lineTo('.$x_0.', '.$y_0.');lineTo('.$x_2.', '.$y_0.');lineTo('.$x_2.', '.$y_2.');lineTo('.$x_0.', '.$y_2.');';break;
        }
        return $str;
    }
}
