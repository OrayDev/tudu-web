<!DOCTYPE html>
<html>
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="en"> <!--<![endif]-->
<!--[if lt IE 9]> <script src="/js/html5.js" type="text/javascript"></script> <![endif]-->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>图度开源安装向导</title>
<link href="/css/install.css" type="text/css" rel="stylesheet">
<script src="/js/jquery-1.4.4.js" type="text/javascript"></script>
<script src="/js/install.js" type="text/javascript"></script>
</head>
<body>
<header class="header">
    <div class="logo"><a href="http://www.tudu.com" target="_blank"><img src="/images/install/logo_130328.gif" border="0" /></a>&nbsp;<span class="text"><em>丨</em>云办公系统 v1.0</span></div>
</header>
<div class="container">
    <section class="step">
        <ol>
            <li style="margin:0;"><span class="step-1"></span>欢迎</li>
            <li><span class="step-2"></span>环境检查</li>
            <li><span class="step-3"></span>配置</li>
            <li><span class="step-4"></span>完成</li>
        </ol>
    </section>
    <section class="agreement">
        <form id="theform" action="?" method="get" onsubmit="return checked();">
            <div class="title">欢迎使用图度办公系统</div>
            <div class="agreement-content">
                <div style="padding:5px;">

	<p class="MsoNormal">
		版权所有<span> (c) 2005-2013</span>，上海贝锐信息科技有限公司保留所有权利。<span></span>
	</p>
	<p class="MsoNormal">
		感谢您选择图度办公系统。希望我们的努力能为您提供一个高效、安全的办公系统。图度官网为<span> http://www.tudu.com</span>。<span></span>
	</p>
	<p class="MsoNormal">
		用户须知：本协议是您与上海贝锐信息科技有限公司（以下简称“<span>Oray</span>”）之间关于您非商业使用图度的法律协议。无论您是个人或组织、盈利与否、用途如何（包括以学习和研究为目的），均需仔细阅读本协议，包括免除或者限制<span>Oray</span>责任的免责条款及对您的权利限制。请您审阅并接受或不接受本服务条款。如您不同意本服务条款及<span>/</span>或<span>Oray</span>随时对其的修改，您应不使用或主动取消使用图度。否则，您的任何对图度的注册、登陆、下载、查看等使用行为将被视为您对本服务条款全部的完全接受，包括接受<span>Oray</span>对服务条款随时所做的任何修改。<span></span>
	</p>
	<p class="MsoNormal">
		本服务条款一旦发生变更<span>, Oray</span>将在网页上公布修改内容。修改后的服务条款一旦在网页上公布即有效代替原来的服务条款。您可随时登陆<span>Oray</span>官方论坛查阅最新版服务条款。如果您选择接受本条款，即表示您同意接受协议各项条件的约束。如果您不同意本服务条款，则不能获得使用本服务的权利。您若有违反本条款规定，<span>Oray</span>有权随时中止或终止您对图度的使用资格并保留追究相关法律责任的权利。<span></span>
	</p>
	<p class="MsoNormal">
		在理解、同意、并遵守本协议的全部条款后，方可开始使用图度。您可能与<span>Oray</span>直接签订另一书面协议，以补充或者取代本协议的全部或者任何部分。<span></span>
	</p>
	<p class="MsoNormal">
		Oray拥有本软件的全部知识产权。本软件只供许可协议，并非出售。<span>Oray</span>只允许您在遵守本协议各项条款的情况下复制、下载、安装、使用或者以其他方式受益于本软件的功能或者知识产权。<span></span>
	</p>
	<p class="MsoNormal">
		<b>I. </b><b>协议许可的权利<span></span></b>
	</p>
	<ol>
		<li class="MsoNormal">
			您可以在完全遵守本许可协议的基础上，将本软件应用于非商业用途，而不必支付软件版权许可费用。 <span></span>
		</li>
		<li class="MsoNormal">
			您可以在协议规定的约束和限制范围内修改图度源代码<span>(</span>如果被提供的话<span>)</span>或界面风格以适应您的网站要求。 <span></span>
		</li>
		<li class="MsoNormal">
			您拥有使用本软件构建的网站中全部会员资料、文章及相关信息的所有权，并独立承担与使用本软件构建的网站内容的审核、注意义务，确保其不侵犯任何人的合法权益，独立承担因使用<span>Oray</span>软件和服务带来的全部责任，若造成<span>Oray</span>或用户损失的，您应予以全部赔偿。 <span></span>
		</li>
		<li class="MsoNormal">
			本协议是您与<span>Oray</span>之间关于您非商业使用<span>Oray</span>提供的各种软件产品及服务的法律协议，若您需将<span>Oray</span>软件或服务用户商业用途，必须另行获得<span>Oray</span>的书面许可，您在获得商业授权之后，您可以将本软件应用于商业用途，同时依据所购买的授权类型中确定的技术支持期限、技术支持方式和技术支持内容，自购买时刻起，在技术支持期限内拥有通过指定的方式获得指定范围内的技术支持服务。商业授权用户享有反映和提出意见的权力，相关意见将被作为首要考虑，但没有一定被采纳的承诺或保证。 <span></span>
		</li>
	</ol>
	<p class="MsoNormal">
		<b>II. </b><b>协议规定的约束和限制<span></span></b>
	</p>
	<ol>
		<li class="MsoNormal">
			未获<span>Oray</span>书面商业授权之前，不得将本软件用于商业用途（包括但不限于企业网站、经营性网站、以营利为目或实现盈利的网站）。 <span></span>
		</li>
		<li class="MsoNormal">
			不得对本软件或与之关联的商业授权进行出租、出售、抵押或发放子许可证。 <span></span>
		</li>
		<li class="MsoNormal">
			无论如何，即无论用途如何、是否经过修改或美化、修改程度如何，只要使用图度的整体或任何部分，未经书面许可，图度名称必须保留，而不能清除或修改。 <span></span>
		</li>
		<li class="MsoNormal">
			禁止在图度的整体或任何部分基础上以发展任何派生版本、修改版本或第三方版本用于重新分发。 <span></span>
		</li>
		<li class="MsoNormal">
			如果您未能遵守本协议的条款，您的授权将被终止，所许可的权利将被收回，同时您应承担相应法律责任。 <span></span>
		</li>
	</ol>
	<p class="MsoNormal">
		<b>III. </b><b>有限担保和免责声明<span></span></b>
	</p>
	<ol>
		<li class="MsoNormal">
			本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的。 <span></span>
		</li>
		<li class="MsoNormal">
			用户出于自愿而使用本软件，您必须了解使用本软件的风险，在尚未购买产品技术服务之前，我们不承诺提供任何形式的技术支持、使用担保，也不承担任何因使用本软件而产生问题的相关责任。 <span></span>
		</li>
		<li class="MsoNormal">
			Oray不对使用本软件构建的网站中或者论坛中的文章或信息承担责任，全部责任由您自行承担。 <span></span>
		</li>
		<li class="MsoNormal">
			Oray对所提供的软件和服务之及时性、安全性、准确性不作担保，由于不可抗力因素、<span>Oray</span>无法控制的因素（包括黑客攻击、停断电等）等造成软件使用和服务中止或终止，而给您造成损失的，您同意放弃追究<span>Oray</span>责任的全部权利。 <span></span>
		</li>
		<li class="MsoNormal">
			Oray特别提请您注意，<span>Oray</span>为了保障公司业务发展和调整的自主权，<span>Oray</span>拥有随时经或未经事先通知而修改服务内容、中止或终止部分或全部软件使用和服务的权利，修改会公布于<span>Oray</span>网站相关页面上，一经公布视为通知。 <span>Oray</span>行使修改或中止、终止部分或全部软件使用和服务的权利而造成损失的，<span>Oray</span>不需对您或任何第三方负责。 <span></span>
		</li>
	</ol>
	<p class="MsoNormal">
		有关图度最终用户授权协议、商业授权与技术服务的详细内容，均由<span>Oray</span>独家提供。<span>Oray</span>拥有在不事先通知的情况下，修改授权协议和服务价目表的权利，修改后的协议或价目表对自改变之日起的新授权用户生效。<span></span>
	</p>
	<p class="MsoNormal">
		一旦您开始安装图度，即被视为完全理解并接受本协议的各项条款，在享有上述条款授予的权利的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权力。<span></span>
	</p>
	<p class="MsoNormal">
		本许可协议条款的解释，效力及纠纷的解决，适用于中华人民共和国大陆法律。<span></span>
	</p>
若您和<span>Oray</span>之间发生任何纠纷或争议，首先应友好协商解决，协商不成的，您在此完全同意将纠纷或争议提交<span>Oray</span>所在地人民法院管辖。<span>Oray</span>拥有对以上各项条款内容的解释权及修改权。

                </div>
            </div>
            <label><input name="protocol" type="checkbox" value="1">已阅读并同意《图度云办公系统服务协议》</label>
            <div align="center" style="margin-top:25px;"><input type="hidden" name="step" value="{$step}" /><input id="submit" class="btn-big" type="submit" value="开始安装图度"></div>
        </form>
    </section>
</div>
<script type="text/javascript">
function checked() {
    if (typeof $('input[name="protocol"]:checked').val() == 'undefined') {
        return Install.showMessage('请阅读并同意《图度云办公系统服务协议》', false);
    }

    return true;
}
</script>
</body>
</html>