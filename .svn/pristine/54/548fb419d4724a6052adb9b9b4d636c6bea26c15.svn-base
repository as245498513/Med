<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<!-- Set render engine for 360 browser -->
	<meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- HTML5 shim for IE8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <![endif]-->

	<link href="/public/simpleboot/themes/<?php echo C('SP_ADMIN_STYLE');?>/theme.min.css" rel="stylesheet">
    <link href="/public/simpleboot/css/simplebootadmin.css" rel="stylesheet">
    <link href="/public/js/artDialog/skins/default.css" rel="stylesheet" />
    <link href="/public/simpleboot/font-awesome/4.4.0/css/font-awesome.min.css"  rel="stylesheet" type="text/css">
    <style>
		form .input-order{margin-bottom: 0px;padding:3px;width:40px;}
		.table-actions{margin-top: 5px; margin-bottom: 5px;padding:0px;}
		.table-list{margin-bottom: 0px;}
	</style>
	<!--[if IE 7]>
	<link rel="stylesheet" href="/public/simpleboot/font-awesome/4.4.0/css/font-awesome-ie7.min.css">
	<![endif]-->
	<script type="text/javascript">
	//全局变量
	var GV = {
	    ROOT: "/",
	    WEB_ROOT: "/",
	    JS_ROOT: "public/js/",
	    APP:'<?php echo (MODULE_NAME); ?>'/*当前应用名*/
	};
	</script>
    <script src="/public/js/jquery.js"></script>
    <script src="/public/js/wind.js"></script>
    <script src="/public/simpleboot/bootstrap/js/bootstrap.min.js"></script>
    <script>
    	$(function(){
    		$("[data-toggle='tooltip']").tooltip();
    	});
    </script>
<?php if(APP_DEBUG): ?><style>
		#think_page_trace_open{
			z-index:9999;
		}
	</style><?php endif; ?>
