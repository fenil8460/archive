<?php

namespace App\Jobs;

use App\Models\Keyword;
use App\Models\Snapshot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateSnapShot implements ShouldQueue
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
        //
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $snap_shot_data = $this->data['insert_data'];
        foreach($snap_shot_data as $item){

            Snapshot::create($item);
        }

        $keywords = Keyword::get();
        $url_id = $this->data['url_id'];
        Snapshot::where('url_id',$url_id)->update(['status'=>2]);
        $snapshot = Snapshot::where('url_id',$url_id)->where('status',2)->get();

        $status = 4; //not spam
        $reason = null; //not spam
        $count = 0;
        if (count($snapshot) >= 0) {
            foreach ($snapshot as  $item) {

                $html = $this->file_get_contents_curl($item->snapshot);
                //check keyword is exists or not
                foreach ($keywords as $item2) {
                    if (str_contains($html, $item2->keyword)) {
                        $count = $count + 1;
                    }
                }

                // check minimum matching count
                if ($count > config('app.spam_keyword')) {
                    $status = 3; // spam
                    $reason = 'Bad-keyword match more than '.$count;
                }elseif($this->isJapanese($html)){
                    $status = 3; // spam
                    $reason = 'Japanese keyword Detected';
                }elseif($this->isChinese($html)){
                    $status = 3; // spam
                    $reason = 'Chinese keyword Detected';
                }
                // update the status is spam or not

                $update_data = [
                    'status'=> $status,
                    'reason'=>$reason
                ];
                Snapshot::where('url_id', $url_id)->where('id',$item->id)->update($update_data);
                $count = 0;
                $status = 4;
                $reason = null;
            }
        }
    }


    public function isJapanese($lang) {
        return preg_match('/[\x{4E00}-\x{9FBF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}]/u', $lang);
    }

    public function isChinese($lang) {
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
