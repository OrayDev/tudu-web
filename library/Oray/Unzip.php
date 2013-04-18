<?php
/**
 * Oray Framework
 * 
 * @category   Oray
 * @package    Oray_Unzip
 * @author     Oray-Yongfa
 * @copyright  Copyright (c) 2009-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Unzip.php 2801 2013-04-02 09:57:31Z chenyongfa $
 */
class Oray_Unzip
{
    /**
     *
     * @var string
     */
    public $comment = '';

    /**
     * 文件信息
     *
     * @var array
     */
    public $entries = array();

    /**
     * 文件名
     *
     * @var string
     */
    public $name = '';

    /**
     * 文件大小
     *
     * @var int
     */
    public $size = 0;

    /**
     * 文件创建时间
     *
     * @var int
     */
    public $time = 0;

    /**
     * Singleton instance
     *
     * @var Auth
     */
    protected static $_instance = null;

    /**
     * 单例模式，隐藏构造函数
     */
    protected function __construct()
    {}

    /**
     * 获取对象实例
     *
     * @return Tudu_Install_Function
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * 创建对象实例
     *
     * @return Tudu_Install_Function
     */
    public static function newInstance()
    {
        return new self();
    }

    /**
     * 文件总数
     * 
     * @return number
     */
    public function count() {
        return count($this->entries);
    }

    /**
     * 读取压缩包文件
     *
     * @param string $in_FileName
     */
    public function readFile($in_FileName)
    {
        $this->entries = array();
 
        $this->name = $in_FileName;
        $this->time = filemtime($in_FileName);
        $this->size = filesize($in_FileName);

        $oF = fopen($in_FileName, 'rb');
        $vZ = fread($oF, $this->size);
        fclose($oF);

        $aE = explode("\x50\x4b\x05\x06", $vZ);
        $aP = unpack('x16/v1CL', $aE[1]);

        $this->comment = substr($aE[1], 18, $aP['CL']);
        $this->comment = strtr($this->comment, array("\r\n" => "\n", "\r"   => "\n"));

        $aE = explode("\x50\x4b\x01\x02", $vZ);
        $aE = explode("\x50\x4b\x03\x04", $aE[0]);
        array_shift($aE);

        foreach($aE as $vZ) {
            $aI = array();
            $aI['E']  = 0;
            $aI['EM'] = '';
            $aP = unpack('v1VN/v1GPF/v1CM/v1FT/v1FD/V1CRC/V1CS/V1UCS/v1FNL', $vZ);
            $bE = ($aP['GPF'] && 0x0001) ? TRUE : FALSE;
            $nF = $aP['FNL'];

            if($aP['GPF'] & 0x0008) {
                $aP1 = unpack('V1CRC/V1CS/V1UCS', substr($vZ, -12));

                $aP['CRC'] = $aP1['CRC'];
                $aP['CS']  = $aP1['CS'];
                $aP['UCS'] = $aP1['UCS'];

                $vZ = substr($vZ, 0, -12);
            }

            $aI['N'] = substr($vZ, 26, $nF);
    
            if(substr($aI['N'], -1) == '/') {
                continue;
            }

            $aI['P'] = dirname($aI['N']);
            $aI['P'] = $aI['P'] == '.' ? '' : $aI['P'];
            $aI['N'] = basename($aI['N']);

            $vZ = substr($vZ, 26 + $nF);

            if(strlen($vZ) != $aP['CS']) {
                $aI['E']  = 1;
                $aI['EM'] = 'Compressed size is not equal with the value in header information.';
            } else {
                if($bE) {
                    $aI['E']  = 5;
                    $aI['EM'] = 'File is encrypted, which is not supported from this class.';
                } else {
                    switch($aP['CM']) {
                        case 0: // Stored
                            break;

                        case 8: // Deflated
                            $vZ = gzinflate($vZ);
                            break;

                        case 12: // BZIP2
                            if(!extension_loaded('bz2')) {
                                if(strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
                                    @dl('php_bz2.dll');
                                } else {
                                    @dl('bz2.so');
                                }
                            }

                            if(extension_loaded('bz2')) {
                                $vZ = bzdecompress($vZ);
                            } else {
                                $aI['E']  = 7;
                                $aI['EM'] = "PHP BZIP2 extension not available.";
                            }

                            break;

                        default:
                            $aI['E']  = 6;
                            $aI['EM'] = "De-/Compression method {$aP['CM']} is not supported.";
                    }

                    if(! $aI['E']) {
                        if($vZ === FALSE) {
                            $aI['E']  = 2;
                            $aI['EM'] = 'Decompression of data failed.';
                        } else {
                            if(strlen($vZ) != $aP['UCS']) {
                                $aI['E']  = 3;
                                $aI['EM'] = 'Uncompressed size is not equal with the value in header information.';
                            } else {
                                if(crc32($vZ) != $aP['CRC']) {
                                    $aI['E']  = 4;
                                    $aI['EM'] = 'CRC32 checksum is not equal with the value in header information.';
                                }
                            }
                        }
                    }
                }
            }

            $aI['D'] = $vZ;

            $aI['T'] = mktime(($aP['FT']  & 0xf800) >> 11,
                    ($aP['FT']  & 0x07e0) >>  5,
                    ($aP['FT']  & 0x001f) <<  1,
                    ($aP['FD']  & 0x01e0) >>  5,
                    ($aP['FD']  & 0x001f),
                    (($aP['FD'] & 0xfe00) >>  9) + 1980);

            $this->entries[] = new Oray_Unzip_Entry($aI);
        }

        return $this->entries;
    }
}