<?php

namespace App\Http\Controllers;

use App\Jobs\CreateDomain;
use App\Jobs\ImportData;
use App\Models\Keyword;
use App\Models\Task;
use App\Models\Url;
use App\Rules\domain;
use DOMDocument;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Imports\UrlsImport;
use App\Jobs\CheckSnapShot;
use App\Jobs\CreateSnapShot;
use App\Models\Snapshot;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CheckDomainController extends Controller
{
    //

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

    public function import(Request $request)
    {
        $request->validate([
            'task_id' => 'required',
            'file' => 'required|mimes:xlsx,xls',
        ]);
        $the_file = $request->file('file');
        $spreadsheet = IOFactory::load($the_file->getRealPath());
        $sheet        = $spreadsheet->getActiveSheet();
        $row_limit    = $sheet->getHighestDataRow();
        $column_limit = $sheet->getHighestDataColumn();
        $row_range    = range(2, $row_limit);
        $column_range = range('F', $column_limit);
        $startcount = 2;
        $data = array();
        $insert_data = [];
        // dd($row_range);
        foreach ($row_range as $row) {
            // $data[] = [
            //     'url' => $sheet->getCell( 'B' . $row )->getValue(),
            //     'task_id'=>$request->task_id
            // ];
            // $insert_data[$key] = $sheet->getCell('B' . $row)->getValue();
            // dd($sheet->getCell( 'A' . $row )->getValue());
            $insert_data = [
                'url' => $sheet->getCell('A' . $row)->getValue(),
                'task_id' => $request->task_id,
                'status' => 1
            ];
            // dd($insert_data);

            // create new url based on task
            Url::create($insert_data);
            $startcount++;
        }
        $data['task_id'] = $request->task_id;
        dispatch(new ImportData($data));

        return redirect()->back()->with('message', "URL's imported successfully!");
    }



    public function ExportExcel($url_data)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '4000M');
        try {
            $spreadSheet = new Spreadsheet();
            $spreadSheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);
            $spreadSheet->getActiveSheet()->fromArray($url_data);
            $Excel_writer = new Xls($spreadSheet);
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="urls.xls"');
            header('Cache-Control: max-age=0');
            ob_end_clean();
            $Excel_writer->save('php://output');
            exit();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     *This function loads the customer data from the database then converts it
     * into an Array that will be exported to Excel
     */
    function exportData($id, $status)
    {
        $url = Url::join('tasks', 'urls.task_id', '=', 'tasks.id')->where('task_id', $id);

        if ($status == 'active') {
            $url->where('status', 4);
        } else {
            $url->where('status', '!=', 4);
        }
        $url = $url->select('tasks.name', 'urls.*')->get();
        $data_array[] = array("Task Name", "URL", "Status", "Reason", "Created At");
        foreach ($url as $key => $data_item) {
            $status = '';
            if ($data_item->status == 1) {
                $status = 'Waiting for proccesing';
            }
            if ($data_item->status == 2) {
                $status = 'Underproccess';
            }
            if ($data_item->status == 3) {
                $status = 'Spam';
            }
            if ($data_item->status == 4) {
                $status = 'Ok';
            }
            $data_array[] = array(
                'Task Name' => $data_item->name,
                'URL' => $data_item->url,
                'Status' => $status,
                'Reason' => $data_item->reason,
                'Created At' => $data_item->created_at,
            );
        }
        $this->ExportExcel($data_array);
    }

    function sampleExportData()
    {
        $data_array[] = array("url");
        $data_array[] = array("https://www.alwaysinfotech.com/");
        $data_array[] = array("alwaysinfotech.com");
        $this->ExportExcel($data_array);
    }


    public function createTask(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => [new domain],
        ]);
        $data = [
            'name' => $request->name,
            'user_id' => Auth::user()->id,
        ];

        // create new task
        $create_task = Task::create($data);

        if ($request->url != null) {
            $url = preg_split('/\r\n|[\r\n]/', $request->url);
            foreach ($url as $item) {
                $insert_url = preg_replace('/\s+/', ' ', ltrim($item));
                $insert_data = [
                    'url' => $insert_url,
                    'task_id' => $create_task->id,
                    'status' => 1
                ];

                // create new url based on task
                Url::create($insert_data);
            }

            $url = [
                'task_id' => $create_task->id
            ];

            dispatch(new CreateDomain($url));
        }
        return redirect('list-task');
    }

    public function listTask(Request $request)
    {
        $task = Task::where('user_id', Auth::user()->id)->paginate(config('app.pagination_limit'));
        foreach ($task as $key => $item) {
            $url = Url::where('task_id', $item->id)->count();
            $task[$key]['count'] = $url;
        }
        return view('task.list', ['tasks' => $task]);
    }

    public function listUrl($id)
    {
        $url_proccess = Url::where('task_id', $id)->where('status','!=', 4)->where('status','!=',3)->paginate(config('app.pagination_limit'));
        $url_active = Url::where('task_id', $id)->where('status', 4)->paginate(config('app.pagination_limit'));
        $url_spam = Url::where('task_id', $id)->where('status', '=', 3)->paginate(config('app.pagination_limit'));
        $name = Task::select('name')->where('id', $id)->first();
        foreach ($url_spam as $key => $item) {
            $status = '';
            if ($item->status == 1) {
                $status = 'Waiting for proccesing';
            }
            if ($item->status == 2) {
                $status = 'Underproccess';
            }
            if ($item->status == 3) {
                $status = 'Spam';
            }
            if ($item->status == 4) {
                $status = 'Ok';
            }
            $url_spam[$key]['status_name'] = $status;
        }
        return view('url.list', ['url_actives' => $url_active, 'task_name' => $name, 'url_proccess' => $url_proccess, 'url_spams' => $url_spam, 'task_id' => $id]);
    }

    public function getSnapShot(Request $request)
    {
        $get_snapshot = Snapshot::where('url_id', $request->id)->count();

        if ($get_snapshot == 0) {
            $snapshot =  Http::get('http://web.archive.org/cdx/search/cdx?output=json&url=' . $request->url);
            $snapshot = json_decode($snapshot);
            $insert_data = [];
            unset($snapshot[0]);
            foreach ($snapshot as $index => $item) {
                $insert_data[$index] = [
                    'snapshot' => 'http://web.archive.org/web/' . $item[1] . '/' . $item[2],
                    'url_id' => $request->id,
                    'status' => 1,
                    'status_name' => 'Processing',
                    'reason' => null,
                    'timestamp' => $item[1],
                ];
            }
            // Snapshot::insert($insert_data);
            $snapshot_data = [
                'url_id' => $request->id,
                'insert_data' => $insert_data
            ];

            // dispatch(new CheckSnapShot($snapshot_data));
            dispatch(new CreateSnapShot($snapshot_data));

            return view('snapshot.list', ['snapshots' => $insert_data]);
        }
        $data = Snapshot::where('url_id', $request->id)->get();
        foreach ($data as $key => $item) {
            $status = '';
            if ($item->status == 1) {
                $status = 'Waiting for proccesing';
            }
            if ($item->status == 2) {
                $status = 'Underproccess';
            }
            if ($item->status == 3) {
                $status = 'Spam';
            }
            if ($item->status == 4) {
                $status = 'Ok';
            }
            $data[$key]['status_name'] = $status;
        }



        return view('snapshot.list', ['snapshots' => $data]);
    }


    public function regenrateSnapShot(Request $request)
    {
        $snapshot =  Http::get('http://web.archive.org/cdx/search/cdx?output=json&url=' . $request->url);
        $snapshot = json_decode($snapshot);
        unset($snapshot[0]);
        $insert_data = [];
        foreach ($snapshot as $index => $item) {

            $insert_data[$index] = [
                'snapshot' => 'http://web.archive.org/web/' . $item[1] . '/' . $item[2],
                'url_id' => $request->id,
                'status' => 2,
                'status_name' => 'Ok',
                'reason' => null,
                'timestamp' => $item[1],
            ];

            // $available_count = Snapshot::where('snapshot', '=', $insert_data['snapshot'])->where('url_id', '=', $request->id)->count();
            // if ($available_count == 0) {
            //     Snapshot::create($insert_data);
            // }
        }

        $snapshot_data = [
            'url_id' => $request->id,
            'insert_data' => $insert_data
        ];

        dispatch(new CheckSnapShot($snapshot_data));            

        $url = '/url-spanshot?url=' . $request->url . '.&id=' . $request->id;
        return redirect($url);
    }

    public function badKeyword()
    {
        $data = [
            ["keyword" => "basl"],
            ["keyword" => "bchatroulette"],
            ["keyword" => "bpos"],
            ["keyword" => "ba11"],
            ["keyword" => "banal"],
            ["keyword" => "bass"],
            ["keyword" => "bballsack"],
            ["keyword" => "bbj"],
            ["keyword" => "bbong"],
            ["keyword" => "bcocaine"],
            ["keyword" => "bcum"],
            ["keyword" => "bdick"],
            ["keyword" => "bdp"],
            ["keyword" => "bffs"],
            ["keyword" => "bfml"],
            ["keyword" => "bjackass"],
            ["keyword" => "bkike"],
            ["keyword" => "bminge"],
            ["keyword" => "bmuff"],
            ["keyword" => "bpaki"],
            ["keyword" => "bpedo"],
            ["keyword" => "bpube"],
            ["keyword" => "brape"],
            ["keyword" => "bretard"],
            ["keyword" => "bscat"],
            ["keyword" => "bschlick"],
            ["keyword" => "bsemen"],
            ["keyword" => "bslag"],
            ["keyword" => "btard"],
            ["keyword" => "btestes"],
            ["keyword" => "btits"],
            ["keyword" => "bvagina"],
            ["keyword" => "bwap"],
            ["keyword" => "bwop"],
            ["keyword" => "bcokehead"],
            ["keyword" => "bfoad"],
            ["keyword" => "bfuÂ©k"],
            ["keyword" => "banalingus"],
            ["keyword" => "banalintruder"],
            ["keyword" => "banilingus"],
            ["keyword" => "banus"],
            ["keyword" => "barsebandit"],
            ["keyword" => "barsehole"],
            ["keyword" => "barsewipe"],
            ["keyword" => "basphyxiophila"],
            ["keyword" => "basshole"],
            ["keyword" => "basswipe"],
            ["keyword" => "bb17ch"],
            ["keyword" => "bb1tch"],
            ["keyword" => "bbadword"],
            ["keyword" => "bballbag"],
            ["keyword" => "bballsac"],
            ["keyword" => "bbastard"],
            ["keyword" => "bbattyboy"],
            ["keyword" => "bbattyman"],
            ["keyword" => "bbawbag"],
            ["keyword" => "bbeastiality"],
            ["keyword" => "bbeefcurtains"],
            ["keyword" => "bbellend"],
            ["keyword" => "bbi7ch"],
            ["keyword" => "bbitch"],
            ["keyword" => "bblowjob"],
            ["keyword" => "bbltch"],
            ["keyword" => "bboabie"],
            ["keyword" => "bbollocks"],
            ["keyword" => "bbollox"],
            ["keyword" => "bboner"],
            ["keyword" => "bboobjob"],
            ["keyword" => "bboobies"],
            ["keyword" => "bboobs"],
            ["keyword" => "bbuftie"],
            ["keyword" => "bbuggery"],
            ["keyword" => "bbukkake"],
            ["keyword" => "bbullshit"],
            ["keyword" => "bbumbandit"],
            ["keyword" => "bbumchum"],
            ["keyword" => "bbuttfucker"],
            ["keyword" => "bbuttplug"],
            ["keyword" => "bc0k"],
            ["keyword" => "bcack"],
            ["keyword" => "bcamelcunt"],
            ["keyword" => "bcameltoe"],
            ["keyword" => "bcannabis"],
            ["keyword" => "bcapper"],
            ["keyword" => "bcarpetmunche"],
            ["keyword" => "bchebs"],
            ["keyword" => "bchickswithdiks"],
            ["keyword" => "bchink"],
            ["keyword" => "bchoad"],
            ["keyword" => "bclit"],
            ["keyword" => "bclunge"],
            ["keyword" => "bclusterfuck"],
            ["keyword" => "bcocksucker"],
            ["keyword" => "bcock"],
            ["keyword" => "bcockend"],
            ["keyword" => "bcockgoblin"],
            ["keyword" => "bcockmuncher"],
            ["keyword" => "bcocknose"],
            ["keyword" => "bcok"],
            ["keyword" => "bcoon"],
            ["keyword" => "bcrackhead"],
            ["keyword" => "bcrackwhore"],
            ["keyword" => "bcrap"],
            ["keyword" => "bcreampie"],
            ["keyword" => "bcretin"],
            ["keyword" => "bcumshot"],
            ["keyword" => "bcumstain"],
            ["keyword" => "bcunilingus"],
            ["keyword" => "bcunnilingus"],
            ["keyword" => "bcuntflaps"],
            ["keyword" => "bcunt"],
            ["keyword" => "bcybersex"],
            ["keyword" => "bdago"],
            ["keyword" => "bdarkie"],
            ["keyword" => "bdiaf"],
            ["keyword" => "bdickcheese"],
            ["keyword" => "bdickhead"],
            ["keyword" => "bdicknose"],
            ["keyword" => "bdike"],
            ["keyword" => "bdildo"],
            ["keyword" => "bdipshit"],
            ["keyword" => "bdoggiestyle"],
            ["keyword" => "bdoggystyle"],
            ["keyword" => "bdoublepenetrtion"],
            ["keyword" => "bdouchebag"],
            ["keyword" => "bdouchefag"],
            ["keyword" => "bdunecoon"],
            ["keyword" => "bdyke"],
            ["keyword" => "bejaculate"],
            ["keyword" => "bfadge"],
            ["keyword" => "bfag"],
            ["keyword" => "bfaggot"],
            ["keyword" => "bfandan"],
            ["keyword" => "bfap"],
            ["keyword" => "bfascist"],
            ["keyword" => "bfcuk"],
            ["keyword" => "bfeck"],
            ["keyword" => "bfelatio"],
            ["keyword" => "bfelch"],
            ["keyword" => "bfellate"],
            ["keyword" => "bfellatio"],
            ["keyword" => "bfeltch"],
            ["keyword" => "bfeltching"],
            ["keyword" => "bfenian"],
            ["keyword" => "bfingerbang"],
            ["keyword" => "bfingerfuck"],
            ["keyword" => "bfisting"],
            ["keyword" => "bfluffer"],
            ["keyword" => "bfook"],
            ["keyword" => "bforeskin"],
            ["keyword" => "bfucc"],
            ["keyword" => "bfuccd"],
            ["keyword" => "bfucced"],
            ["keyword" => "bfuccer"],
            ["keyword" => "bfucces"],
            ["keyword" => "bfuccing"],
            ["keyword" => "bfuccs"],
            ["keyword" => "bfuckface"],
            ["keyword" => "bfuck"],
            ["keyword" => "bfucker"],
            ["keyword" => "bfucking"],
            ["keyword" => "bfucktard"],
            ["keyword" => "bfuckwit"],
            ["keyword" => "bfuct"],
            ["keyword" => "bfudgepacker"],
            ["keyword" => "bfugly"],
            ["keyword" => "bfuk"],
            ["keyword" => "bfunbags"],
            ["keyword" => "bfvck"],
            ["keyword" => "bgangbang"],
            ["keyword" => "bgangrape"],
            ["keyword" => "bganja"],
            ["keyword" => "bgaylord"],
            ["keyword" => "bgaytard"],
            ["keyword" => "bgimp"],
            ["keyword" => "bgizzum"],
            ["keyword" => "bgloryhole"],
            ["keyword" => "bgoatse"],
            ["keyword" => "bgobshite"],
            ["keyword" => "bgoddamn"],
            ["keyword" => "bgoddammit"],
            ["keyword" => "bgollywog"],
            ["keyword" => "bgonads"],
            ["keyword" => "bgooch"],
            ["keyword" => "bgook"],
            ["keyword" => "bgoolies"],
            ["keyword" => "bgypo"],
            ["keyword" => "bgyppo"],
            ["keyword" => "bhandjob"],
            ["keyword" => "bhard-on"],
            ["keyword" => "bhardon"],
            ["keyword" => "bhentai"],
            ["keyword" => "bhooker"],
            ["keyword" => "bhoormister"],
            ["keyword" => "bincest"],
            ["keyword" => "bintercourse"],
            ["keyword" => "bjackingoff"],
            ["keyword" => "bjackoff"],
            ["keyword" => "bjamrag"],
            ["keyword" => "bjap'seye"],
            ["keyword" => "bjapseye"],
            ["keyword" => "bjaysis"],
            ["keyword" => "bjaysus"],
            ["keyword" => "bjerkoff"],
            ["keyword" => "bjerkingoff"],
            ["keyword" => "bjiggaboo"],
            ["keyword" => "bjism"],
            ["keyword" => "bjiz"],
            ["keyword" => "bjizm"],
            ["keyword" => "bjizz"],
            ["keyword" => "bkaffir"],
            ["keyword" => "bkeech"],
            ["keyword" => "bklunge"],
            ["keyword" => "bknackers"],
            ["keyword" => "bknobend"],
            ["keyword" => "bknobhead"],
            ["keyword" => "bknobjockey"],
            ["keyword" => "bkoon"],
            ["keyword" => "bkyke"],
            ["keyword" => "blardarse"],
            ["keyword" => "blardass"],
            ["keyword" => "blesbo"],
            ["keyword" => "blezbo"],
            ["keyword" => "blezzer"],
            ["keyword" => "blezzie"],
            ["keyword" => "bmasterbate"],
            ["keyword" => "bmasterbation"],
            ["keyword" => "bmasturbat"],
            ["keyword" => "bmasturbate"],
            ["keyword" => "bmasturbating"],
            ["keyword" => "bmasturbation"],
            ["keyword" => "bmeatspin"],
            ["keyword" => "bmilf"],
            ["keyword" => "bminger"],
            ["keyword" => "bmofo"],
            ["keyword" => "bmolest"],
            ["keyword" => "bmong"],
            ["keyword" => "bmongoloid"],
            ["keyword" => "bmotherfucker"],
            ["keyword" => "bmowdie"],
            ["keyword" => "bmutha"],
            ["keyword" => "bnig-nog"],
            ["keyword" => "bnig"],
            ["keyword" => "bniga"],
            ["keyword" => "bnigga"],
            ["keyword" => "bnigger"],
            ["keyword" => "bnignog"],
            ["keyword" => "bnob"],
            ["keyword" => "bnobhead"],
            ["keyword" => "bnonce"],
            ["keyword" => "bnumpty"],
            ["keyword" => "bnutsack"],
            ["keyword" => "bomfg"],
            ["keyword" => "boralsex"],
            ["keyword" => "borgasm"],
            ["keyword" => "borgy"],
            ["keyword" => "bp0rn"],
            ["keyword" => "bpaedo"],
            ["keyword" => "bpaedofile"],
            ["keyword" => "bpaedophile"],
            ["keyword" => "bpecker"],
            ["keyword" => "bpederast"],
            ["keyword" => "bpedofile"],
            ["keyword" => "bpedophile"],
            ["keyword" => "bpenis"],
            ["keyword" => "bphuk"],
            ["keyword" => "bpikey"],
            ["keyword" => "bpimp"],
            ["keyword" => "bpissflaps"],
            ["keyword" => "bpisshead"],
            ["keyword" => "bpiss"],
            ["keyword" => "bponce"],
            ["keyword" => "bpoofter"],
            ["keyword" => "bpoon"],
            ["keyword" => "bpoonanie"],
            ["keyword" => "bpoontang"],
            ["keyword" => "bporn"],
            ["keyword" => "bpr0n"],
            ["keyword" => "bpron"],
            ["keyword" => "bpubes"],
            ["keyword" => "bpunani"],
            ["keyword" => "bpussy"],
            ["keyword" => "bqueef"],
            ["keyword" => "bqueer"],
            ["keyword" => "braghead"],
            ["keyword" => "braping"],
            ["keyword" => "brapist"],
            ["keyword" => "brentboy"],
            ["keyword" => "bretarded"],
            ["keyword" => "brimjob"],
            ["keyword" => "brimming"],
            ["keyword" => "bringpiece"],
            ["keyword" => "brugmuncher"],
            ["keyword" => "bs1ut"],
            ["keyword" => "bs1utd"],
            ["keyword" => "bsandnigger"],
            ["keyword" => "bschlong"],
            ["keyword" => "bscrote"],
            ["keyword" => "bscrotum"],
            ["keyword" => "bsex"],
            ["keyword" => "bshag"],
            ["keyword" => "bshagged"],
            ["keyword" => "bsheepshagger"],
            ["keyword" => "bshirtlifter"],
            ["keyword" => "bshithead"],
            ["keyword" => "bshit"],
            ["keyword" => "bshitcunt"],
            ["keyword" => "bshite"],
            ["keyword" => "bskank"],
            ["keyword" => "bslapper"],
            ["keyword" => "bslut"],
            ["keyword" => "bsmeg"],
            ["keyword" => "bsmegma"],
            ["keyword" => "bsnatch"],
            ["keyword" => "bsodding"],
            ["keyword" => "bsodomise"],
            ["keyword" => "bsodomy"],
            ["keyword" => "bsonofabitch"],
            ["keyword" => "bson-of-a-bith"],
            ["keyword" => "bspaccer"],
            ["keyword" => "bspack"],
            ["keyword" => "bspastic"],
            ["keyword" => "bspaz"],
            ["keyword" => "bsperm"],
            ["keyword" => "bspic"],
            ["keyword" => "bsplooge"],
            ["keyword" => "bspunk"],
            ["keyword" => "bstfu"],
            ["keyword" => "bstiffy"],
            ["keyword" => "bstrap-on"],
            ["keyword" => "bstrapon"],
            ["keyword" => "bsubnormal"],
            ["keyword" => "btaig"],
            ["keyword" => "bteabagged"],
            ["keyword" => "bteabagging"],
            ["keyword" => "btesticle"],
            ["keyword" => "btitwank"],
            ["keyword" => "btitties"],
            ["keyword" => "btitty"],
            ["keyword" => "btosspot"],
            ["keyword" => "btosser"],
            ["keyword" => "btowelhead"],
            ["keyword" => "btrannie"],
            ["keyword" => "btranny"],
            ["keyword" => "btubgirl"],
            ["keyword" => "btugjob"],
            ["keyword" => "bturdburglar"],
            ["keyword" => "bturd"],
            ["keyword" => "btwat"],
            ["keyword" => "bvadge"],
            ["keyword" => "bvag"],
            ["keyword" => "bvaj"],
            ["keyword" => "bwankshaft"],
            ["keyword" => "bwankstain"],
            ["keyword" => "bwank"],
            ["keyword" => "bwanker"],
            ["keyword" => "bwhore"],
            ["keyword" => "bwindowlicker"],
            ["keyword" => "bwog"],
            ["keyword" => "bwtf"],
            ["keyword" => "byid"],
            ["keyword" => "bzoophilia"],
            ["keyword" => "bbadworde"],
            ["keyword" => "bbbween"],
            ["keyword" => "bbadworda"],
            ["keyword" => "asshole"],
            ["keyword" => "blowjob"],
            ["keyword" => "cocksuck"],
            ["keyword" => "cunt"],
            ["keyword" => "fag"],
            ["keyword" => "fuck"],
            ["keyword" => "nigga"],
            ["keyword" => "nigger"],
            ["keyword" => "pussy"],
            ["keyword" => "shit"],
            ["keyword" => "slut"],
            ["keyword" => "twat"],
            ["keyword" => "warumoshi"],
            ["keyword" => "ãƒ¯ãƒ«ãƒ¢ã"]
        ];

        Keyword::insert($data);
    }
}
