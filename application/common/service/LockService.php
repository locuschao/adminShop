<?php
/**
 *缓存锁类
 */
namespace app\common\service;
class LockService extends BaseService{

    //加锁
    public function set($key,$expTime)
    {
        //初步加锁
        $isLock = $this->redisCache->setnx($key,time()+$expTime);
        if($isLock)
        {
            return true;
        }
        else
        {
            //加锁失败的情况下。判断锁是否已经存在，如果锁存在切已经过期，那么删除锁。进行重新加锁
            $val = $this->redisCache->get($key);
            if($val&&$val<time())
            {
                $this->del($key);
            }
            return  $this->redisCache->setnx($key,time()+$expTime);
        }
    }

    /**
     * @param $key 解锁
     */
    public function del($key)
    {
        $this->redisCache->del($key);
    }


}