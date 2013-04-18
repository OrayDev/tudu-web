<?php
/**
 * Forbid Controller
 * 服务暂停提示页面
 *
 * @version $Id: ForbidController.php 1031 2011-07-28 10:17:56Z cutecube $
 */

class ForbidController extends TuduX_Controller_Base
{

	public function indexAction()
	{
		$this->lang = Tudu_Lang::getInstance()->load('common');

		$this->view->LANG   = $this->lang;
		$this->view->forbid = $this->session->auth['invalid'];
	}
}