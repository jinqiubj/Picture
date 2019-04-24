<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 图片处理
 */
class Picture_demo {

	// 最大允许上传的图片大小(4M)
	private $imgMaxSize = 4194304;

	/**
	 * 裁剪图片处理
	 * @return [type] [description]
	 */
	public function uploadImg_2() {

		if (!empty($_FILES)) {
			if ($_FILES["cutImg"]["error"]) {
				$this->error_message('上传图片出错：' . $_FILES["cutImg"]["error"]);
			}
			if (!is_uploaded_file($_FILES['cutImg']['tmp_name'])) {
				$this->error_message('非法操作!');
			}

			// 文件大小
			$filesize = $_FILES['cutImg']['size'];
			// 临时文件存放目录
			$tmpname  = $_FILES['cutImg']['tmp_name'];
			// 判断上传的图片是否符合要求
			$filetype = $this->checkFileType($tmpname);
			if ($filetype === false) {
				$this->error_message('只能上传jpg/jpeg、png、gif类型的图片');
			}
			if ($filesize > $this->imgMaxSize) {
				$this->error_message('只允许上传4M以内的图片');
			}


			$saveDir = dirname(APPPATH) . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'index' . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . date ('Ymd', time()) . DIRECTORY_SEPARATOR;

			if ( ! is_dir($saveDir)) {
				if ( ! mkdir($saveDir, 0777, true)) {
					$this->error_message('上传目录创建失败');
				}
			}

			$saveName= $saveDir . time() . '_' . mt_rand(1000, 9999) . '.png';

			// 引用图片处理类库
			$this->load->library('picture_class', '', 'picture');
			$rs = $this->picture->cutPicture($tmpname, $saveName, 400, 300, 0, 0, 0, 0, 200, 200);
			if ($rs !== true) {
				$this->error_message('图片裁剪失败');
			}
			
			echo '裁切成功';
			exit;
			
		} else {
			$this->error_message('请上传要裁剪的图片');
		}
	}
	
	/**
	 * 统一错误提示
	 * @param  string $msg [description]
	 * @return [type]      [description]
	 */
	private function error_message($msg = '') {
		
		echo $msg;

		exit;
	}

	/**
	 * 读取上传图片前2个字节，判断文件类型
	 * @param  [type] $filename [description]
	 * @return [type]           [description]
	 */
	private function checkFileType($filename){

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
}
