<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class ImportNOWService
{
	public function getList(){
		$list = array();
		$list = array(
			'bobee' => [
			    'author'=>['196'],
			    'name'=>['保庇'],
			    'activity'=>['true'],
			    'url'=>[
			        "https://bobee.nownews.com/devnownews"
			        ]
			    ],
			'sight' => [
			    'author'=>['197'],
			    'name'=>['今日觀點'],
			    'activity'=>['false'],
			    'url'=>[
			        "https://sight.nownews.com/devnownews"
			        ]
			    ],
			);
		return $list;
	}
	public function nowxmlparser ($import) {
		$now_list = $this->getList();
		$rn = "<br>\r\n";
		echo "------------- Start Import 分眾頻道 RSS Feed -------------".$rn;
		foreach ($now_list as $feedsCate => $feedsParam) {
			if($feedsParam['activity'][0] == 'false'){
				continue;
			}
			foreach ($feedsParam['url'] as $feedsUrl) {
				$feedsUrl = $feedsUrl."?time=".strtotime("now");//防止快取
				$now_xml = file_get_contents("$feedsUrl");
				$xml = simplexml_load_string($now_xml, 'SimpleXMLElement', LIBXML_NOCDATA);
				if(!$xml){
					echo "Error: Cannot create object".$rn;
					die();
				}
				echo "------------- Start Import 分眾頻道 ".$feedsCate." RSS Feed -------------".$rn;
				$content = $xml->channel->item;
				foreach ($content as $value) {
					$regexLink = '/^(http|https):\/\/'.$feedsCate.'.nownews.com\/[0-9]+/';
					preg_match($regexLink, $value->link , $resultLink);
					if(empty($resultLink)){
						echo "Error: Cannot parser link".$rn;
						continue;
					}
					$uniqKey = $value->guid;
					echo $uniqKey.$rn;
					$byline = $feedsParam['name'][0];

					$sqlquery = DB::select("select * from now_feed where guid='$uniqKey'");
					if($sqlquery){
						echo "Feed exist".$rn;
						continue;
					}

					$pubdate = $value->pubDate;
					$pubTime =  strtotime($pubdate."+8 hour");
					$prevTime = strtotime("-1 day");
					$pubdateFormat = date("Y-m-d H:i:s",$pubTime);
					if($pubTime < $prevTime){
						echo "Out of times".$rn;
						continue;
					}
					
					if(isset($value->tags)){
						$keywords = null;
						foreach($value->tags as $tag){
							$keywords .= $tag.",";
						}
						$keywords = substr($keywords,0,-1);
					}else{
						$keywords = $feedsParam['name'][0];
					}

					if($feedsCate == 'sight'){
						$byline = "文 / ".$value->children( 'dc', True )->creator;
					}

					if(isset($value->category)){
						if($feedsCate == 'sight'){
							$cate = '116,';
							foreach ($value->category as $cat){
								if($cat == '時事'){
									$cat_num = '124';
								}else if($cat == '社會議題'){
									$cat_num = '125';
								}else if($cat == '健康醫療'){
									$cat_num = '126';
								}else if($cat == '科技智慧'){
									$cat_num = '127';
								}else if($cat == '文化'){
									$cat_num = '128';
								}else if($cat == '環境'){
									$cat_num = '129';
								}else if($cat == '人物專訪'){
									$cat_num = '130';
								}
								$cate .= $cat_num.",";
							}
							$cate = substr($cate,0,-1);
						}else if($feedsCate == 'bobee'){
							$cate = '115,';
							foreach ($value->category as $cat){
                                                                if($cat == '神明故事' || $cat == '廟宇故事'){
                                                                        $cat_num = '119';
                                                                }else if($cat == '宗教用品' || $cat == '廟宇小物'){
                                                                        $cat_num = '120';
                                                                }else if($cat == '時事專題' || $cat == '到新北瘋祭典' || $cat == '怪談追追追'){
                                                                        $cat_num = '121';
                                                                }else if($cat == '宗教儀式' || $cat == '拜拜教學' ||$cat == '民俗相關' ||$cat == '神職人員' || $cat == '宗教祭典'){
                                                                        $cat_num = '122';
                                                                }else if($cat == '宗教藝術' || $cat == '原民信仰' || $cat == '原民傳說' || $cat == '民俗藝陣'){
                                                                        $cat_num = '123';
                                                                }
                                                                $cate .= $cat_num.",";
                                                        }
							$cate = substr($cate,0,-1);
						}
					}

					$aliveImg = false;
					$checkImg = false;
					$imageUrl = $value->enclosure->attributes()['url'];
					$imageAlt = "▲ （圖／".$feedsParam['name'][0]."）";

					$code = 'wget --spider -S "'.$imageUrl.'" 2>&1 | grep "HTTP/" | awk \'{print $2}\'';
					$status = exec("$code");

					if($status == '200'){
						$aliveImg = true;
						$photofile = file_get_contents("$imageUrl");
						$filename = md5($uniqKey).".jpg";
						file_put_contents("/var/www/html/rssFeed/now_img/$filename", $photofile);
					}
					echo $filename;
					echo $status."==".$rn;
					//break;


					$title = mb_substr($value->title, 0, 26, 'utf8');
					$description = $value->children( 'content', True )->encoded;
					$rep = '#data-image-meta="(.*?)"#';
					$description = preg_replace($rep, '', $description);
					$description = str_replace(array('\n','"'), array('','\"'), $description);
					echo $description.$rn;

					$author = $feedsParam['author'][0];
					$wp_post = "wp post create --allow-root --path=\"/var/www/html\" --post_type=post --post_author=\"".$author."\" --post_category=$cate --tags_input=\"".htmlspecialchars($keywords,ENT_QUOTES)."\" --post_date=\"".$pubdateFormat."\" --meta_input='{\"byline\":\"".$byline."\"}' --post_title=\"".htmlspecialchars($title,ENT_QUOTES)."\" --post_status=publish --post_content=\"".$description."\" --porcelain";
					$createPost = shell_exec("$wp_post");
					if($createPost){
						DB::insert('insert into now_feed (guid) values (?)', [$uniqKey]);
						echo "------------------分眾頻道--------------------".$rn;
						echo "收錄新聞: ".$value->title.$rn;
						echo "新聞連結: ".$value->link.$rn;
						echo "新聞識別唯一值: ".$uniqKey.$rn;
						echo "訊頭: ".$byline.$rn;
						echo $createPost;
						echo "--------------------------------------------".$rn;
					//dd($wp_post);
					}else{
						echo "Error: Cannot create post";
						continue;
					}

					if($aliveImg){
						$wp_media = "wp media import '/var/www/html/rssFeed/now_img/$filename' --allow-root --path=\"/var/www/html\" --title=\"".$imageAlt."\" --caption=\"".$imageAlt."\" --alt=\"".$imageAlt."\" --featured_image --post_id=".$createPost;
						$createMedia = shell_exec("$wp_media");
						echo $createMedia.$rn;
						$checkImg = true;

						if(file_exists("/var/www/html/rssFeed/now_img/$filename")){
							unlink("/var/www/html/rssFeed/now_img/$filename");
						}
					}

					if (!$checkImg) {
						echo "Error: Cannot create media".$rn;
						continue;
					}

				}
			}
		}
		echo "------------- Finish Import 分眾頻道 RSS Feed -------------".$rn;
    }
}
