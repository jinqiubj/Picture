### 类库地址
https://github.com/jinqiubj/Picture

### 此类库满足以下需求
1. 将任意尺寸的图片等比缩放到任意尺寸的透明图片上，如下图是一张200*200的正方形图片：
![图片裁剪/裁切](https://github.com/jinqiubj/Picture/blob/master/img/02.png)
2. 在等比缩放的同时，此类库可根据图片的exif信息判断图片是否需要翻转.
3. 此类库设计的最终用途是实现图片裁剪，但是需要前端插件的配合，前端插件需要把选区的左上端点的坐标、选区的宽和高、原图，上传到服务端并调用类库即可，如下图：
![图片裁剪/裁切](https://github.com/jinqiubj/Picture/blob/master/img/01.png)

### 其他说明
1. 类库文件是Picture_class.php.
2. 本类库有完整Demo，把github仓库拉取下来，放在web目录下即可测试.
3. 在类Unix系统测试时，请赋予upload目录可写权限.