<?php

namespace app\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ModifyPlatformJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $platform;
    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($tripartite_provider,$datas)
    {
        $this->platform = $tripartite_provider;
        $this->data = $datas;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->platform as $item){
//                dd($item);//provider_uniacid,domain
            $url = "{$item['domain']}/addons/yun_shop/api.php?i={$item['provider_uniacid']}&mid=0&type=5&shop_id=null&route=plugin.tripartite-provider.admin.tripartiteProvider.list.updatePlatform";
            // 提交推送请求
            $response = Curl::to($url)->withData(['data' => json_encode($this->data,1)])->asJsonResponse(true)->post();
        }
    }
}
