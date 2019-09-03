<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class ImportFarService
{
    public function getList()
    {
        $list = [];
        $category1 = 'news';
        $category1_id = '172662';
        $url1 = 'https://healthmedia.com.tw/xml/nownews1.xml';

        $category2 = 'mind';
        $category2_id = '172661';
        $url2 = 'https://healthmedia.com.tw/xml/nownews2.xml';

        $category3 = 'pandora';
        $category3_id = '172663';
        $url3 = 'https://healthmedia.com.tw/xml/nownews3.xml';

        $category4 = 'frontline';
        $category4_id = '172664';
        $url4 = 'https://healthmedia.com.tw/xml/nownews4.xml';

        $list['url'] = [
            $category1 => $url1,
            $category2 => $url2,
            $category3 => $url3,
            $category4 => $url4,
        ];

        $list['cat'] = [
            $category1 => $category1_id,
            $category2 => $category2_id,
            $category3 => $category3_id,
            $category4 => $category4_id,
        ];

        return $list;
    }

    public function farxmlParser($import)
    {
        $list = $this->getList();
        $rn = "<br>\r\n";
        foreach ($list['url'] as $rss_name => $rss_url) {
            $ap_news = file_get_contents("$rss_url");
            $cat_id = $list['cat']["$rss_name"];
            $xml = simplexml_load_string($ap_news, 'SimpleXMLElement', LIBXML_NOCDATA);
            if (!$xml) {
                echo "Error create xml object" . $rn;
                die();
            }
            $content = $xml->channel->item;
            foreach ($content as $value) {
                echo "feed start" . $rn;
                $contentTitle = $value->title;
                $contentDescription = $value->description;
                $contentGuid = $value->guid;
                $insert = 0;
                if ($contentTitle == "") {
                    continue;
                }

                $sqlquery = DB::select("select * from far_feed where guid='$contentGuid'");

                if ($sqlquery) {
                    echo "Feed exist" . $rn;
                    continue;
                }

                $pubdate = $value->pubDate;
                $pubTime = strtotime($pubdate . "+8 hour");
                $prevTime = strtotime("-1 day");
                $pubdateFormat = date("Y-m-d H:i:s", $pubTime);
                if ($pubTime < $prevTime) {
                    echo "Out of times" . $rn;
                    continue;
                }
                $contentDescription = preg_replace('/style\s*=\s*(\'|").+(\'|")/i', '', $contentDescription);
                //parser description
                preg_match_all("(<p[^>]*>.*?</p>)i", $contentDescription, $p);

//				echo $contentDescription.$rn;

//            			foreach($p as $resDescription){
//                  			$resDescription = implode("",$resDescription);
//            			}
//            			$resDescription = str_replace('"', '\"', $resDescription);
//				echo $resDescription.$rn;

                $imgUrl = $value->featuredImage ?? null;
                $imageAlt = "▲ （圖／遠大）";

                if ($imgUrl == null) {
                    $wp_post = "wp post create --allow-root --path=\"/var/www/html\" --post_type=post --post_author=164 --post_category=$cat_id --post_date=\"" . $pubdateFormat . "\" --meta_input='{\"byline\":\"NOW健康\"}' --post_title=\"" . htmlspecialchars($contentTitle, ENT_QUOTES) . "\" --post_status=publish --post_content=\"" . $contentDescription . "\" --porcelain";
                    $createPost = shell_exec("$wp_post");
                    if ($createPost != "") {
                        DB::insert('insert into far_feed (guid) values (?)', [$contentGuid]);
                        $insert++;
                    }
                } else {
                    $photoFile = file_get_contents($imgUrl);
                    $filename = md5($contentGuid) . ".jpg";
                    file_put_contents("/var/www/html/rssFeed/far_img/$filename", $photoFile);
                    $wp_post = "wp post create --allow-root --path=\"/var/www/html\" --post_type=post --post_author=164 --post_category=$cat_id --post_date=\"" . $pubdateFormat . "\" --meta_input='{\"byline\":\"NOW健康\"}' --post_title=\"" . htmlspecialchars($contentTitle, ENT_QUOTES) . "\" --post_status=publish --post_content=\"" . $contentDescription . "\" --porcelain";
                    $createPost = shell_exec("$wp_post");
                    $wp_media = "wp media import '/var/www/html/rssFeed/far_img/$filename' --allow-root --path=\"/var/www/html\" --title=\"" . $imageAlt . "\" --featured_image --post_id=" . $createPost;
                    $createMedia = shell_exec("$wp_media");
                    if ($createPost != "" || $createMedia != "") {
                        DB::insert('insert into far_feed (guid) values (?)', [$contentGuid]);
                        if (file_exists("/var/www/html/rssFeed/far_img/$filename")) {
                            unlink("/var/www/html/rssFeed/far_img/$filename");
                        }
                        $insert++;
                    }
                }


                if ($insert == '1') {
                    echo "insert success";
                } else {
                    echo "insert failed";
                }
            }
        }
    }
}
