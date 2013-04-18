<?php
/**
 * Job
 *
 * LICENSE
 *
 *
 * @category   Task_Tudu
 * @package    Task_Tudu
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 * @link       http://www.tudu.com/
 * @version    $Id: Attachment.php 1384 2011-12-13 11:11:34Z cutecube $
 */

/**
 * 计划：每天执行一次
 *
 * 附件清理 -- 保留常用功能（清除数据库中没有回复关联的附件）
 *
 * @category   Task_Tudu
 * @package    Task_Tudu
 * @copyright  Copyright (c) 2011-2011 Shanghai Best Oray Information S&T CO., Ltd.
 */
class Task_Tudu_Attachment extends Task_Abstract
{
    /**
     * 文件路径
     */
    public $_path;

    /**
     *
     */
    public function startUp()
    {
        if (empty($this->_options['attachment']['path'])) {
            throw new Task_Exception("Missing config node: (attachment.path)");
        }

        foreach ($this->_options['multidb'] as $key => $item) {
            if (0 === strpos($key, 'ts')) {
                $this->_dbs[$key] = Zend_Db::factory($item['adapter'], $item['params']);
            }
        }
    }

    /**
     * 执行
     */
    public function run()
    {
        $this->_path = $this->_options['attachment']['path'];

        foreach ($this->_dbs as $db) {
            $this->saveNetdiskFile($db);
            $this->cleanAttachment($db);
        }
    }

    /**
     * 保存网盘文件
     */
    public function saveNetdiskFile($db)
    {
        $sql = "SELECT file_id, org_id, path, unique_id, attach_file_id FROM nd_file WHERE is_from_attach = 1";
        $attachs = $db->fetchAll($sql);
        foreach ($attachs as $attach) {
            $fileName = $this->_path . '/' . $attach['path'] . '/' . $attach['attach_file_id'];
            if (file_exists($fileName)) {
                // 复制文件
                $savePath = $attach['org_id'] . '/_netdisk/' . $attach['unique_id'];

                $ret = @copy($fileName, $this->_path . '/' . $savePath . '/' . $attach['file_id']);
                if (!$ret) {
                    $this->getLogger()->warn("File:({$fileName}) copy failure");
    				continue ;
                }
                // 更新数据表
                $db->query('UPDATE nd_file SET is_from_attach = 0, path = ' . $db->quote($savePath) . ' WHERE org_id = '. $db->quote($attach['org_id']) . ' AND unique_id = ' . $db->quote($attach['unique_id']) . ' AND file_id = ' . $db->quote($attach['file_id']));

                $this->getLogger()->info("File name:({$fileName}) copy success");
            } else {
                $this->getLogger()->warn("File exists:({$fileName})");
                continue ;
            }
        }
    }

    /**
     * 清理没有回复关联的附件
     */
    public function cleanAttachment($db)
    {
        $sql = 'SELECT A.file_id, A.path, A.is_netdisk FROM td_attachment A LEFT JOIN td_attach_post AP ON A.file_id = AP.file_id '
             . 'LEFT JOIN td_post AS P ON AP.tudu_id = P.tudu_id AND AP.post_id = P.post_id '
             . 'WHERE P.post_id IS NULL';
        $attachs = $db->fetchAll($sql);
        foreach ($attachs as $attach) {

            if (!$attach['is_netdisk']) {
                $fileName = $this->_path . '/' . $attach['path'] . '/' . $attach['file_id'];
                if (file_exists($fileName)) {
                    // 删除附件
                    $ret = @unlink($fileName);

                    if (!$ret) {
                        $this->getLogger()->warn("File delete failure:({$fileName})");
                        continue ;
                    }
                }
            }

            // 更新数据表
            $db->query('DELETE FROM td_attachment WHERE file_id = ' . $db->quote($attach['file_id']));
            $this->getLogger()->info("File delete success:({$fileName})");
        }
    }
}