<?php

namespace App\Console\Commands;

use App\Model\Master\Item;
use App\Model\Master\ItemUnit;
use App\Model\Project\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use App\Model\Accounting\ChartOfAccount;

class AlterData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:alter-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Temporary';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $projects = Project::where('group', 'kopibara')->get();
        foreach ($projects as $project) {
            $this->line('Clone '.$project->code);
            Artisan::call('tenant:database:backup-clone', ['project_code' => strtolower($project->code)]);
            $this->line('Alter '.$project->code);
            config()->set('database.connections.tenant.database', env('DB_DATABASE').'_'.strtolower($project->code));
            DB::connection('tenant')->reconnect();

            $account = ChartOfAccount::where('name', 'sediaan barang jadi (manufaktur)')->first();

            $items = [
                'B001 REGULER BUBUK 250GR',
                'B005 REGULER KOPI GULA 20GR',
                'B008 REGULER CUP HOREKA 1KG',
                'B011 REGULER 3 IN 1 BULK 1KG',
                'R001 PREMIUM PACK 70GR',
                'R002 PREMIUM PACK 5GR',
                'R003 PREMIUM CUP KOPI GULA',
                'R008 PREMIUM BIJI SEAL PACK 1KG',
                'R012 PREMIUM PACK HOREKA 1KG',
                'Y007 NEW GEN BULK PACK 1KG',
            ];

            for ($i = 0; $i < count($items); $i++) {
                if (Item::where('name', $items[$i])->first()) {
                    continue;
                }
                $item = new Item;
                $item->chart_of_account_id = $account->id;
                $item->name = $items[$i];
                $item->save();

                $units = [
                    [
                        'label' => 'pcs',
                        'name' => 'pcs'
                    ],
                ];
                $unitsToBeInserted = [];
                foreach ($units as $unit) {
                    $itemUnit = new ItemUnit();
                    $itemUnit->fill($unit);
                    array_push($unitsToBeInserted, $itemUnit);
                }
                $item->units()->saveMany($unitsToBeInserted);
            }
        }
    }
}
