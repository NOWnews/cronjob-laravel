<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class ImportCNYESService
{
	public function cnyesxmlparser ($import) {
		$cnyes_xml = file_get_contents("https://service.cnyes.com/rss/v2/news?vendor=nownews");
		$rn = "<br>\r\n";
		$xml = simplexml_load_string($cnyes_xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		if(!$xml){
			echo "Error: Cannot create object".$rn;
			die();
		}
		echo "------------- Start Import 鉅亨網 RSS Feed -------------".$rn;
		$content = $xml->channel->item;
		foreach ($content as $value) {
			$regexLink = '/^(http|https):\/\/news.cnyes.com\/news\/id\/[0-9]+/';
			preg_match($regexLink, $value->link , $resultLink);
			if(empty($resultLink)){
				echo "Error: Cannot parser link".$rn;
				continue;
			}
			$uniqKey = $value->guid;
			echo $uniqKey.$rn;
			$sqlquery = DB::select("select * from cnyes_feed where guid='$uniqKey'");
			if($sqlquery){
				echo "Feed exist".$rn;
				continue;
			}

			$pubdate = $value->pubDate;
			$pubTime =  strtotime($pubdate);
			$prevTime = strtotime("-1 day");
			$pubdateFormat = date_create($pubdate);
			$pubdateFormat = date_format($pubdateFormat,'Y-m-d H:i:s');

			if($pubTime < $prevTime){
				echo "Out of times".$rn;
				continue;
			}
			
			if(isset($value->children( 'media', True )->keywords)){
                                $keywords = $value->children( 'media', True )->keywords;
                        }else{
                                $keywords = "headline";//鉅亨文章共有的關鍵字
                        }

			$description = str_replace(array('\n','"'), array('','\"'), $value->children( 'content', True )->encoded);

			$hasImg = false;
			$aliveImg = false;
			$checkImg = false;
			if(!isset($value->children( 'media', True )->content)){
				$cnyesImagesObjectIds = [
					'2952514',
                                        '2952513',
                                        '2952512'
				];
				$randomDefaultImageId = $cnyesImagesObjectIds[array_rand($cnyesImagesObjectIds, 1)];
			}else{
				$hasImg = true;
				$imageUrl = $value->children( 'media', True )->content->attributes()['url'];
				$imageAlt = "▲ ".$value->children( 'media', True )->content->attributes()['caption'];
				if(!$imageAlt){
					$imageAlt = "▲ （圖／鉅亨網）";
				}

				$code = 'wget --spider -S "'.$imageUrl.'" 2>&1 | grep "HTTP/" | awk \'{print $2}\'';
				$status = exec("$code");

				if($status == '200'){
					$aliveImg = true;
					$photofile = file_get_contents("$imageUrl");
					$filename = md5($uniqKey).".jpg";
					file_put_contents("/var/www/html/rssFeed/cnyes_img/$filename", $photofile);
				}
				echo $filename;
				echo $status."==".$rn;
				//break;
			}

			$title = mb_substr($value->title, 0, 26, 'utf8');
			$link = "http://news.cnyes.com/?utm_medium=news&amp;utm_source=nownews";
			$description .= '更多精彩內容請至 《鉅亨網》 <a target=\"_blank\" href=\"'.$link.'\">連結>></a>';
			//echo $description.$rn;


			$wp_post = "wp post create --allow-root --path=\"/var/www/html\" --post_type=post --post_author=2 --post_category=13 --tags_input=$keywords --post_date=\"".$pubdateFormat."\" --meta_input='{\"byline\":\"鉅亨網\"}' --post_title=\"".htmlspecialchars($title,ENT_QUOTES)."\" --post_status=publish --post_content=\"".$description."\" --porcelain";
			$createPost = shell_exec("$wp_post");
			if($createPost){
				DB::insert('insert into cnyes_feed (guid) values (?)', [$uniqKey]);
				echo "------------------鉅亨網--------------------".$rn;
				echo "收錄新聞: ".$value->title.$rn;
				echo "新聞連結: ".$value->link.$rn;
				echo "新聞識別唯一值: ".$uniqKey.$rn;
				echo "--------------------------------------------".$rn;
			}else{
				echo "Error: Cannot create post";
				continue;
			}

			if($hasImg && $aliveImg){
				$wp_media = "wp media import '/var/www/html/rssFeed/cnyes_img/$filename' --allow-root --path=\"/var/www/html\" --title=\"".$imageAlt."\" --caption=\"".$imageAlt."\" --alt=\"".$imageAlt."\" --featured_image --post_id=".$createPost;
				$createMedia = shell_exec("$wp_media");
				echo $createMedia.$rn;
				$checkImg = true;

				if(file_exists("/var/www/html/rssFeed/cnyes_img/$filename")){
					unlink("/var/www/html/rssFeed/cnyes_img/$filename");
				}
			}else{
				$wp_post_meta = "wp post meta update " .$createPost . " _thumbnail_id $randomDefaultImageId --allow-root --path=\"/var/www/html\"";
				$updatePostMeta = exec(escapeshellcmd("$wp_post_meta"));
				echo $updatePostMeta.$rn;
				$checkImg = true;
			}

			if(!$checkImg){
				$cnyesImagesObjectIds = [
                                        '2952512',
                                        '2952513',
                                        '2952514'
				];
				$randomDefaultImageId = $cnyesImagesObjectIds[array_rand($cnyesImagesObjectIds, 1)];
				$wp_post_meta = "wp post meta update " .$createPost . " _thumbnail_id $randomDefaultImageId --allow-root --path=\"/var/www/html\"";
				$updatePostMeta = exec(escapeshellcmd("$wp_post_meta"));
				echo "Error: Cannot create media".$rn;
				continue;
			}

		}
	echo "------------- Finish Import 鉅亨網 RSS Feed -------------".$rn;
    }
}
