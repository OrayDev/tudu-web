<?php
/**
 * Tudu Library
 *
 * LICENSE
 *
 *
 * @category   Tudu
 * @package    Tudu_Pinyin
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: Pinyin.php 361 2010-09-19 07:15:12Z cutecube $
 */

/**
 * @category   Tudu
 * @package    Tudu_Pinyin
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Tudu_Pinyin
{

	/**
	 * 
	 * @var array
	 */
	protected static $_map = array(
        array("zuo", -10254),
		array("zun", -10256), 
		array("zui", -10260), 
		array("zuan", -10262), 
		array("zu", -10270), 
		array("zou", -10274), 
		array("zong", -10281), 
		array("zi", -10296), 
		array("zhuo", -10307), 
		array("zhun", -10309), 
		array("zhui", -10315), 
		array("zhuang", -10322), 
		array("zhuan", -10328), 
		array("zhuai", -10329), 
		array("zhua", -10331), 
		array("zhu", -10519), 
		array("zhou", -10533), 
		array("zhong", -10544), 
		array("zhi", -10587), 
		array("zheng", -10764), 
		array("zhen", -10780), 
		array("zhe", -10790), 
		array("zhao", -10800), 
		array("zhang", -10815), 
		array("zhan", -10832), 
		array("zhai", -10838), 
		array("zha", -11014), 
		array("zeng", -11018), 
		array("zen", -11019), 
		array("zei", -11020), 
		array("ze", -11024), 
		array("zao", -11038), 
		array("zang", -11041), 
		array("zan", -11045), 
		array("zai", -11052), 
		array("za", -11055), 
		array("yun", -11067), 
		array("yue", -11077), 
		array("yuan", -11097), 
		array("yu", -11303), 
		array("you", -11324), 
		array("yong", -11339), 
		array("yo", -11340), 
		array("ying", -11358), 
		array("yin", -11536), 
		array("yi", -11589), 
		array("ye", -11604), 
		array("yao", -11781), 
		array("yang", -11798), 
		array("yan", -11831), 
		array("ya", -11847), 
		array("xun", -11861), 
		array("xue", -11867), 
		array("xuan", -12039), 
		array("xu", -12058), 
		array("xiu", -12067), 
		array("xiong", -12074), 
		array("xing", -12089), 
		array("xin", -12099), 
		array("xie", -12120), 
		array("xiao", -12300), 
		array("xiang", -12320), 
		array("xian", -12346), 
		array("xia", -12359), 
		array("xi", -12556), 
		array("wu", -12585), 
		array("wo", -12594), 
		array("weng", -12597), 
		array("wen", -12607), 
		array("wei", -12802), 
		array("wang", -12812), 
		array("wan", -12829), 
		array("wai", -12831), 
		array("wa", -12838), 
		array("tuo", -12849), 
		array("tun", -12852), 
		array("tui", -12858), 
		array("tuan", -12860), 
		array("tu", -12871), 
		array("tou", -12875), 
		array("tong", -12888), 
		array("ting", -13060), 
		array("tie", -13063), 
		array("tiao", -13068), 
		array("tian", -13076), 
		array("ti", -13091), 
		array("teng", -13095), 
		array("te", -13096), 
		array("tao", -13107), 
		array("tang", -13120), 
		array("tan", -13138), 
		array("tai", -13147), 
		array("ta", -13318), 
		array("suo", -13326), 
		array("sun", -13329), 
		array("sui", -13340), 
		array("suan", -13343), 
		array("su", -13356), 
		array("sou", -13359), 
		array("song", -13367), 
		array("si", -13383), 
		array("shuo", -13387), 
		array("shun", -13391), 
		array("shui", -13395), 
		array("shuang", -13398), 
		array("shuan", -13400), 
		array("shuai", -13404), 
		array("shua", -13406), 
		array("shu", -13601), 
		array("shou", -13611), 
		array("shi", -13658), 
		array("sheng", -13831), 
		array("shen", -13847), 
		array("she", -13859), 
		array("shao", -13870), 
		array("shang", -13878), 
		array("shan", -13894), 
		array("shai", -13896), 
		array("sha", -13905), 
		array("seng", -13906), 
		array("sen", -13907), 
		array("se", -13910), 
		array("sao", -13914), 
		array("sang", -13917), 
		array("san", -14083), 
		array("sai", -14087), 
		array("sa", -14090), 
		array("ruo", -14092), 
		array("run", -14094), 
		array("rui", -14097), 
		array("ruan", -14099), 
		array("ru", -14109), 
		array("rou", -14112), 
		array("rong", -14122), 
		array("ri", -14123),
		array("reng", -14125), 
		array("ren", -14135), 
		array("re", -14137), 
		array("rao", -14140), 
		array("rang", -14145), 
		array("ran", -14149), 
		array("qun", -14151), 
		array("que", -14159), 
		array("quan", -14170), 
		array("qu", -14345), 
		array("qiu", -14353), 
		array("qiong", -14355), 
		array("qing", -14368), 
		array("qin", -14379), 
		array("qie", -14384), 
		array("qiao", -14399), 
		array("qiang", -14407), 
		array("qian", -14429), 
		array("qia", -14594), 
		array("qi", -14630), 
		array("pu", -14645), 
		array("po", -14654), 
		array("ping", -14663), 
		array("pin", -14668), 
		array("pie", -14670), 
		array("piao", -14674), 
		array("pian", -14678), 
		array("pi", -14857), 
		array("peng", -14871), 
		array("pen", -14873), 
		array("pei", -14882), 
		array("pao", -14889), 
		array("pang", -14894), 
		array("pan", -14902), 
		array("pai", -14908), 
		array("pa", -14914), 
		array("ou", -14921), 
		array("o", -14922), 
		array("nuo", -14926), 
		array("nue", -14928), 
		array("nuan", -14929), 
		array("nv", -14930), 
		array("nu", -14933), 
		array("nong", -14937), 
		array("niu", -14941), 
		array("ning", -15109), 
		array("nin", -15110), 
		array("nie", -15117), 
		array("niao", -15119), 
		array("niang", -15121), 
		array("nian", -15128), 
		array("ni", -15139), 
		array("neng", -15140), 
		array("nen", -15141), 
		array("nei", -15143), 
		array("ne", -15144), 
		array("nao", -15149), 
		array("nang", -15150), 
		array("nan", -15153), 
		array("nai", -15158), 
		array("na", -15165), 
		array("mu", -15180), 
		array("mou", -15183), 
		array("mo", -15362), 
		array("miu", -15363), 
		array("ming", -15369), 
		array("min", -15375), 
		array("mie", -15377), 
		array("miao", -15385), 
		array("mian", -15394), 
		array("mi", -15408), 
		array("meng", -15416), 
		array("men", -15419), 
		array("mei", -15435), 
		array("me", -15436), 
		array("mao", -15448), 
		array("mang", -15454), 
		array("man", -15625), 
		array("mai", -15631), 
		array("ma", -15640), 
		array("luo", -15652), 
		array("lun", -15659), 
		array("lue", -15661), 
		array("luan", -15667), 
		array("lv", -15681), 
		array("lu", -15701), 
		array("lou", -15707), 
		array("long", -15878), 
		array("liu", -15889), 
		array("ling", -15903), 
		array("lin", -15915), 
		array("lie", -15920), 
		array("liao", -15933), 
		array("liang", -15944), 
		array("lian", -15958), 
		array("lia", -15959), 
		array("li", -16155), 
		array("leng", -16158), 
		array("lei", -16169), 
		array("le", -16171), 
		array("lao", -16180), 
		array("lang", -16187), 
		array("lan", -16202), 
		array("lai", -16205), 
		array("la", -16212), 
		array("kuo", -16216), 
		array("kun", -16220), 
		array("kui", -16393), 
		array("kuang", -16401), 
		array("kuan", -16403), 
		array("kuai", -16407), 
		array("kua", -16412), 
		array("ku", -16419), 
		array("kou", -16423), 
		array("kong", -16427), 
		array("keng", -16429), 
		array("ken", -16433), 
		array("ke", -16448), 
		array("kao", -16452), 
		array("kang", -16459), 
		array("kan", -16465), 
		array("kai", -16470), 
		array("ka", -16474), 
		array("jun", -16647), 
		array("jue", -16657), 
		array("juan", -16664), 
		array("ju", -16689), 
		array("jiu", -16706), 
		array("jiong", -16708), 
		array("jing", -16733), 
		array("jin", -16915), 
		array("jie", -16942), 
		array("jiao", -16970), 
		array("jiang", -16983), 
		array("jian", -17185), 
		array("jia", -17202), 
		array("ji", -17417), 
		array("huo", -17427), 
		array("hun", -17433), 
		array("hui", -17454), 
		array("huang", -17468), 
		array("huan", -17482), 
		array("huai", -17487), 
		array("hua", -17496), 
		array("hu", -17676), 
		array("hou", -17683), 
		array("hong", -17692), 
		array("heng", -17697), 
		array("hen", -17701), 
		array("hei", -17703), 
		array("he", -17721), 
		array("hao", -17730), 
		array("hang", -17733), 
		array("han", -17752), 
		array("hai", -17759), 
		array("ha", -17922), 
		array("guo", -17928), 
		array("gun", -17931), 
		array("gui", -17947), 
		array("guang", -17950), 
		array("guan", -17961), 
		array("guai", -17964), 
		array("gua", -17970), 
		array("gu", -17988), 
		array("gou", -17997), 
		array("gong", -18012), 
		array("geng", -18181), 
		array("gen", -18183), 
		array("gei", -18184), 
		array("ge", -18201), 
		array("gao", -18211), 
		array("gang", -18220), 
		array("gan", -18231), 
		array("gai", -18237), 
		array("ga", -18239), 
		array("fu", -18446), 
		array("fou", -18447), 
		array("fo", -18448), 
		array("feng", -18463), 
		array("fen", -18478), 
		array("fei", -18490), 
		array("fang", -18501), 
		array("fan", -18518), 
		array("fa", -18526), 
		array("er", -18696), 
		array("en", -18697), 
		array("e", -18710), 
		array("duo", -18722), 
		array("dun", -18731), 
		array("dui", -18735), 
		array("duan", -18741), 
		array("du", -18756), 
		array("dou", -18763), 
		array("dong", -18773), 
		array("diu", -18774), 
		array("ding", -18783), 
		array("die", -18952), 
		array("diao", -18961), 
		array("dian", -18977), 
		array("di", -18996), 
		array("deng", -19003), 
		array("de", -19006), 
		array("dao", -19018), 
		array("dang", -19023), 
		array("dan", -19038), 
		array("dai", -19212), 
		array("da", -19218), 
		array("cuo", -19224), 
		array("cun", -19227), 
		array("cui", -19235), 
		array("cuan", -19238), 
		array("cu", -19242), 
		array("cou", -19243), 
		array("cong", -19249), 
		array("ci", -19261), 
		array("chuo", -19263), 
		array("chun", -19270), 
		array("chui", -19275), 
		array("chuang", -19281), 
		array("chuan", -19288), 
		array("chuai", -19289), 
		array("chu", -19467), 
		array("chou", -19479), 
		array("chong", -19484), 
		array("chi", -19500), 
		array("cheng", -19515), 
		array("chen", -19525), 
		array("che", -19531), 
		array("chao", -19540), 
		array("chang", -19715), 
		array("chan", -19725), 
		array("chai", -19728), 
		array("cha", -19739), 
		array("ceng", -19741), 
		array("ce", -19746), 
		array("cao", -19751), 
		array("cang", -19756), 
		array("can", -19763), 
		array("cai", -19774), 
		array("ca", -19775), 
		array("bu", -19784), 
		array("bo", -19805), 
		array("bing", -19976), 
		array("bin", -19982), 
		array("bie", -19986), 
		array("biao", -19990), 
		array("bian", -20002), 
		array("bi", -20026), 
		array("beng", -20032), 
		array("ben", -20036), 
		array("bei", -20051), 
		array("bao", -20230), 
		array("bang", -20242), 
		array("ban", -20257), 
		array("bai", -20265), 
		array("ba", -20283), 
		array("ao", -20292), 
		array("ang", -20295), 
		array("an", -20304), 
		array("ai", -20317), 
		array("a", -20319)
	);

	/**
	 * 字符编码
	 * 
	 * @var string
	 */
	protected static $_charset = 'utf-8';

	/**
	 * 设置字符集合
	 * 
	 * @param string $charset
	 */
	public static function setCharset($charset)
	{
		self::$_charset = $charset;
	}
	
	/**
	 * 转换
	 * 
	 * @param string  $str
	 * @param boolean $getFirst 只获取首字母
	 * @return string
	 */
	public static function parse($str, $getFirst = false)
	{
		$str = self::_convert($str);
		$ret = '';
        for ($i = 0, $c = strlen($str) - 1; $i < $c; $i++) {
            $p = ord($str{$i});
            if ($p > 160 && $i < $c) {
                $q = ord($str{++$i});
                $p = $p * 256 + $q - 65536;
            }
            
            $pinyin = self::_decode($p);
            
            $ret .= $getFirst ? substr($pinyin, 0, 1) : $pinyin;
        }
        
        return $ret;
	}
	
	/**
	 * 获取字符拼音
	 * 
	 * @param string $char
	 * @return string
	 */
	private static function _decode($char)
	{
		if ($char > 0 && $char < 160) {
			return ''; //chr($char);
		}
		
		if ($char < -20319 || $char > -10247) {
			return '';
		}
		
		foreach (self::$_map as $pair) {
			if ($pair[1] <= $char) {
				return $pair[0];
			}
		}
	}
	
	/**
	 * 转换字符编码
	 * 
	 * @param string $str
	 */
	private static function _convert($str, $charset = null)
	{
		if (!$charset) {
			$charset = self::$_charset;
		}
		
        if (self::$_charset == 'gbk') {
        	return $str;
        }
        
        return iconv($charset, 'gbk', $str);
	}
}