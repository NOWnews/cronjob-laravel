<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class ImportNEWTALKService
{
    public function newtalkxmlparser ($import) {
	$newtalk_xml = file_get_contents("https://newtalk.tw/rss_news_b2b.php?ch=NOENEWS&ccd=9bc34549d565d9505b287de0cd20ac77be1d3f2c");
	$rn = "<br>\r\n";
	$xml = simplexml_load_string($newtalk_xml, 'SimpleXMLElement', LIBXML_NOCDATA);
	if(!$xml){
		echo "Error: Cannot create object".$rn;
		die();
	}
	echo "------------- Start Import 新頭殼 RSS Feed -------------".$rn;
	$content = $xml->channel->item;
	foreach ($content as $value) {
		$regexLink = '/^(http|https):\/\/newtalk.tw\/news\/view\/([12]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]))/';
		preg_match($regexLink, $value->link , $resultLink);
		if(empty($resultLink)){
			echo "Error: Cannot parser link".$rn;
			continue;
		}
		$uniqKey = $value->guid;
		echo $uniqKey.$rn;
		$sqlquery = DB::select("select * from newtalk_feed where guid='$uniqKey'");
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

		$category = '123717';
		$newtalkImagesObjectId = '';
		if(isset($value->subcategory)){
			$subcategory = $value->subcategory;
                        if($subcategory == '國際'){$category .= ',123718,7';$newtalkImagesObjectId = '2952527';}
			else if($subcategory == '藝文'){$category .= ',123719,124246';$newtalkImagesObjectId = '2952531';}
			else if($subcategory == '電競'){$category .= ',123720,90';$newtalkImagesObjectId = '2952528';}
			else if($subcategory == '環保'){$category .= ',123721,124251';$newtalkImagesObjectId = '2952529';}
                }
		//if($uniqKey != 'http://newtalk.gpwb.gov.tw/IndexDetail.aspx?id=90274'){
                //        continue;
                //}

		$hasImg = false;
		$aliveImg = false;
		$checkImg = false;
		if(!isset($value->image)){
			$newtalkImagesObjectIds = [
				"$newtalkImagesObjectId"
			];
			$randomDefaultImageId = $newtalkImagesObjectIds[array_rand($newtalkImagesObjectIds, 1)];
		}else{
			$hasImg = true;
			$imageUrl = $value->image;
			$imageAlt = "▲ （圖／新頭殼）";

			$code = 'wget --spider -S "'.$imageUrl.'" 2>&1 | grep "HTTP/" | awk \'{print $2}\'';
			$status = exec("$code");
			if($status == '200'){
				$aliveImg = true;
				$photofile = file_get_contents("$imageUrl");
				$filename = md5($uniqKey).".jpg";
				file_put_contents("/var/www/html/rssFeed/newtalk_img/$filename", $photofile);
			}
			echo $filename;
			echo $status."==".$rn;
			//break;

		}

		$title = mb_substr($value->title, 0, 26, 'utf8');
		$description = str_replace('"', '\"', $value->description);
		echo $description.$rn;


		$wp_post = "wp post create --allow-root --path=\"/var/www/html\" --post_type=post --post_author=1004 --post_category=$category --post_date=\"".$pubdateFormat."\" --meta_input='{\"byline\":\"新頭殼\"}' --post_title=\"".htmlspecialchars($title,ENT_QUOTES)."\" --post_status=publish --post_content=\"".$description."\" --porcelain";
		$createPost = shell_exec("$wp_post");
		if($createPost){
			DB::insert('insert into newtalk_feed (guid) values (?)', [$uniqKey]);
			echo "------------------新頭殼--------------------".$rn;
                	echo "收錄新聞: ".$value->title.$rn;
                	echo "新聞連結: ".$value->link.$rn;
                	echo "新聞識別唯一值: ".$uniqKey.$rn;
                	echo "--------------------------------------------".$rn;
		}else{
			echo "Error: Cannot create post";
			continue;
		}

		if($hasImg && $aliveImg){
			$wp_media = "wp media import '/var/www/html/rssFeed/newtalk_img/$filename' --allow-root --path=\"/var/www/html\" --title=\"".$imageAlt."\" --caption=\"".$imageAlt."\" --alt=\"".$imageAlt."\" --featured_image --post_id=".$createPost;
			$createMedia = shell_exec("$wp_media");
			echo $createMedia.$rn;
			$checkImg = true;

			if(file_exists("/var/www/html/rssFeed/newtalk_img/$filename")){
				unlink("/var/www/html/rssFeed/newtalk_img/$filename");
			}
		}else{
			$wp_post_meta = "wp post meta update " .$createPost . " _thumbnail_id $randomDefaultImageId --allow-root --path=\"/var/www/html\"";
			$updatePostMeta = exec(escapeshellcmd("$wp_post_meta"));
			echo $updatePostMeta.$rn;
			$checkImg = true;
		}

		if(!$checkImg){
			$newtalkImagesObjectIds = [
                                "$newtalkImagesObjectId"
                        ];
                        $randomDefaultImageId = $newtalkImagesObjectIds[array_rand($newtalkImagesObjectIds, 1)];
                        $wp_post_meta = "wp post meta update " .$createPost . " _thumbnail_id $randomDefaultImageId --allow-root --path=\"/var/www/html\"";
                        $updatePostMeta = exec(escapeshellcmd("$wp_post_meta"));
			echo "Error: Cannot create media".$rn;
			continue;
		}
	}
	echo "------------- Finish Import 新頭殼 RSS Feed -------------".$rn;
    }
}

