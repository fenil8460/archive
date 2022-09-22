<?php

namespace App\Jobs;

use App\Models\Keyword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Url;
use DOMDocument;

class CreateDomain implements ShouldQueue
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
        //get keyword
        $keywords = Keyword::get();
        $task_id = $this->data['task_id'];

        // update url status
        Url::where('task_id', $task_id)->update(['status'=> 2]);
        
        $url = Url::where('task_id', $task_id)->get();

        $status = 4; //not spam
        $count = 0;
        if (count($url) >= 0) {
            foreach ($url as  $item) {

                $html = $this->file_get_contents_curl($item->url);
                //check keyword is exists or not
                foreach ($keywords as $item2) {
                    if (str_contains($html, $item2->keyword)) {
                        $count = $count + 1;
                    }
                }

                // check minimum matching count
                if ($count > config('app.spam_keyword')) {
                    $status = 3; // spam
                }
               
                // update the status is spam or not
                Url::where('task_id', $task_id)->where('id',$item->id)->update(['status'=> $status]);
                $count = 0;
                $status = 4;
            }
        }
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
