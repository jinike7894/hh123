-- 替换苹果cms的影视图片地址，播放地址也是这样的。
update mac_vod set vod_pic = REPLACE(vod_pic,'老地址','新地址');