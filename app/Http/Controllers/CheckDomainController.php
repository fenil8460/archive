<?php

namespace App\Http\Controllers;

use App\Jobs\CreateDomain;
use App\Models\Keyword;
use App\Models\Task;
use App\Models\Url;
use DOMDocument;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

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

    public function createTask(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url' => ['required', 'regex:/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i'],
        ]);

        $data = [
            'name' => $request->name,
            'user_id' => Auth::user()->id,
        ];

        // create new task
        $create_task = Task::create($data);
        $url = explode(',', $request->url);

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

        return redirect('list-task');
    }

    public function listTask(Request $request)
    {
        $task = Url::join('tasks', 'tasks.id', '=', 'urls.task_id')->select('urls.*', 'tasks.name')->where('user_id',Auth::user()->id)->paginate(config('app.pagination_limit'));
        foreach($task as $key=>$item){
            $status = '';
            if($item->status == 1){
                $status = 'Waiting for proccesing';
            }
            if($item->status == 2){
                $status = 'Underproccess';
            }
            if($item->status == 3){
                $status = 'Spam';
            }
            if($item->status == 4){
                $status = 'Not spam';
            }
            $task[$key]['status_name'] = $status;
        }
        return view('task.list', ['tasks' => $task]);
    }

    public function getSnapShot(Request $request)
    {
        $snapshot =  Http::get('http://web.archive.org/cdx/search/cdx?output=json&url=' . $request->url);
        $snapshot = json_decode($snapshot);
        unset($snapshot[0]);
        $data = [];
        foreach ($snapshot as $index => $item) {
            $data[$index] = [
                'url' => 'http://web.archive.org/web/' . $item[1] . '/' . $item[2],
                'timestamp' => $item[1]
            ];
        }
        return view('snapshot.list', ['snapshots' => $data]);
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
