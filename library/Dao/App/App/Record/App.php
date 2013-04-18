<?php
/**
 * App
 *
 * LICENSE
 *
 *
 * @category   Dao
 * @package    Dao_Md
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.oray.com/
 * @version    $Id: App.php 2766 2013-03-05 10:16:20Z chenyongfa $
 */

/**
 * @category   Dao
 * @package    Dao_Md
 * @subpackage App
 * @copyright  Copyright (c) 2009-2010 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Dao_App_App_Record_App extends Oray_Dao_Record
{
    /**
     * 应用ID
     *
     * @var string
     */
    public $appId;

    /**
     * 应用名称
     *
     * @var string
     */
    public $appName;

    /**
     * 应用类型
     *
     * @var int
     */
    public $type;

    /**
     * 版本号
     *
     * @var string
     */
    public $version;

    /**
     * 访问url
     *
     * @var string
     */
    public $url;

    /**
     * 应用图标地址
     *
     * @var string
     */
    public $logo;

    /**
     * 作者
     *
     * @var string
     */
    public $author;

    /**
     * 应用描述
     *
     * @var string
     */
    public $description;

    /**
     * 应用详细介绍
     *
     * @var string
     */
    public $content;

    /**
     * 语言
     *
     * @var int
     */
    public $languages;

    /**
     * 分数
     *
     * @var int
     */
    public $score;

    /**
     * 评论数
     *
     * @var int
     */
    public $commentNum;

    /**
     * 创建时间
     *
     * @var int
     */
    public $createTime;

    /**
     * 最后更新时间
     *
     * @var int
     */
    public $lastUpdateTime;

    /**
     * 过期时间
     *
     * @var int
     */
    public $expireDate;

    /**
     * 应用状态
     *
     * @var int
     */
    public $status;

    /**
     * 使用应用的组织ID
     *
     * @var string
     */
    public $orgId;

    /**
     * 应用是否已安装
     *
     * @var boolean
     */
    public $isInstall;

    /**
     * 激活时间
     *
     * @var int
     */
    public $activeTime;

    /**
     * 应用其他设置
     *
     * @var array
     */
    public $settings;

    /**
     * Construct
     *
     * @param array $record
     */
    public function __construct(array $record)
    {
        $this->appId          = $record['appid'];
        $this->appName        = $record['appname'];
        $this->type           = isset($record['type']) ? $record['type'] : null;
        $this->version        = isset($record['version']) ? $record['version'] : null;
        $this->url            = $record['url'];
        $this->author         = isset($record['author']) ? $record['author'] : null;
        $this->logo           = $record['logo'];
        $this->description    = $record['description'];
        $this->content        = isset($record['content']) ? $record['content'] : null;
        $this->languages      = isset($record['languages']) ? $record['languages'] : null;
        $this->score          = isset($record['score']) ? $this->_toInt($record['score']) : null;
        $this->commentNum     = isset($record['commentnum']) ? $this->_toInt($record['commentnum']) : null;
        $this->createTime     = $this->_toTimestamp($record['createtime']);
        $this->lastUpdateTime = $this->_toTimestamp($record['lastupdatetime']);
        $this->status         = $this->_toInt($record['status']);
        $this->orgId          = $record['orgid'];
        $this->expireDate     = isset($record['expiredate']) ? $this->_toTimestamp($record['expiredate']) : null;
        $this->activeTime     = isset($record['activetime']) ? $this->_toTimestamp($record['activetime']) : null;
        $this->isInstall      = (isset($record['orgid']) && isset($record['status'])) ? true : false;
        $this->settings       = !empty($record['settings']) ? json_decode($record['settings'], true) : array();

        parent::__construct();
    }
}