</head>
<body>
	<div class="wrap js-check-wrap">
		<ul class="nav nav-tabs">
			<li class="active"><a href="<?php echo U('storage/index');?>"><?php echo L('ADMIN_STORAGE_INDEX');?></a></li>
		</ul>
		<form method="post" class="form-horizontal js-ajax-form" action="<?php echo U('storage/setting_post');?>">
			<?php $support_storages=array("Local"=>L('DEFAULT'),"Qiniu"=>L('QINIU')); ?>
			<select name="type">
				<?php if(is_array($support_storages)): foreach($support_storages as $key=>$vo): $type_selected=$type==$key?"selected":""; ?>
					<option value="<?php echo ($key); ?>"<?php echo ($type_selected); ?>><?php echo ($vo); ?></option><?php endforeach; endif; ?>
			</select>
			<div style="margin-top: 10px;">
				<ul class="nav nav-tabs">
					<li class="active"><a href="#qiniu-setting" data-toggle="tab"><?php echo L('QINIU');?></a></li>
					<li><a href="#qiniu-picture-protect" data-toggle="tab">原图保护设置</a></li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active" id="qiniu-setting">
						<div class="control-group">
							<label class="control-label">AccessKey</label>
							<div class="controls">
								<input type="text" name="Qiniu[accessKey]" value="<?php echo ($Qiniu["accessKey"]); ?>">
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">SecretKey</label>
							<div class="controls">
								<input type="text" name="Qiniu[secretKey]" value="<?php echo ($Qiniu["secretKey"]); ?>">
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">上传域名</label>
							<div class="controls">
								<input type="text" name="Qiniu[upHost]" value="<?php echo ($Qiniu["upHost"]); ?>">
								<span class="help-block">
									七牛不同存储区域上传域名不一样，根据您空间的存储区域,设置不同的域名；<br>
									华东：http://up.qiniu.com,华北：http://up-z1.qiniu.com;<br>
									默认为华东；
								</span>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">域名协议</label>
							<div class="controls">
								<select name="Qiniu[setting][protocol]">
									<option value="http">http</option>
									<?php if(($Qiniu["setting"]["protocol"]) == "https"): ?><option value="https" selected="selected">https</option>
									<?php else: ?>
									<option value="https">https</option><?php endif; ?> 
									
								</select>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label"><?php echo L('DOMAIN');?></label>
							<div class="controls">
								<input type="text" name="Qiniu[domain]" value="<?php echo ($Qiniu["domain"]); ?>">
							</div>
						</div>
						<div class="control-group">
							<label class="control-label"><?php echo L('BUCKET');?></label>
							<div class="controls">
								<input type="text" name="Qiniu[bucket]" value="<?php echo ($Qiniu["bucket"]); ?>">
							</div>
						</div>
						<div class="control-group">
							<label class="control-label"><?php echo L('GET_ACCESS_KEY');?></label>
							<div class="controls">
								<a href="https://portal.qiniu.com/signup?code=3lfihpz361o42" target="_blank"><?php echo L('GET_IT_NOW');?></a>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label"><?php echo L('QINIU_PROMOTION_CODE');?></label>
							<div class="controls">
								<a href="http://www.thinkcmf.com/topic/topic/index/id/103.html" target="_blank">507670e8</a>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">七牛帮助文档</label>
							<div class="controls">
								<a href="http://www.thinkcmf.com/qiniu/help.html" target="_blank">立即访问</a>
							</div>
						</div>
					</div>
					<div class="tab-pane" id="qiniu-picture-protect">
						<div class="control-group">
							<label class="control-label">开启原图保护</label>
							<div class="controls">
								<select name="Qiniu[setting][enable_picture_protect]">
									<option value="0">关闭</option>
									<?php if(empty($Qiniu["setting"]["enable_picture_protect"])): ?><option value="1">开启</option>
									<?php else: ?>
									<option value="1" selected="selected">开启</option><?php endif; ?> 
									
								</select>
							</div>
						</div>
						
						<div class="control-group">
							<label class="control-label">样式分隔符</label>
							<div class="controls">
								<input type="text" name="Qiniu[setting][style_separator]" value="<?php echo ((isset($Qiniu["setting"]["style_separator"]) && ($Qiniu["setting"]["style_separator"] !== ""))?($Qiniu["setting"]["style_separator"]):'!'); ?>">
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">样式-水印</label>
							<div class="controls">
								<input type="text" name="Qiniu[setting][styles][watermark]" value="<?php echo ((isset($Qiniu["setting"]["styles"]["watermark"]) && ($Qiniu["setting"]["styles"]["watermark"] !== ""))?($Qiniu["setting"]["styles"]["watermark"]):'watermark'); ?>">
								<span class="help-block">
									请到七牛存储空间->图片样式：添加此样式名称，并进行相应设置
								</span>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">样式-头像</label>
							<div class="controls">
								<input type="text" name="Qiniu[setting][styles][avatar]" value="<?php echo ((isset($Qiniu["setting"]["styles"]["watermark"]) && ($Qiniu["setting"]["styles"]["watermark"] !== ""))?($Qiniu["setting"]["styles"]["watermark"]):'avatar'); ?>">
								<span class="help-block">
									请到七牛存储空间->图片样式：添加此样式名称，并进行相应设置
									处理接口:<br>
									imageView2/1/w/100/h/100/interlace/0/q/100
								</span>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">样式-缩略图120x120</label>
							<div class="controls">
								<input type="text" name="Qiniu[setting][styles][thumbnail120x120]" value="<?php echo ((isset($Qiniu["setting"]["styles"]["thumbnail120x120"]) && ($Qiniu["setting"]["styles"]["thumbnail120x120"] !== ""))?($Qiniu["setting"]["styles"]["thumbnail120x120"]):'thumbnail120x120'); ?>">
								<span class="help-block">
								请到七牛存储空间->图片样式：添加此样式名称，并进行相应设置<br>
								处理接口:<br>
								imageView2/1/w/120/h/120/interlace/0/q/100
								</span>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">样式-缩略图300x300</label>
							<div class="controls">
								<input type="text" name="Qiniu[setting][styles][thumbnail300x300]" value="<?php echo ((isset($Qiniu["setting"]["styles"]["thumbnail300x300"]) && ($Qiniu["setting"]["styles"]["thumbnail300x300"] !== ""))?($Qiniu["setting"]["styles"]["thumbnail300x300"]):'thumbnail300x300'); ?>">
								<span class="help-block">
								请到七牛存储空间->图片样式：添加此样式名称，并进行相应设置<br>
								处理接口:<br>
								imageView2/1/w/300/h/300/interlace/0/q/100
								</span>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">样式-缩略图640x640</label>
							<div class="controls">
								<input type="text" name="Qiniu[setting][styles][thumbnail640x640]" value="<?php echo ((isset($Qiniu["setting"]["styles"]["thumbnail640x640"]) && ($Qiniu["setting"]["styles"]["thumbnail640x640"] !== ""))?($Qiniu["setting"]["styles"]["thumbnail640x640"]):'thumbnail640x640'); ?>">
								<span class="help-block">
								请到七牛存储空间->图片样式：添加此样式名称，并进行相应设置<br>
								处理接口:<br>
								imageView2/1/w/640/h/640/interlace/0/q/100
								</span>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">样式-缩略图1080x1080</label>
							<div class="controls">
								<input type="text" name="Qiniu[setting][styles][thumbnail1080x1080]" value="<?php echo ((isset($Qiniu["setting"]["styles"]["thumbnail1080x1080"]) && ($Qiniu["setting"]["styles"]["thumbnail1080x1080"] !== ""))?($Qiniu["setting"]["styles"]["thumbnail1080x1080"]):'thumbnail1080x1080'); ?>">
								<span class="help-block">
								请到七牛存储空间->图片样式：添加此样式名称，并进行相应设置<br>
								处理接口:<br>
								imageView2/1/w/1080/h/1080/interlace/0/q/100
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="form-actions">
				<button type="submit" class="btn btn-primary js-ajax-submit"><?php echo L('SAVE');?></button>
			</div>
		</form>
	</div>
	<script src="/public/js/common.js"></script>
</body>
</html>