<?php
/**
 * Created by PhpStorm.
 * User: cuijianguo
 * Date: 18/4/11
 * Time: 下午3:33
 */

namespace App\Observers;

use App\Models\Link;
use Illuminate\Support\Facades\Cache;

class LinkObserver
{
    // 在保存时清空 cache_key 对应的缓存
    public function saved ( Link $link )
    {
        Cache::forget ( $link->cache_key );
    }
}