<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class ImportMNAService
{
    public function mnaxmlparser ($import) {
	$mna_xml = file_get_contents("http://mna.gpwb.gov.tw/service/RSS.php?type=news");
	$mna_xml = preg_replace('/\<summary\>/','<summary><![CDATA[',$mna_xml);
	$mna_xml = preg_replace('/\<\/summary\>/',']]></summary>',$mna_xml);
	$rn = "<br>\r\n";
	$xml = simplexml_load_string($mna_xml, 'SimpleXMLElement', LIBXML_NOCDATA);
	if(!$xml){
		echo "Error: Cannot create object".$rn;
		die();
	}
	echo "------------- Start Import 軍聞社 RSS Feed -------------".$rn;
	$content = $xml->channel->item;
	foreach ($content as $value) {
		$regexLink = '/^(http|https):\/\/mna.gpwb.gov.tw\/post.php\?id=/';
		preg_match($regexLink, $value->link , $resultLink);
		if(empty($resultLink)){
			echo "Error: Cannot parser link".$rn;
			continue;
		}
		$uniqKey = $value->guid;
		echo $uniqKey.$rn;
		$sqlquery = DB::select("select * from mna_feed where guid='$uniqKey'");
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
		//if($uniqKey != 'http://mna.gpwb.gov.tw/IndexDetail.aspx?id=90274'){
                //        continue;
                //}
		
		$description = str_replace("\n", "", $value->description);

		$hasImg = false;
		$aliveImg = false;
		$checkImg = false;
		preg_match("/(<img[^>]+>)/i", $description, $img);
		if(empty($img[0])){
			$mnaImagesObjectIds = [
				'2821166',
                                '2821129',
                                '2820975'
			];
			$randomDefaultImageId = $mnaImagesObjectIds[array_rand($mnaImagesObjectIds, 1)];
		}else{
			$hasImg = true;
			preg_match('/<img src=[\'"](.*?)[\'"](.*?)alt=[\'"](.*?)[\'"]/i', $description, $imageParam);
			$imageUrl = $imageParam[1];
			$imageAlt = "▲ ".$imageParam[3];
			if(!$imageAlt){
				$imageAlt = "▲ （圖／軍聞社）";
			}

			$code = 'wget --spider -S "'.$imageUrl.'" 2>&1 | grep "HTTP/" | awk \'{print $2}\'';
			$status = exec("$code");
			if($status == '200'){
				$aliveImg = true;
				$photofile = file_get_contents("$imageUrl");
				$filename = md5($uniqKey).".jpg";
				file_put_contents("/var/www/html/rssFeed/mna_img/$filename", $photofile);
			}
			echo $filename;
			echo $status."==".$rn;
			//break;

		}

		$title = mb_substr($value->title, 0, 26, 'utf8');
		$description = preg_replace('/(<img[^>]+>)/i', '', $description, 1);
		$description .= "<p>新聞來源:國防部軍事新聞通訊社</p>";
		//echo $description.$rn;


		$wp_post = "wp post create --allow-root --path=\"/var/www/html\" --post_type=post --post_author=5 --post_category=15 --post_date=\"".$pubdateFormat."\" --meta_input='{\"byline\":\"軍聞社\"}' --post_title=\"".htmlspecialchars($title,ENT_QUOTES)."\" --post_status=publish --post_content=\"".$description."\" --porcelain";
		$createPost = shell_exec("$wp_post");
		if($createPost){
			DB::insert('insert into mna_feed (guid) values (?)', [$uniqKey]);
			echo "------------------軍聞社--------------------".$rn;
                	echo "收錄新聞: ".$value->title.$rn;
                	echo "新聞連結: ".$value->link.$rn;
                	echo "新聞識別唯一值: ".$uniqKey.$rn;
                	echo "--------------------------------------------".$rn;
		}else{
			echo "Error: Cannot create post";
			continue;
		}

		if($hasImg && $aliveImg){
			$wp_media = "wp media import '/var/www/html/rssFeed/mna_img/$filename' --allow-root --path=\"/var/www/html\" --title=\"".$imageAlt."\" --caption=\"".$imageAlt."\" --alt=\"".$imageAlt."\" --featured_image --post_id=".$createPost;
			$createMedia = shell_exec("$wp_media");
			echo $createMedia.$rn;
			$checkImg = true;

			if(file_exists("/var/www/html/rssFeed/mna_img/$filename")){
				unlink("/var/www/html/rssFeed/mna_img/$filename");
			}
		}else{
			$wp_post_meta = "wp post meta update " .$createPost . " _thumbnail_id $randomDefaultImageId --allow-root --path=\"/var/www/html\"";
			$updatePostMeta = exec(escapeshellcmd("$wp_post_meta"));
			echo $updatePostMeta.$rn;
			$checkImg = true;
		}

		if(!$checkImg){
			$mnaImagesObjectIds = [
                                '2821166',
                                '2821129',
                                '2820975'
                        ];
                        $randomDefaultImageId = $mnaImagesObjectIds[array_rand($mnaImagesObjectIds, 1)];
                        $wp_post_meta = "wp post meta update " .$createPost . " _thumbnail_id $randomDefaultImageId --allow-root --path=\"/var/www/html\"";
                        $updatePostMeta = exec(escapeshellcmd("$wp_post_meta"));
			echo "Error: Cannot create media".$rn;
			continue;
		}
	}
	echo "------------- Finish Import 軍聞社 RSS Feed -------------".$rn;
    }
}
