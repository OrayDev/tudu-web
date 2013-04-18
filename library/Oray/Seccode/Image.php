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
 * @subpackage Image
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Oray_Seccode_Image extends Oray_Seccode_Abstract
{
    /**
     * 验证码长度
     *
     * @var int
     */
    private $_length = 4;

    /**
     * 是否纯英文字符
     *
     * @var boolean
     */
    private $_isEn;

    /**
     * 字条颜色
     *
     * @var array
     */
    private $_fontColor = array();

    /**
     * Resource of image
     *
     * @var image
     */
    private $_im;

    public $background = true;    // 随机图片背景
    public $adulterate = true;    // 随机背景掺杂
    public $ttf 	   = true;    // 随机 TTF 字体
    public $angle      = true;    // 随机倾斜度
    public $color      = true;    // 随机颜色
    public $size       = true;    // 随机大小
    public $shadow     = true;    // 文字阴影
    public $animator   = false;   // GIF 动画

    /**
     * 显示验证码
     *
     * @param string $code
     * @return void
     */
    public function display($code) {

        $this->_code = $code;
        $this->_isEn = (boolean) preg_match('/^[\x{01}-\x{ff}]+$/u', $code);

        $this->width  = ($this->width > 0 && $this->width <= Oray_Seccode::WIDTH_MAX) ? $this->width : Oray_Seccode::WIDTH_DEFAULT;
        $this->height = ($this->height > 0 && $this->height <= Oray_Seccode::HEIGHT_MAX) ? $this->height : Oray_Seccode::HEIGHT_DEFAULT;

        if (function_exists('imagecreate')
            && function_exists('imagecolorset')
            && function_exists('imagecopyresized')
            && function_exists('imagecolorallocate')
            && function_exists('imagechar')
            && function_exists('imagecolorsforindex')
            && function_exists('imageline')
            && function_exists('imagecreatefromstring')
            && (function_exists('imagegif')
                || function_exists('imagepng')
                || function_exists('imagejpeg'))) {
            $this->_image();
        } else {
            $this->_bitmap();
        }
    }

    /**
     * 图片验证码
     *
     */
    private function _image()
    {
        // 创建图像
        $this->_createBackground();

        // GIF动画
        if ($this->animator && function_exists('imagegif')) {

            $trueframe = mt_rand(1, 9);

            for($i = 0; $i <= 9; $i++) {

                // 新建一个真彩色图像
                $im = imagecreatetruecolor($this->width, $this->height);

                // 拷贝背景到新的图像
                imagecopy($im, $this->_im, 0, 0, 0, 0, $this->width, $this->height);

                // 随机背景掺杂
                if ($this->adulterate) {
                    $this->_adulterate($im);
                }

                $x[$i] = $y[$i] = 0;

                if ($i == $trueframe) {
                    if ($this->ttf && function_exists('imagettftext')) {
                        $this->_ttfFont($im);
                    } else {
                        $this->_gifFont($im);
                    }
                    $d[$i] = mt_rand(250, 400);
                } else {
                    $this->_randomFont($im);
                    $d[$i] = mt_rand(5, 15);
                }

                ob_start();
                imagegif($im);
                imagedestroy($im);
                $frame[$i] = ob_get_contents();
                ob_end_clean();
            }
            imagedestroy($this->_im);

            require_once 'Oray/Seccode/GifMerge.php';
            $anim = new Oray_Seccode_GifMerge($frame, 255, 255, 255, 0, $d, $x, $y, 'C_MEMORY');
            header('Content-type: image/gif');
            echo $anim->getAnimation();

        } else {

            // 随机背景掺杂
            if ($this->adulterate) {
                $this->_adulterate($this->_im);
            }

            if ($this->ttf && function_exists('imagettftext')) {
                $this->_ttfFont($this->_im);
            } else {
                $this->_gifFont($this->_im);
            }

            // 输出图片
            if (function_exists('imagepng')) {
                header('Content-type: image/png');
                imagepng($this->_im);
            } else {
                header('Content-type: image/jpeg');
                imagejpeg($this->_im, '', 75);
            }

            imagedestroy($this->_im);
        }
    }

    /**
     * 返回文件后缀
     *
     * @param string $filename
     * @return string
     */
    private function _fileExt($filename) {
        return trim(substr(strrchr($filename, '.'), 1, 10));
    }

    /**
     * 生成图片背景内容
     *
     * @return void
     */
    private function _createBackground() {

        // 新建一个真彩色图像
        $this->_im = imagecreatetruecolor($this->width, $this->height);

        // 为一幅图像分配颜色，第一次对 imagecolorallocate() 的调用会填充背景色
        $backgroundColor = imagecolorallocate($this->_im, 255, 255, 255);

        $backgrounds = $color = array();

        // 随机背景
        if ($this->background && function_exists('imagecreatefromjpeg')
            && function_exists('imagecolorat') && function_exists('imagecopymerge')
            && function_exists('imagesetpixel') && function_exists('imageSX')
            && function_exists('imageSY')) {
            if ($handle = opendir($this->dataPath . '/background/')) {
                while ($bgfile = readdir($handle)) {
                    if (preg_match('/\.jpg$/i', $bgfile)) {
                        $backgrounds[] = $this->dataPath . '/background/' . $bgfile;
                    }
                }
                closedir($handle);
            }

            if ($backgrounds) {

                // 从 URL 新建一图像
                $im = imagecreatefromjpeg($backgrounds[array_rand($backgrounds)]);

                // 取得某像素的颜色索引值
                $rgb = imagecolorat($im, 0, 0);

                // 取得某索引的颜色
                $color = imagecolorsforindex($im, $rgb);

                // 取得某像素的颜色索引值
                $rgb = imagecolorat($im, 1, 0);

                // 画一个单一像素
                imagesetpixel($im, 0, 0, $rgb);

                // ??
                $color[0] = $color['red'];
                $color[1] = $color['green'];
                $color[2] = $color['blue'];

                // 拷贝并合并图像的一部分
                imagecopymerge($this->_im,
                               $im,
                               0,
                               0,
                               mt_rand(0, Oray_Seccode::WIDTH_MAX - $this->width),
                               mt_rand(0, Oray_Seccode::HEIGHT_MAX - $this->height),
                               imageSX($im),
                               imageSY($im),
                               100);

                // 销毁一图像
                imagedestroy($im);
            }
        }

        if (!$this->background || !$backgrounds) {
            for($i = 0; $i < 3; $i++) {
                $start[$i] = mt_rand(200, 255);
                $end[$i] = mt_rand(100, 150);
                $step[$i] = ($end[$i] - $start[$i]) / $this->width;
                $color[$i] = $start[$i];
            }
            for($i = 0; $i < $this->width; $i++) {

                // 获取给定的 RGB 成分组成的颜色标识符
                $rgb = imagecolorallocate($this->_im, $color[0], $color[1], $color[2]);

                // 画一条线段
                imageline($this->_im, $i, 0, $i, $this->height, $rgb);

                $color[0] += $step[0];
                $color[1] += $step[1];
                $color[2] += $step[2];
            }

            $color[0] -= 20;
            $color[1] -= 20;
            $color[2] -= 20;
        }

        $this->_fontColor = $color;

        //return $this->_im;
    }

    /**
     * 背景图片掺杂
     *
     * @param gd $im
     */
    private function _adulterate($im) {
        $amount = $this->height / 10;
        for($i = 0; $i <= $amount; $i++) {

            // 随机颜色
            $fontColor = $this->_getFontColor();

            $color = imagecolorallocate($im, $fontColor[0], $fontColor[1], $fontColor[2]);

            $x = mt_rand(0, $this->width);
            $y = mt_rand(0, $this->height);

            if (mt_rand(0, 1)) {

                // 画椭圆弧
                imagearc($im, $x, $y, mt_rand(0, $this->width), mt_rand(0, $this->height), mt_rand(0, 360), mt_rand(0, 360), $color);
            } else {

                // 画一条线段
                imageline($im, $x, $y, mt_rand(0, $this->width), mt_rand(0, mt_rand($this->height, $this->width)), $color);
            }
        }
    }

    /**
     * 使用随机字体
     *
     * @param gd $im
     */
    private function _randomFont($im)
    {
        $units = 'BCEFGHJKMPQRTVWXY2346789';
        $x = $this->width / 4;
        $y = $this->height / 10;

        $color = imagecolorallocate($im, $this->_fontColor[0], $this->_fontColor[1], $this->_fontColor[2]);

        for($i = 0; $i <= 3; $i++) {
            $code = $units[mt_rand(0, 23)];
            imagechar($im, 5, $x * $i + mt_rand(0, $x - 10), mt_rand($y, $this->height - 10 - $y), $code, $color);
        }
    }

    /**
     * 使用TTF字体
     *
     * @return void
     */
    private function _ttfFont($im) {
        $seccode = $this->_code;
        $charset = strtolower($this->_charset);
        $ttfs    = array();

        if ($this->_isEn) {
            $folder = $this->fontPath . '/en/';
        } else {
            $folder = $this->fontPath . '/ch/';
        }

        $dirs = opendir($folder);
        while($filename = readdir($dirs)) {
            if ($filename != '.' && $filename != '..'
                && in_array(strtolower($this->_fileExt($filename)),
                            array('ttf', 'ttc'))) {
                $ttfs[] = $filename;
            }
        }

        // 字体为空时，采用GIF图片输出
        if (empty($ttfs)) {
            $this->_gifFont();
            return;
        }

        $length = $this->_length;

        if (!$this->_isEn && !empty($ttfs)) {
            if ($charset != 'utf-8') {
                $seccode = mb_convert_encoding($seccode, 'utf-8', $charset);
            }
            $seccode = str_split($seccode, 3);
            $length = count($seccode);
        }

        $totalWidth = 0;

        for($i = 0; $i < $length; $i++) {
            $font[$i]['font']  = $folder . $ttfs[array_rand($ttfs)];
            $font[$i]['angle'] = $this->angle ? mt_rand(-30, 30) : 0;
            $font[$i]['size']  = $this->_getFontSize();

            // 取得使用 TrueType 字体的文本的范围
            $box = imagettfbbox($font[$i]['size'], 0, $font[$i]['font'], $seccode[$i]);
            $font[$i]['zheight'] = max($box[1], $box[3]) - min($box[5], $box[7]);
            $box = imagettfbbox($font[$i]['size'], $font[$i]['angle'], $font[$i]['font'], $seccode[$i]);
            $font[$i]['height'] = max($box[1], $box[3]) - min($box[5], $box[7]);
            $font[$i]['hd'] = $font[$i]['height'] - $font[$i]['zheight'];
            $font[$i]['width'] = (max($box[2], $box[4]) - min($box[0], $box[6])) + mt_rand(0, $this->width / 8);
            if ($font[$i]['width'] > $this->width / $length) {
                $font[$i]['width'] = $this->width / $length;
            }
            $totalWidth += $font[$i]['width'];
        }

        // X坐标
        $x = $font[0]['angle'] > 0
             ? $this->_mt_rand(cos(deg2rad(90 - $font[0]['angle'])) * $font[0]['zheight'], $this->width - $totalWidth)
             : $this->_mt_rand(1, $this->width - $totalWidth);

        if (!$this->color) {
            $textColor = imagecolorallocate($im, $this->_fontColor[0], $this->_fontColor[1], $this->_fontColor[2]);
        }

        for($i = 0; $i < $length; $i++) {

            // 随机字体颜色
            if ($this->color) {
                $this->_fontColor = $this->_getFontColor();
                $textColor = imagecolorallocate($im, $this->_fontColor[0], $this->_fontColor[1], $this->_fontColor[2]);
            }

            // Y坐标
            $y = $font[$i]['angle'] > 0
                 ? $this->_mt_rand($font[$i]['height'], $this->height)
                 : $this->_mt_rand($font[$i]['height'] - $font[$i]['hd'], $this->height - $font[$i]['hd']);

            // 文字阴影
            if ($this->shadow) {
                $shadowColor = imagecolorallocate($im, 255 - $this->_fontColor[0], 255 - $this->_fontColor[1], 255 - $this->_fontColor[2]);
                imagettftext($im, $font[$i]['size'], $font[$i]['angle'], $x + 1, $y + 1, $shadowColor, $font[$i]['font'], $seccode[$i]);
            }

            // 用 TrueType 字体向图像写入文本
            imagettftext($im, $font[$i]['size'], $font[$i]['angle'], $x, $y, $textColor, $font[$i]['font'], $seccode[$i]);

            $x += $font[$i]['width'];
        }
    }

    /**
     * 使用GIF图片字体
     *
     * @return void
     */
    private function _gifFont($im) {
        $seccode = $this->_code;

        $seccodedir = array();

        if (function_exists('imagecreatefromgif')) {
            $folder = $this->dataPath . '/gif/';
            $dirs = opendir($folder);
            while($dir = readdir($dirs)) {
                if ($dir != '.' && $dir != '..' && file_exists($folder.$dir.'/9.gif')) {
                    $seccodedir[] = $dir;
                }
            }
        }
        $widthtotal = 0;

        for($i = 0; $i <= 3; $i++) {
            $this->imcodefile = $seccodedir ? $folder.$seccodedir[array_rand($seccodedir)].'/'.strtolower($seccode[$i]).'.gif' : '';
            if (!empty($this->imcodefile) && file_exists($this->imcodefile)) {
                $font[$i]['file'] = $this->imcodefile;
                $font[$i]['data'] = getimagesize($this->imcodefile);
                $font[$i]['width'] = $font[$i]['data'][0] + mt_rand(0, 6) - 4;
                $font[$i]['height'] = $font[$i]['data'][1] + mt_rand(0, 6) - 4;
                $font[$i]['width'] += mt_rand(0, $this->width / 5 - $font[$i]['width']);
                $widthtotal += $font[$i]['width'];
            } else {
                $font[$i]['file'] = '';
                $font[$i]['width'] = 8 + mt_rand(0, $this->width / 5 - 5);
                $widthtotal += $font[$i]['width'];
            }
        }
        $x = mt_rand(1, $this->width - $widthtotal);
        for($i = 0; $i <= 3; $i++) {
            $this->color && $this->_fontColor = array(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            if ($font[$i]['file']) {
                $this->imcode = imagecreatefromgif ($font[$i]['file']);
                if ($this->size) {
                    $font[$i]['width'] = mt_rand($font[$i]['width'] - $this->width / 20, $font[$i]['width'] + $this->width / 20);
                    $font[$i]['height'] = mt_rand($font[$i]['height'] - $this->width / 20, $font[$i]['height'] + $this->width / 20);
                }
                $y = mt_rand(0, $this->height - $font[$i]['height']);
                if ($this->shadow) {
                    $this->imcodeshadow = $this->imcode;
                    imagecolorset($this->imcodeshadow, 0 , 255 - $this->_fontColor[0], 255 - $this->_fontColor[1], 255 - $this->_fontColor[2]);
                    imagecopyresized($im, $this->imcodeshadow, $x + 1, $y + 1, 0, 0, $font[$i]['width'], $font[$i]['height'], $font[$i]['data'][0], $font[$i]['data'][1]);
                }
                imagecolorset($this->imcode, 0 , $this->_fontColor[0], $this->_fontColor[1], $this->_fontColor[2]);
                imagecopyresized($im, $this->imcode, $x, $y, 0, 0, $font[$i]['width'], $font[$i]['height'], $font[$i]['data'][0], $font[$i]['data'][1]);
            } else {
                $y = mt_rand(0, $this->height - 20);
                if ($this->shadow) {
                    $text_shadowcolor = imagecolorallocate($im, 255 - $this->_fontColor[0], 255 - $this->_fontColor[1], 255 - $this->_fontColor[2]);
                    imagechar($im, 5, $x + 1, $y + 1, $seccode[$i], $text_shadowcolor);
                }
                $text_color = imagecolorallocate($im, $this->_fontColor[0], $this->_fontColor[1], $this->_fontColor[2]);
                imagechar($im, 5, $x, $y, $seccode[$i], $text_color);
            }
            $x += $font[$i]['width'];
        }
    }

    /**
     * 获取颜色
     *
     * @return array
     */
    private function _getFontColor()
    {
        // 随机颜色
        if ($this->color) {
            $color = array(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
        } else {
            $color = $this->_fontColor;
        }
        return $color;
    }

    /**
     * 获取字体大小
     *
     * @return int
     */
    private function _getFontSize()
    {
        $size = $this->_isEn ? $this->width / 6 : $this->width / 7;

        // 随机字体大小
        if ($this->size) {
            $size = mt_rand(($size - $this->width / 40), ($size + $this->width / 20));
        }

        return $size;
    }

    /**
     * 生成BMP图片
     *
     * @return void
     */
    private function _bitmap() {
        $numbers = array (
            'B' => array('00','fc','66','66','66','7c','66','66','fc','00'),
            'C' => array('00','38','64','c0','c0','c0','c4','64','3c','00'),
            'E' => array('00','fe','62','62','68','78','6a','62','fe','00'),
            'F' => array('00','f8','60','60','68','78','6a','62','fe','00'),
            'G' => array('00','78','cc','cc','de','c0','c4','c4','7c','00'),
            'H' => array('00','e7','66','66','66','7e','66','66','e7','00'),
            'J' => array('00','f8','cc','cc','cc','0c','0c','0c','7f','00'),
            'K' => array('00','f3','66','66','7c','78','6c','66','f7','00'),
            'M' => array('00','f7','63','6b','6b','77','77','77','e3','00'),
            'P' => array('00','f8','60','60','7c','66','66','66','fc','00'),
            'Q' => array('00','78','cc','cc','cc','cc','cc','cc','78','00'),
            'R' => array('00','f3','66','6c','7c','66','66','66','fc','00'),
            'T' => array('00','78','30','30','30','30','b4','b4','fc','00'),
            'V' => array('00','1c','1c','36','36','36','63','63','f7','00'),
            'W' => array('00','36','36','36','77','7f','6b','63','f7','00'),
            'X' => array('00','f7','66','3c','18','18','3c','66','ef','00'),
            'Y' => array('00','7e','18','18','18','3c','24','66','ef','00'),
            '2' => array('fc','c0','60','30','18','0c','cc','cc','78','00'),
            '3' => array('78','8c','0c','0c','38','0c','0c','8c','78','00'),
            '4' => array('00','3e','0c','fe','4c','6c','2c','3c','1c','1c'),
            '6' => array('78','cc','cc','cc','ec','d8','c0','60','3c','00'),
            '7' => array('30','30','38','18','18','18','1c','8c','fc','00'),
            '8' => array('78','cc','cc','cc','78','cc','cc','cc','78','00'),
            '9' => array('f0','18','0c','6c','dc','cc','cc','cc','78','00')
        );

        foreach($numbers as $i => $number) {
            for($j = 0; $j < 6; $j++) {
                $a1 = substr('012', mt_rand(0, 2), 1) . substr('012345', mt_rand(0, 5), 1);
                $a2 = substr('012345', mt_rand(0, 5), 1) . substr('0123', mt_rand(0, 3), 1);
                mt_rand(0, 1) == 1 ? array_push($numbers[$i], $a1) : array_unshift($numbers[$i], $a1);
                mt_rand(0, 1) == 0 ? array_push($numbers[$i], $a1) : array_unshift($numbers[$i], $a2);
            }
        }

        $bitmap = array();
        for($i = 0; $i < 20; $i++) {
            for($j = 0; $j <= 3; $j++) {
                $bytes = $numbers[$this->_code[$j]][$i];
                $a = mt_rand(0, 14);
                array_push($bitmap, $bytes);
            }
        }

        for($i = 0; $i < 8; $i++) {
            $a = substr('012345', mt_rand(0, 2), 1) . substr('012345', mt_rand(0, 5), 1);
            array_unshift($bitmap, $a);
            array_push($bitmap, $a);
        }

        $image = pack('H*', '424d9e000000000000003e0000002800000020000000180000'
                          . '00010001000000000060000000000000000000000000000000'
                          . '0000000000000000FFFFFF00' . implode('', $bitmap));

        header('Content-Type: image/bmp');
        echo $image;
    }

    /**
     * 随机获取范围值
     *
     * @param int $min
     * @param int $max
     * @return int
     */
    private function _mt_rand($min, $max)
    {
        if ($min > $max) {
            $tmp = $max;
            $max = $min;
            $min = $tmp;
        }

        return mt_rand($min, $max);
    }
}
