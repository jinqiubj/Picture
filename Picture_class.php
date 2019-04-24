<?php

/**
 * 图片处理类库
 */
class Picture_class {

	/**
	 * 裁剪图片处理
	 *
	 * 图片裁剪的原理：
	 * 1、先建立一个和前端裁剪控件宽高相同的基础画板。
	 * 2、再把原图按原图自身的宽高比，以画板的宽或高为缩放基准等比缩放在画板上。
	 * 3、再按照前端控件传过来的裁切的宽高和相对于前端画板原点的坐标，在后端生成画板中裁切出图片。
	 * 4、再把裁剪出的图片按它自身的宽高比，以最终图像(200*200的图片)的宽或高为缩放基准等比缩放在最终图像上，且生成图像是透明背景。
	 * 
	 * @param  [type]  $tmpname       图片上传成功后的临时目录
	 * @param  [type]  $saveName      处理后的图片的保存路径和名称
	 * @param  integer $p_tmpWidth    画板宽
	 * @param  integer $p_tmpHeight   画板高
	 * @param  integer $p_cutWidth    裁剪/选区的宽
	 * @param  integer $p_cutHeight   裁剪/选区的高
	 * @param  integer $p_cutX        选区左上角的点相对于等比缩放后的图片的原点的横坐标
	 * @param  integer $p_cutY        选区左上角的点相对于等比缩放后的图片的原点的纵坐标
	 * @param  integer $p_finalWidth  最终图像的宽
	 * @param  integer $p_finalHeight 最终图像的高
	 * @return [type]                 [description]
	 */
	public function cutPicture($tmpname, $saveName, $p_tmpWidth = 400, $p_tmpHeight = 300, $p_cutWidth = 0, $p_cutHeight = 0, $p_cutX = 0, $p_cutY = 0, $p_finalWidth = 200, $p_finalHeight = 200) {

		// 原图信息
		$imgInfo   = getimagesize($tmpname);
		$imgWidth  = $imgReWidth =  $imgInfo['0'];
		$imgHeight = $imgReHeight = $imgInfo['1'];
		$imgMime   = $imgInfo['mime'];

		// 打开原图
		if ($imgMime == 'image/gif') {
			$imgSource = imagecreatefromgif($tmpname);
		} elseif ($imgMime == 'image/jpeg' || $imgMime == 'image/jpg') {
			$imgSource = imagecreatefromjpeg($tmpname);
		} elseif ($imgMime == 'image/png') {
			$imgSource = imagecreatefrompng($tmpname);
		}

		// 原图exif信息，判断是否需要旋转图片
		if (function_exists('exif_read_data')) {
			$exif = @exif_read_data($tmpname);
			if (isset($exif['Orientation'])) {
				if ($exif['Orientation'] == 1) {
					// 不需要旋转
				} elseif ($exif['Orientation'] == 6) {
					// 顺时针旋转90度
					$imgSource = imagerotate($imgSource, -90, 0);
					// 图片旋转90度以后，原图宽和高要调换
					$imgWidth  = $imgReWidth = $imgInfo['1'];
					$imgHeight = $imgReHeight= $imgInfo['0'];
				} elseif ($exif['Orientation'] == 8) {
					// 逆时针旋转90度
					$imgSource = imagerotate($imgSource, 90, 0);
					// 图片旋转90度以后，原图宽和高要调换
					$imgWidth  = $imgReWidth = $imgInfo['1'];
					$imgHeight = $imgReHeight= $imgInfo['0'];
				} elseif ($exif['Orientation'] == 3) {
					// 逆时针旋转180度
					$imgSource = imagerotate($imgSource, 180, 0);
				}
			}
		}

		// 计算等比缩放到画板上时，原图的宽高
		$tmpWidth     = $p_tmpWidth;  			 // 画板的宽高(与前端裁剪控件的画板宽高相同)
		$tmpHeight    = $p_tmpHeight; 			 // 画板的宽高(与前端裁剪控件的画板宽高相同)
		$ratio_1      = $imgWidth / $imgHeight;  // 原图宽高比
		$ratio_2      = $tmpWidth / $tmpHeight;  // 画板宽高比
		if ($ratio_1  >= $ratio_2) {
			$imgWidth = $tmpWidth; 			 	 // 以画板宽为基准，计算等比缩放到画板上的原图的高
			$imgHeight= $imgWidth / $ratio_1;
		} else { 
			$imgHeight= $tmpHeight;		 	 	 // 以画板高为基准，计算等比缩放到画板上的原图的宽
			$imgWidth = $imgHeight * $ratio_1;
		}

		// 创建画板
		$im_2      = imagecreatetruecolor($tmpWidth, $tmpHeight);
		// 将原图等比缩放到画板$im_2上，然后再在画板上裁剪需要的图片。注意：这种缩放并不一定完全占用整个画板的区域。
		imagecopyresampled($im_2, $imgSource, 0, 0, 0, 0, $imgWidth, $imgHeight, $imgReWidth, $imgReHeight);

		// 要裁剪的宽高(选区的宽高)
		$cutWidth  = $p_cutWidth;
		$cutHeight = $p_cutHeight;

		// 如果没有选择要裁剪的区域，默认把等比缩放后的整张图片作为选区，即裁剪整张图片
		if (empty($cutWidth) || empty($cutHeight)) {
			$cutWidth = $imgWidth;
			$cutHeight= $imgHeight;
		}

		// 创建与选区(要裁剪的图片)宽高相同的底版
		$im_3      = imagecreatetruecolor($cutWidth, $cutHeight);

		// 选区左上角的点相对于等比缩放后的图片的原点的坐标
		$cutX = $p_cutX;
		$cutY = $p_cutY;

		// 从画板中按坐标、裁切的宽高裁剪图片，并将裁剪的图片偏复制到$im_3上
		imagecopy($im_3, $im_2, 0, 0, $cutX, $cutY, $cutWidth, $cutHeight);

		// 最终图像的宽高
		$finalWidth  = $p_finalWidth;
		$finalHeight = $p_finalHeight;

		// 计算等比缩放到最终上图像时，裁切的图片的宽高
		$cutRatio    = $cutWidth / $cutHeight;
		$finalRatio  = $finalWidth / $finalHeight;
		if ($cutRatio >= $finalRatio) { 
			$finalCutWidth = $finalWidth; // 以最终图像的宽为基准，计算等比缩放到最终图像上的裁切图的高
			$finalCutHeight= $finalCutWidth / $cutRatio;
		} else {
			$finalCutHeight= $finalHeight;// 以最终图像的高为基准，计算等比缩放到最终图像上的裁切图的宽
			$finalCutWidth = $finalHeight * $cutRatio;
		}

		// 居中的坐标值
		$finalX = ($finalWidth  - $finalCutWidth) / 2;
        $finalY = ($finalHeight - $finalCutHeight) / 2;

        // 最终图像
		$im_final = imagecreatetruecolor($finalWidth, $finalHeight);

		// 设置透明背景色，使$im_final底版透明
		$bgColor  = imagecolorallocatealpha($im_final, 0, 0, 0, 127);
		imagealphablending($im_final, false);
		imagefill($im_final, 0, 0, $bgColor);
		imagesavealpha($im_final, true);

		// 将从画板中裁切的图片居中并等比缩放到(默认200*200)的图像中
		imagecopyresampled($im_final, $im_3, $finalX, $finalY, 0, 0, $finalCutWidth, $finalCutHeight, $cutWidth, $cutHeight);

		header('Content-Type: image/png');
		imagepng($im_final, $saveName);

		imagedestroy($imgSource);
		imagedestroy($im_2);
		imagedestroy($im_3);
		imagedestroy($im_final);

		// 删除临时目录中的文件
		unlink($tmpname);

		return true;
	}
}