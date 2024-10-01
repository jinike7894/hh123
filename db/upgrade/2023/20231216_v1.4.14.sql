-- 短视频把点赞数量为0的变为20-500之间的随机数
UPDATE mac_short_vod
SET likeCount = FLOOR(RAND() * (500 - 20 + 1) + 20),
    realLikeCount = FLOOR(RAND() * (500 - 20 + 1) + 20)
WHERE likeCount = 0;