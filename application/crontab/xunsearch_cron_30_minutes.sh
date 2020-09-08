#!/bin/bash
#每30分钟执行1次
/usr/local/xunsearch/sdk/php/util/Indexer.php --rebuild --source=mysql://weiyu:weiyu12345@172.16.0.6/cc_shop --sql="select goods_id,goods_name,keywords,goods_remark,goods_content from cc_goods" --filter=debug --project=goods;

/usr/local/xunsearch/sdk/php/util/Indexer.php --rebuild --source=mysql://weiyu:weiyu12345@172.16.0.6/cc_shop --sql="select id,title from cc_admin_menu" --filter=debug --project=menu;

 /usr/local/xunsearch/sdk/php/util/Indexer.php --rebuild --source=mysql://weiyu:weiyu12345@172.16.0.6/cc_shop --sql="select id,title from cc_coupon" --filter=debug --project=coupon;

 /usr/local/xunsearch/sdk/php/util/Indexer.php --rebuild --source=mysql://weiyu:weiyu12345@172.16.0.6/cc_shop --sql="select id,title from cc_goods_activity" --filter=debug --project=goods_activity;

 /usr/local/xunsearch/sdk/php/util/Indexer.php --rebuild --source=mysql://weiyu:weiyu12345@172.16.0.6/cc_shop --sql="select id,username,platform,contact,responser,mobile from cc_merchants" --filter=debug --project=merchants;

 /usr/local/xunsearch/sdk/php/util/Indexer.php --rebuild --source=mysql://weiyu:weiyu12345@172.16.0.6/cc_shop --sql="select id,title from cc_gift_activity_list" --filter=debug --project=gift_activity;