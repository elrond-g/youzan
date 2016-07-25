<?php
use GuzzleHttp\Client;
class Youzan
{
    private static $start_shop_id = 1;
    private static $stop_shop_id = 10;
    private static $url = 'https://wap.koudaitong.com/v2/showcase/homepage';

    public static function run()
    {
      //$start_shop_id = static::start_shop_id;
      //$stop_shop_id = static::stop_shop_id;
      //$url = static::url;
        $start_shop_id = 1;
        $stop_shop_id = 10000;
        $url = 'https://wap.koudaitong.com/v2/showcase/homepage';
        $youzan = new Youzan;

        for($i = $start_shop_id; $i < $stop_shop_id; $i++)
        {

            $page = $youzan->get($url, ['kdt_id'=>$i]);

            if( strlen($page) < 20000 )
                continue;

            $youzan->store($i, $page);

            $weixin = @$youzan->getInfo($page);
            $youzan->save($i, $weixin);
        }
    }


    private $client = null;

    public function __construct()
    {
        $this->client = new Client;
    }

    public function get($url, $data)
    {
        $url = $url . '?' . http_build_query($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }



    public function store($key, $value)
    {
        $fileName = DATA_DIR . '/shopIndex/' . $key . '.html';
        file_put_contents($fileName, $value);
        return true;
    }

    public function save($key, $value)
    {
        $info = [
            'shop_id' => $key,
            'shop_info' => $value,
            ];

        $json = json_encode($info);
        echo $json  . "\n";

        return true;
    }

    public function getInfo($page)
    {
        $result = [];
        //获取店铺名称
        $pa = '%<p class="shop-name">(.*?)</p>%si';
        preg_match_all($pa,$page,$match);
        $result['shopName'] = trim($match[1][0]);

        //获取店铺微信号
        $pa = '%<p class="text-center weixin-no">(.*?)</p>%si';
        preg_match_all($pa,$page,$match);
        $result['weixin'] = isset($match[1][0]) ? $match[1][0] : null;;

        //微信二维码
      //$pa = '%<p class="text-center qr-code">(.*?)</p>%si';
      //preg_match_all($pa,$page,$match);
      //$result['weixin_img'] = isset($match[1][0]) ? $match[1][0] : null;;


        return $result;
    }


}

