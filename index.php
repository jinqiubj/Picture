<?php
	if (!empty($_FILES)) {
		
		// 最大允许上传的图片大小(4M)
		$imgMaxSize = 4194304;
	
		if ($_FILES["cutImg"]["error"]) {
			echo '上传图片出错：' . $_FILES["cutImg"]["error"];
			exit;
		}
		if (!is_uploaded_file($_FILES['cutImg']['tmp_name'])) {
			echo '非法操作!';
			exit;
		}

		// 文件大小
		$filesize = $_FILES['cutImg']['size'];
		// 临时文件存放目录
		$tmpname  = $_FILES['cutImg']['tmp_name'];
		// 判断上传的图片是否符合要求
		$filetype = checkFileType($tmpname);
		if ($filetype === false) {
			echo '只能上传jpg/jpeg、png、gif类型的图片';
			exit;
		}
		if ($filesize > $imgMaxSize) {
			echo '只允许上传4M以内的图片';
			exit;
		}
		
		$saveDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . date ('Ymd', time()) . DIRECTORY_SEPARATOR;

		if ( ! is_dir($saveDir)) { // 用于生产环境代码中时，注意权限
			if ( ! mkdir($saveDir, 0777, true)) {
				echo '上传目录创建失败';
				exit;
			}
		}

		$saveName= $saveDir . time() . '_' . mt_rand(1000, 9999) . '.png';

		// 引用图片处理类库
		require_once 'picture_class.php';
		$picture = new Picture_class();
		
		$finalWidth  = 200;
		$finalHeight = 200;
		$rs = $picture->cutPicture($tmpname, $saveName, 400, 300, 0, 0, 0, 0, $finalWidth, $finalHeight);
		if ($rs !== true) {
			echo '图片裁剪失败';
			exit;
		}
		
		$imgVisitUrl = str_replace(dirname(__FILE__), '', $saveName);
		$imgVisitUrl = str_replace('\\', '/', $imgVisitUrl);
			
		header("Location: /?finalWidth=$finalWidth&finalHeight=$finalHeight&filename=$imgVisitUrl");
		exit;
		
	}
	
	$filename    = isset($_GET['filename']) ? $_GET['filename'] : '';
	
	/**
	 * 读取上传图片前2个字节，判断文件类型
	 * @param  [type] $filename [description]
	 * @return [type]           [description]
	 */
	function checkFileType($filename){

		$handle   = fopen($filename, 'rb');
		// 只读2字节
		$binaryStr= fread($handle, 2);
		fclose($handle);

		$strInfo  = unpack("c2chars", $binaryStr);
		$typeCode = intval($strInfo['chars1'] . $strInfo['chars2']);

		if ($typeCode == 255216 || ($strInfo['chars1']=='-1' && $strInfo['chars2']=='-40')) {
			return 'image/jpg';
		} elseif ($typeCode == 7173) {
			return 'image/gif';
		} elseif ($typeCode == 13780 || ($strInfo['chars1']=='-119' && $strInfo['chars2']=='80')) {
			return 'image/png';
		} else {
			return false;
		}
	}
?>
<!DOCTYPE html>
<html lang="zh-CN">
	<head>
    	<meta charset="utf-8">
    	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    	<meta name="viewport" content="width=device-width, initial-scale=1">
    	<meta name="description" content="">
	    <meta name="author" content="">
	    <link rel="icon" href="/favicon.ico">

	    <title>图片处理Demo</title>

		<link rel="stylesheet" href="/static/css/bootstrap.css"/>
		<script src="/static/js/jquery.min.js"></script>
		<script src="/static/js/bootstrap.min.js"></script>
		<script src="/static/js/jquery-3.3.1.min.js" ></script>
	</head>

	<style type="text/css">
		body { padding-top: 70px; }
	</style>
	
	<body>
		<div class="container">
			<nav class="navbar navbar-inverse navbar-fixed-top">
				<div class="container">
					<div class="navbar-header">
						<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
							<span class="sr-only">我爱学Web！</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
						<a class="navbar-brand" href="/">我爱学Web！</a>
					</div>
					<div id="navbar" class="navbar-collapse collapse">
						<ul class="nav navbar-nav">
							<li  ><a href="/">首页</a></li>
						</ul>
					</div>
				</div>
			</nav>

			<div class="row">
				<div class="page-header" style="margin-left: 16px;">
					<h3>裁剪图片Demo</h3>
				</div>
				<div class="col-md-12">
					<form id="menu_form" action="" enctype="multipart/form-data" method="post" class="form-horizontal" style="margin-top: 30px;">
						<div class="box-body">
							<div class="form-group">
								<label for="exampleInputFile" class="col-sm-2 control-label">上传图片：</label>
								<div class="col-sm-9">
									<input type="file" name="cutImg" id="exampleInputFile">
								</div>
							</div>
							<div class="form-group">
								<label for="exampleInputFile" class="col-sm-2 control-label">生成结果：</label>
								<div class="col-sm-9">
									<img width="<?php echo $finalWidth;?>" height="<?php echo $finalHeight;?>" src="<?php echo $filename?>" />
								</div>
							</div>
						</div>
						<div class="box-footer text-center">
							<button type="submit" class="btn btn-default">提交</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</body>
</html>