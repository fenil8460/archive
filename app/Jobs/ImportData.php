<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Url;
use App\Models\Keyword;


class ImportData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //get keyword
        $keywords = Keyword::get();
        $task_id = $this->data['task_id'];

        $url = $this->data['url'];

        $status = 4; //not spam
        $reason = null; //not spam
        $count = 0;
        if (count($url) >= 0) {
            foreach ($url as  $item) {
                $html = $this->file_get_contents_curl($item);
                //check keyword is exists or not
                foreach ($keywords as $item2) {
                    if (str_contains($html, $item2->keyword)) {
                        $count = $count + 1;
                    }
                }
                // check minimum matching count
                if ($count > config('app.spam_keyword')) {
                    $status = 3; // spam
                    $reason = 'Bad-keyword match more than ' . $count;
                } elseif ($this->isJapanese($html)) {
                    $status = 3; // spam
                    $reason = 'Japanese keyword Detected';
                } elseif ($this->isChinese($html)) {
                    $status = 3; // spam
                    $reason = 'Chinese keyword Detected';
                }
                // update the status is spam or not
                Url::create(['url'=>$item,'status' => $status, 'reason' => $reason,'task_id'=> $task_id]);
                $count = 0;
                $status = 4;
                $reason = null;
            }
        }
    }


    public function isJapanese($lang)
    {
        return preg_match('/[\x{4E00}-\x{9FBF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}]/u', $lang);
    }

    public function isChinese($lang)
    {
        return preg_match('/[\x{4e00}-\x{9fa5}]/u', $lang);
    }


    public function file_get_contents_curl($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }
}
