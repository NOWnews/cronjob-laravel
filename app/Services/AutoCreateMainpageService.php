<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AutoCreateMainpageService
{
    public function createMainpage() {
        $innerHtml = '';
        $leftTopUrl = "https://v4api.nownews.com/cat/homepage-lefttop?limit=1&page=1";
        $leftTopPostContent = file_get_contents("$leftTopUrl");
        $leftTopJsonContent = json_decode($leftTopPostContent, true);
        $leftTopTitle = '';
        $leftTopImgUrl = '';
        $leftTopCategory = '焦點';
        $leftTopLink = '';

        if (!$leftTopJsonContent['newsList']) {
            $leftTopUrl = "https://v4api.nownews.com/cat/column?limit=1&page=1";
            $leftTopPostContent = file_get_contents("$leftTopUrl");
            $leftTopJsonContent = json_decode($leftTopPostContent, true);
            $leftTopCategory = '焦點';
        }

        $leftTopTitle = $leftTopJsonContent['newsList'][0]['title'];
        $leftTopImgUrl = $leftTopJsonContent['newsList'][0]['MainPhoto']['sizeFormat']['w750q70'];
        $leftTopLink = "https://www.nownews.com/news/". preg_replace("/-/", "", explode(" ", $leftTopJsonContent['newsList'][0]['formatStartedAt'])[0]) . "/" . $leftTopJsonContent['newsList'][0]['sn'];


        $innerHtml .= '<div class="c-features d-flex f-jc-between f-ai-stretch f-d-xs-column mx-2 mt-3 mb-3">';
        $innerHtml .= '<div class="c-features__smallWrap d-flex f-d-column f-ai-center f-jc-start f-d-xs-row f-w-xs-nowrap order-xs-1">';
        $innerHtml .= '<a href="';
        $innerHtml .= $leftTopLink;
        $innerHtml .= '" class="c-features__smallItem c-singlePost p-relative overflow-hidden" style="background-image:url(';
        $innerHtml .= $leftTopImgUrl;
        $innerHtml .= ')">';
        $innerHtml .= '<div class="c-singlePost__text p-absolute pr-3">';
        $innerHtml .= '<span class="tags d-inline-block">';
        $innerHtml .= $leftTopCategory;
        $innerHtml .= '</span>';
        $innerHtml .= '<div class="c-singlePost__text__title">';
        $innerHtml .= $leftTopTitle;
        $innerHtml .= '</div>';
        $innerHtml .= '</div></a>';

        
        $leftBottomUrl = "https://v4api.nownews.com/cat/homepage-leftbottom?limit=1&page=1";
        $leftBottomPostContent = file_get_contents("$leftBottomUrl");
        $leftBottomJsonContent = json_decode($leftBottomPostContent, true);
        $leftBottomTitle = '';
        $leftBottomImgUrl = '';
        $leftBottomCategory = '焦點';
        $leftBottomLink = '';
        if (!$leftBottomJsonContent['newsList']) {
            $leftBottomUrl = "https://v4api.nownews.com/cat/sport?limit=1&page=1";
            $leftBottomPostContent = file_get_contents("$leftBottomUrl");
            $leftBottomJsonContent = json_decode($leftBottomPostContent, true);
            $leftBottomCategory = '運動';
        }
        $leftBottomTitle = $leftBottomJsonContent['newsList'][0]['title'];
        $leftBottomImgUrl = $leftBottomJsonContent['newsList'][0]['MainPhoto']['sizeFormat']['w750q70'];
        $leftBottomLink = "https://www.nownews.com/news/". preg_replace("/-/", "", explode(" ", $leftBottomJsonContent['newsList'][0]['formatStartedAt'])[0]) . "/" . $leftBottomJsonContent['newsList'][0]['sn'];

        // $innerHtml .= '<div class="c-features d-flex f-jc-between f-ai-stretch f-d-xs-column mx-2 mt-3 mb-3">';
        // $innerHtml .= '<div class="c-features__smallWrap d-flex f-d-column f-ai-center f-jc-start f-d-xs-row f-w-xs-nowrap order-xs-1">';
        $innerHtml .= '<a href="';
        $innerHtml .= $leftBottomLink;
        $innerHtml .= '" class="c-features__smallItem c-singlePost p-relative overflow-hidden" style="background-image:url(';
        $innerHtml .= $leftBottomImgUrl;
        $innerHtml .= ')">';
        $innerHtml .= '<div class="c-singlePost__text p-absolute pr-3">';
        $innerHtml .= '<span class="tags d-inline-block">';
        $innerHtml .= $leftBottomCategory;
        $innerHtml .= '</span>';
        $innerHtml .= '<div class="c-singlePost__text__title">';
        $innerHtml .= $leftBottomTitle;
        $innerHtml .= '</div>';
        $innerHtml .= '</div></a>';

        $rightTopNoneUrl = "https://v4api.nownews.com/cat/homepage-rightTop?limit=1&page=1";
        $rightTopNonePostContent = file_get_contents("$rightTopNoneUrl");
        $rightTopNoneJsonContent = json_decode($rightTopNonePostContent, true);
        $rightTopNoneTitle = '';
        $rightTopNoneImgUrl = '';
        $rightTopNoneCategory = '焦點';
        $rightTopNoneLink = '';
        if (!$rightTopNoneJsonContent['newsList']) {
            $rightTopNoneUrl = "https://v4api.nownews.com/cat/life?limit=1&page=1";
            $rightTopNonePostContent = file_get_contents("$rightTopNoneUrl");
            $rightTopNoneJsonContent = json_decode($rightTopNonePostContent, true);
            $rightTopNoneCategory = '生活';
        }
        $rightTopNoneTitle = $rightTopNoneJsonContent['newsList'][0]['title'];
        $rightTopNoneImgUrl = $rightTopNoneJsonContent['newsList'][0]['MainPhoto']['sizeFormat']['w750q70'];
        $rightTopNoneLink = "https://www.nownews.com/news/". preg_replace("/-/", "", explode(" ", $rightTopNoneJsonContent['newsList'][0]['formatStartedAt'])[0]) . "/" . $rightTopNoneJsonContent['newsList'][0]['sn'];

        // $innerHtml .= '<div class="c-features d-flex f-jc-between f-ai-stretch f-d-xs-column mx-2 mt-3 mb-3">';
        // $innerHtml .= '<div class="c-features__smallWrap d-flex f-d-column f-ai-center f-jc-start f-d-xs-row f-w-xs-nowrap order-xs-1">';
        $innerHtml .= '<a href="';
        $innerHtml .= $rightTopNoneLink;
        $innerHtml .= '" class="c-features__smallItem c-singlePost p-relative overflow-hidden d-none d-xs-block" style="background-image:url(';
        $innerHtml .= $rightTopNoneImgUrl;
        $innerHtml .= ')">';
        $innerHtml .= '<div class="c-singlePost__text p-absolute pr-3">';
        $innerHtml .= '<span class="tags d-inline-block">';
        $innerHtml .= $rightTopNoneCategory;
        $innerHtml .= '</span>';
        $innerHtml .= '<div class="c-singlePost__text__title">';
        $innerHtml .= $rightTopNoneTitle;
        $innerHtml .= '</div>';
        $innerHtml .= '</div></a>';

        $rightBottomNoneUrl = "https://v4api.nownews.com/cat/homepage-rightBottom?limit=1&page=1";
        $rightBottomNonePostContent = file_get_contents("$rightBottomNoneUrl");
        $rightBottomNoneJsonContent = json_decode($rightBottomNonePostContent, true);
        $rightBottomNoneTitle = '';
        $rightBottomNoneImgUrl = '';
        $rightBottomNoneCategory = '焦點';
        $rightBottomNoneLink = '';
        if (!$rightBottomNoneJsonContent['newsList']) {
            $rightBottomNoneUrl = "https://v4api.nownews.com/cat/entertainment?limit=1&page=1";
            $rightBottomNonePostContent = file_get_contents("$rightBottomNoneUrl");
            $rightBottomNoneJsonContent = json_decode($rightBottomNonePostContent, true);
            $rightBottomNoneCategory = '娛樂';
        }
        $rightBottomNoneTitle = $rightBottomNoneJsonContent['newsList'][0]['title'];
        $rightBottomNoneImgUrl = $rightBottomNoneJsonContent['newsList'][0]['MainPhoto']['sizeFormat']['w750q70'];
        $rightBottomNoneLink = "https://www.nownews.com/news/". preg_replace("/-/", "", explode(" ", $rightBottomNoneJsonContent['newsList'][0]['formatStartedAt'])[0]) . "/" . $rightBottomNoneJsonContent['newsList'][0]['sn'];

        // $innerHtml .= '<div class="c-features d-flex f-jc-between f-ai-stretch f-d-xs-column mx-2 mt-3 mb-3">';
        // $innerHtml .= '<div class="c-features__smallWrap d-flex f-d-column f-ai-center f-jc-start f-d-xs-row f-w-xs-nowrap order-xs-1">';
        $innerHtml .= '<a href="';
        $innerHtml .= $rightBottomNoneLink;
        $innerHtml .= '" class="c-features__smallItem c-singlePost p-relative overflow-hidden d-none d-xs-block" style="background-image:url(';
        $innerHtml .= $rightBottomNoneImgUrl;
        $innerHtml .= ')">';
        $innerHtml .= '<div class="c-singlePost__text p-absolute pr-3">';
        $innerHtml .= '<span class="tags d-inline-block">';
        $innerHtml .= $rightBottomNoneCategory;
        $innerHtml .= '</span>';
        $innerHtml .= '<div class="c-singlePost__text__title">';
        $innerHtml .= $rightBottomNoneTitle;
        $innerHtml .= '</div>';
        $innerHtml .= '</div></a></div>';

        $centerUrl = "https://v4api.nownews.com/cat/homepage-center?limit=1&page=1";
        $centerPostContent = file_get_contents("$centerUrl");
        $centerJsonContent = json_decode($centerPostContent, true);
        $centerTitle = '';
        $centerImgUrl = '';
        $centerCategory = '焦點';
        $centerLink = '';
        if (!$centerJsonContent['newsList']) {
            $centerUrl = "https://v4api.nownews.com/cat/politics?limit=1&page=1";
            $centerPostContent = file_get_contents("$centerUrl");
            $centerJsonContent = json_decode($centerPostContent, true);
            $centerCategory = '政治';
        }
        $centerTitle = $centerJsonContent['newsList'][0]['title'];
        $centerImgUrl = $centerJsonContent['newsList'][0]['MainPhoto']['sizeFormat']['w750q70'];
        $centerLink = "https://www.nownews.com/news/". preg_replace("/-/", "", explode(" ", $centerJsonContent['newsList'][0]['formatStartedAt'])[0]) . "/" . $centerJsonContent['newsList'][0]['sn'];

        // $innerHtml .= '<div class="c-features d-flex f-jc-between f-ai-stretch f-d-xs-column mx-2 mt-3 mb-3">';
        // $innerHtml .= '<div class="c-features__smallWrap d-flex f-d-column f-ai-center f-jc-start f-d-xs-row f-w-xs-nowrap order-xs-1">';
        $innerHtml .= '<div class="c-features__bigWrap overflow-hidden order-0" >';
        $innerHtml .= '<a href="';
        $innerHtml .= $centerLink;
        $innerHtml .= '" class="c-features__bigItem p-relative overflow-hidden" style="background-image:url(';
        $innerHtml .= $centerImgUrl;
        $innerHtml .= ')">';
        $innerHtml .= '<div class="c-singlePost__text p-absolute pr-3">';
        $innerHtml .= '<span class="tags d-inline-block">';
        $innerHtml .= $centerCategory;
        $innerHtml .= '</span>';
        $innerHtml .= '<div class="c-features__bigItem__text__title">';
        $innerHtml .= $centerTitle;
        $innerHtml .= '</div>';
        $innerHtml .= '</div></a></div>';

        $rightTopUrl = "https://v4api.nownews.com/cat/homepage-rightTop?limit=1&page=1";
        $rightTopPostContent = file_get_contents("$rightTopUrl");
        $rightTopJsonContent = json_decode($rightTopPostContent, true);
        $rightTopTitle = '';
        $rightTopImgUrl = '';
        $rightTopCategory = '焦點';
        $rightTopLink = '';
        if (!$rightTopJsonContent['newsList']) {
            $rightTopUrl = "https://v4api.nownews.com/cat/life?limit=1&page=1";
            $rightTopPostContent = file_get_contents("$rightTopUrl");
            $rightTopJsonContent = json_decode($rightTopPostContent, true);
            $rightTopCategory = '生活';
        }
        $rightTopTitle = $rightTopJsonContent['newsList'][0]['title'];
        $rightTopImgUrl = $rightTopJsonContent['newsList'][0]['MainPhoto']['sizeFormat']['w750q70'];
        $rightTopLink = "https://www.nownews.com/news/". preg_replace("/-/", "", explode(" ", $rightTopJsonContent['newsList'][0]['formatStartedAt'])[0]) . "/" . $rightTopJsonContent['newsList'][0]['sn'];

        // $innerHtml .= '<div class="c-features d-flex f-jc-between f-ai-stretch f-d-xs-column mx-2 mt-3 mb-3">';
        // $innerHtml .= '<div class="c-features__smallWrap d-flex f-d-column f-ai-center f-jc-start f-d-xs-row f-w-xs-nowrap order-xs-1">';
        $innerHtml .= '<div class="c-features__smallWrap d-flex d-xs-none f-d-column f-ai-center f-jc-start d-xs-none">';
        $innerHtml .= '<a href="';
        $innerHtml .= $rightTopLink;
        $innerHtml .= '" class="c-features__smallItem c-singlePost p-relative overflow-hidden" style="background-image:url(';
        $innerHtml .= $rightTopImgUrl;
        $innerHtml .= ')">';
        $innerHtml .= '<div class="c-singlePost__text p-absolute pr-3">';
        $innerHtml .= '<span class="tags d-inline-block">';
        $innerHtml .= $rightTopCategory;
        $innerHtml .= '</span>';
        $innerHtml .= '<div class="c-singlePost__text__title">';
        $innerHtml .= $rightTopTitle;
        $innerHtml .= '</div>';
        $innerHtml .= '</div></a>';

        $rightBottomUrl = "https://v4api.nownews.com/cat/homepage-rightBottom?limit=1&page=1";
        $rightBottomPostContent = file_get_contents("$rightBottomUrl");
        $rightBottomJsonContent = json_decode($rightBottomPostContent, true);
        $rightBottomTitle = '';
        $rightBottomImgUrl = '';
        $rightBottomCategory = '焦點';
        $rightBottomLink = '';
        if (!$rightBottomJsonContent['newsList']) {
            $rightBottomUrl = "https://v4api.nownews.com/cat/entertainment?limit=1&page=1";
            $rightBottomPostContent = file_get_contents("$rightBottomUrl");
            $rightBottomJsonContent = json_decode($rightBottomPostContent, true);
            $rightBottomCategory = '娛樂';
        }
        $rightBottomTitle = $rightBottomJsonContent['newsList'][0]['title'];
        $rightBottomImgUrl = $rightBottomJsonContent['newsList'][0]['MainPhoto']['sizeFormat']['w750q70'];
        $rightBottomLink = "https://www.nownews.com/news/". preg_replace("/-/", "", explode(" ", $rightBottomJsonContent['newsList'][0]['formatStartedAt'])[0]) . "/" . $rightBottomJsonContent['newsList'][0]['sn'];

        // $innerHtml .= '<div class="c-features d-flex f-jc-between f-ai-stretch f-d-xs-column mx-2 mt-3 mb-3">';
        // $innerHtml .= '<div class="c-features__smallWrap d-flex f-d-column f-ai-center f-jc-start f-d-xs-row f-w-xs-nowrap order-xs-1">';
        $innerHtml .= '<a href="';
        $innerHtml .= $rightBottomLink;
        $innerHtml .= '" class="c-features__smallItem c-singlePost p-relative overflow-hidden" style="background-image:url(';
        $innerHtml .= $rightBottomImgUrl;
        $innerHtml .= ')">';
        $innerHtml .= '<div class="c-singlePost__text p-absolute pr-3">';
        $innerHtml .= '<span class="tags d-inline-block">';
        $innerHtml .= $rightBottomCategory;
        $innerHtml .= '</span>';
        $innerHtml .= '<div class="c-singlePost__text__title">';
        $innerHtml .= $rightBottomTitle;
        $innerHtml .= '</div>';
        $innerHtml .= '</div></a></div></div>';


        // $htmlStr = '';
        // $htmlStr .= '<!DOCTYPE html><html lang="zh-TW"><head><meta charset="UTF-8" /><meta http-equiv="X-UA-Compatible" content="IE=edge" /><meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0"/><title>Website</title><link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css"integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous"/><link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/><link rel="stylesheet" href="./Styles/all.css" /><script async="async" src="https://www.googletagservices.com/tag/js/gpt.js"></script><script>var googletag = googletag || {}; googletag.cmd = googletag.cmd || [];</script><script>googletag.cmd.push(function() {googletag.defineSlot("/5799246/Nownews_home_970x250_T_new2", [[970, 90], [970, 250], [300, 250], [320, 100]], "div-gpt-ad-1496983147535-0").addService(googletag.pubads()); googletag.defineSlot("/5799246/Nownews_home_300x250_M1_new2", [300, 250], "div-gpt-ad-1496983171426-0").addService(googletag.pubads()); googletag.defineSlot("/5799246/Nownews_home_300x250_M2_new2", [300, 250], "div-gpt-ad-1496983198899-0").addService(googletag.pubads()); googletag.defineSlot("/5799246/Nownews_home_970x250_B_new2", [[970, 250], [970, 90], [300, 250], [320, 100]], "div-gpt-ad-1496983308222-0").addService(googletag.pubads()); googletag.pubads().enableSingleRequest();googletag.pubads().collapseEmptyDivs();googletag.enableServices();});</script></head><body><div id="fb-root"></div><script async defer crossorigin="anonymous" src="https://connect.facebook.net/zh_TW/sdk.js#xfbml=1&version=v3.3&appId=1658394844305199&autoLogAppEvents=1"></script><div class="wrap mx-auto pt-3"><div class="c-features d-flex f-jc-between f-ai-stretch f-d-xs-column mx-2 mt-3 mb-3">';
        // $htmlStr .= $innerHtml;
        // $htmlStr .= '</div><script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script><script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script><script src="https://unpkg.com/axios/dist/axios.min.js"></script><script type="text/javascript" src="Scripts/all.js"></script></body></html>';

        // file_put_contents("/home/developer/NewMainPage/mainpage.html", $htmlStr);
        // file_put_contents("/home/developer/NOWrss-laravel/public/mainpage.html", $htmlStr);

        file_put_contents("/home/developer/NewMainPage/mainpage.html", $innerHtml);
        file_put_contents("/home/developer/NOWrss-laravel/public/mainpage.html", $innerHtml);
    }
}


