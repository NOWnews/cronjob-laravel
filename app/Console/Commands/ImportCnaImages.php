<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportCnaImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:CnaImagesXML';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'import CNA images xml';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cnaImagesUrl = 'http://rss.cna.com.tw/client/nownews/pho/gallery_feed_cnaphoto.xml';
        $now = Carbon::now('Asia/Taipei');

        // get cna images xml info
        $result = file_get_contents($cnaImagesUrl);

        // turn xml string into SimpleXMLElement
        $this->info("Download xml from {$cnaImagesUrl}...");
        $xml = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);

        if (!$xml) {
            $this->error('Invalid xml');
            exit;
        }

        $imageItems = [];
        $newItems = $xml->NewsItem;

        $this->info('Parsing xml...');
        foreach ($newItems as $newItem) {
            $newsComponents = $newItem->NewsComponent->NewsComponent->NewsComponent;
            $imageItem = [];

            foreach ($newsComponents as $newsComponent) {
                $roleAttributes = (array)$newsComponent->Role->attributes();
                $formalName = $roleAttributes['@attributes']['FormalName'];

                // only get images info in Supporting attribute
                if ($formalName !== 'Supporting') {
                    continue;
                }

                // get Duid
                $duid = (string)$newsComponent->NewsComponent->attributes()['Duid'];
                $imageItem['duid'] = $duid;

                $supportingComponents = $newsComponent->NewsComponent->NewsComponent;

                foreach ($supportingComponents as $supportingComponent) {
                    $roleAttributes = (array)$supportingComponent->Role->attributes();
                    $formalName = $roleAttributes['@attributes']['FormalName'];

                    if ($formalName === 'Main') {
                        // get image href
                        $imageItem['href'] = (string)$supportingComponent->ContentItem->attributes()['Href'];
                    }

                    if ($formalName === 'Caption') {
                        // get image caption
                        $captions = (array)$supportingComponent->ContentItem->DataContent->p;
                        $imageItem['title'] = $captions[0];
                        $imageItem['caption'] = $captions[1];
                    }
                }

                $imageItems[] = $imageItem;
            }
        }

        $this->info('Import cna images into media...');

        foreach ($imageItems as $imageItem) {
            $duid = $imageItem['duid'];
            $url = $imageItem['href'];
            $caption = $imageItem['caption'];
            $title = $imageItem['title'];
            $userId = 4; // cna user id


            // ignore if post exists
            if ($this->isPostsExists($duid)) {
                continue;
            }

            // import image into media
            $wpCli = "wp media import {$url} --allow-root --path=\"/var/www/html\" --user={$userId} --title=\"{$title}\" --caption=\"{$caption}\" --porcelain";
            $mediaId = (int)shell_exec($wpCli);

            if (!$mediaId) {
                $this->error("media import fail: {$duid}");
                continue;
            }

            // record guid to prevent repeat import
            DB::table('cna_feed')->insert([
                'guid' => $duid,
                'created_at' => $now,
            ]);

            // print messages
            $this->line("Import Completed ! media id: {$mediaId}");
            $this->line("Image url: {$url}");
            $this->line("Image title: {$title}");
            $this->line("Image caption: {$caption}");
            $this->line('');
        }

        $this->info('Import cna images into media all completed !');
    }

    protected function isPostsExists(string $guid)
    {
        $post = DB::table('cna_feed')
            ->where('guid', $guid)
            ->first();

        if (!$post) {
            return false;
        }

        $this->warn("Duid is exists: {$guid}");

        return true;
    }

    protected function deletePostRecord(string $guid)
    {
        DB::table('cna_feed')
            ->where('guid', $guid)
            ->delete();
    }
}
