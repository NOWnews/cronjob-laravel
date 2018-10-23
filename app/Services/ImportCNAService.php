<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class ImportCNAService
{
	public function getList(){
		$list = array();
		$list = array(
                'business' => [
                    'image'=>['2952523'],
                    'url'=>[
                        "http://rss.cna.com.tw/client/nownews/cfp/article_feed_business_cn.xml",
                        "http://rss.cna.com.tw/client/nownews/cfp/article_feed_business_int.xml",
                        "http://rss.cna.com.tw/client/nownews/cfp/article_feed_business_tw.xml"
                        ]
                    ],
                'china' => [
                    'image'=>['2952517'],
                    'url'=>[
                        "http://rss.cna.com.tw/client/nownews/cfp/article_feed_china.xml"
                        ]
                    ],
                'health' => [
                    'image'=>['2952524'],
                    'url'=>[
                        "http://rss.cna.com.tw/client/nownews/cfp/article_feed_health.xml"
                        ]
                    ],
                    'int' => [
                        'image'=>['2952525'],
                        'url'=>[
                        "http://rss.cna.com.tw/client/nownews/cfp/article_feed_int.xml"
                        ]
                    ],
                    'life' => [
                        'image'=>['2952515'],
                        'url'=>[
                        "http://rss.cna.com.tw/client/nownews/cfp/article_feed_life.xml"
                        ]
                    ],
                    'local' => [
                        'image'=>['2952516'],
                        'url'=>[
                        "http://rss.cna.com.tw/client/nownews/cfp/article_feed_local.xml"
                        ]
                    ],
                    'politics' => [
                        'image'=>['2952519'],
                        'url'=>[
                        "http://rss.cna.com.tw/client/nownews/cfp/article_feed_politics.xml"
                        ]
                    ],
                    'society' => [
                        'image'=>['2952518'],
                        'url'=>[
                        "http://rss.cna.com.tw/client/nownews/cfp/article_feed_society.xml"
                        ]
                    ],
                    'sports' => [
                        'image'=>['2952526'],
                        'url'=>[
                        "http://rss.cna.com.tw/client/nownews/cfp/article_feed_sports_cn.xml",
                        "http://rss.cna.com.tw/client/nownews/cfp/article_feed_sports_int.xml",
                        "http://rss.cna.com.tw/client/nownews/cfp/article_feed_sports_tw.xml"
                        ]
                    ],
                    'stars' => [
                        'image'=>['2952521'],
                        'url'=>[
                        "http://rss.cna.com.tw/client/nownews/cfp/article_feed_stars.xml"
                        ]
                    ],
                    'tech' => [
                        'image'=>['2952520'],
                        'url'=>[
                        "http://rss.cna.com.tw/client/nownews/cfp/article_feed_tech.xml"
                        ]
                    ],
			);
		return $list;
	}
	public function cnaxmlparser ($import) {
		$cna_list = $this->getList();
		$rn = "<br>\r\n";
		echo "------------- Start Import 中央社 RSS Feed -------------".$rn;
		foreach ($cna_list as $feedsCate => $feedsParam) {
			foreach ($feedsParam['url'] as $feedsUrl) {
				$cna_xml = file_get_contents("$feedsUrl");
				$xml = simplexml_load_string($cna_xml, 'SimpleXMLElement', LIBXML_NOCDATA);
				if(!$xml){
					echo "Error: Cannot create object".$rn;
					die();
				}
				echo "------------- Start Import 中央社 ".$feedsCate." RSS Feed -------------".$rn;
				$content = $xml->NewsItem;
				foreach ($content as $value) {
					// $regexLink = '/^(http|https):\/\/news.cna.com\/news\/id\/[0-9]+/';
					// preg_match($regexLink, $value->link , $resultLink);
					// if(empty($resultLink)){
					// 	echo "Error: Cannot parser link".$rn;
					// 	continue;
					// }
					$uniqKey = $value->NewsComponent->NewsComponent->attributes()['Duid'];
					echo $uniqKey.$rn;
					$sqlquery = DB::select("select * from cna_feed where guid='$uniqKey'");
					if($sqlquery){
						echo "Feed exist".$rn;
						continue;
					}

					$pubdate = $value->NewsComponent->NewsComponent->DateLine;
					$pubTime =  strtotime($pubdate."+8 hour");
					$prevTime = strtotime("-1 hour");
					$pubdateFormat = date("Y-m-d H:i:s",$pubTime);

					if($pubTime < $prevTime){
						echo "Out of times".$rn;
						continue;
					}

					//中央社無圖片
					// if(isset($value->children( 'media', True )->keywords)){
					// 	$keywords = $value->children( 'media', True )->keywords;
					// }else{
					// 	$keywords = "headline";
					// }


					// $hasImg = false;
					// $aliveImg = false;
					// $checkImg = false;
					// if(!isset($value->children( 'media', True )->content)){
					// 	$cnaImagesObjectIds = [
					// 		'3238251',
					// 		'3238226',
					// 		'3238221',
					// 		'3238217'
					// 	];
					// 	$randomDefaultImageId = $cnaImagesObjectIds[array_rand($cnaImagesObjectIds, 1)];
					// 	echo $randomDefaultImageId;
					// }else{
					// 	$hasImg = true;
					// 	$imageUrl = $value->children( 'media', True )->content->attributes()['url'];
					// 	$imageAlt = "▲ ".$value->children( 'media', True )->content->attributes()['caption'];
					// 	if(!$imageAlt){
					// 		$imageAlt = "▲ （圖／中央社）";
					// 	}

					// 	$code = 'wget --spider -S "'.$imageUrl.'" 2>&1 | grep "HTTP/" | awk \'{print $2}\'';
					// 	$status = exec("$code");

					// 	if($status == '200'){
					// 		$aliveImg = true;
					// 		$photofile = file_get_contents("$imageUrl");
					// 		$filename = md5($uniqKey).".jpg";
					// 		file_put_contents("/var/www/html/rssFeed/cna_img/$filename", $photofile);
					// 	}
					// 	echo $filename;
					// 	echo $status."==".$rn;
					// 	//break;
					// }

					$title = mb_substr($value->NewsComponent->NewsComponent->NewsComponent->NewsLines->HeadLine, 0, 26, 'utf8');
					$key = 'body.content';
					$description_content = null;
					$description = $value->NewsComponent->NewsComponent->NewsComponent->ContentItem->DataContent->nitf->body->$key->p;
					foreach ($description as $value){
						$description_content .= "<p>".htmlspecialchars($value,ENT_QUOTES)."</p>";
					}
					//echo $description_content.$rn;


					$wp_post = "wp post create --allow-root --path=\"/var/www/html\" --post_type=post --post_author=4 --post_category=14 --post_date=\"".$pubdateFormat."\" --meta_input='{\"byline\":\"中央社\"}' --post_title=\"".htmlspecialchars($title,ENT_QUOTES)."\" --post_status=publish --post_content=\"".$description_content."\" --porcelain";
					$createPost = shell_exec("$wp_post");
					if($createPost){
						DB::insert('insert into cna_feed (guid) values (?)', [$uniqKey]);
						echo "------------------中央社--------------------".$rn;
						echo "收錄新聞: ".$title.$rn;
						echo "新聞連結: ".$uniqKey.$rn;
						echo "新聞識別唯一值: ".$uniqKey.$rn;
						echo "--------------------------------------------".$rn;
					}else{
						echo "Error: Cannot create post";
						continue;
					}

					//中央社無圖片
					// if($hasImg && $aliveImg){
					// 	$wp_media = "wp media import '/var/www/html/rssFeed/cna_img/$filename' --allow-root --path=\"/var/www/html\" --title=\"".$imageAlt."\" --caption=\"".$imageAlt."\" --alt=\"".$imageAlt."\" --featured_image --post_id=".$createPost;
					// 	$createMedia = shell_exec("$wp_media");
					// 	echo $createMedia.$rn;
					// 	$checkImg = true;

					// 	if(file_exists("/var/www/html/rssFeed/cna_img/$filename")){
					// 		unlink("/var/www/html/rssFeed/cna_img/$filename");
					// 	}
					// }else{
					// 	$wp_post_meta = "wp post meta update " .$createPost . " _thumbnail_id $randomDefaultImageId --allow-root --path=\"/var/www/html\"";
					// 	$updatePostMeta = exec(escapeshellcmd("$wp_post_meta"));
					// 	echo $updatePostMeta.$rn;
					// 	$checkImg = true;
					// }

					$cnaImagesObjectId = $feedsParam['image'][0];
					$cnaImagesObjectIds = [
						"$cnaImagesObjectId"
					];
					$randomDefaultImageId = $cnaImagesObjectIds[array_rand($cnaImagesObjectIds, 1)];
					echo $randomDefaultImageId.$rn;
					$wp_post_meta = "wp post meta update " .$createPost . " _thumbnail_id $randomDefaultImageId --allow-root --path=\"/var/www/html\"";
					$updatePostMeta = exec(escapeshellcmd("$wp_post_meta"));
					// echo "Error: Cannot create media".$rn;
					// continue;

				}
			}
		}
		echo "------------- Finish Import 中央社 RSS Feed -------------".$rn;
    }
}
