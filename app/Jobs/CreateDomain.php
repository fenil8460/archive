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
        //
        $keywords = Keyword::get();
        $url = explode(',', $this->data['url']);
        $task_id = $this->data['task_id'];
        $status = 1;
        $count = 0;
        if (count($url) >= 0) {
            foreach ($url as $key => $item) {
                $insert_url = preg_replace('/\s+/', ' ', ltrim($item));
                $html = $this->file_get_contents_curl($insert_url);
                foreach ($keywords as $item2) {
                    if (str_contains($html, $item2->keyword)) {
                        $count = $count + 1;
                    }
                }
                if ($count > config('app.spam_keyword')) {
                    $status = 0;
                }
                $insert_data = [
                    'url' => $insert_url,
                    'task_id' => $task_id,
                    'status' => $status
                ];
                Url::create($insert_data);
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
