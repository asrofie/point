<?php

namespace App\Console\Commands;

use App\Model\Master\Group;
use App\Model\Project\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

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

            $groups = [
                'Hotel',
                'Resto',
                'Cafe',
                'Toko',
                'Warung',
                'Agen',
                'Grosir',
                'Mini Market',
            ];

            for ($i = 0; $i < count($groups); $i++) {
                if (!Group::where('name', $groups[$i])->first()) {
                    $group = new Group;
                    $group->name = $groups[$i];
                    $group->class_reference = 'Customer';
                    $group->save();
                }
            }
        }
    }
}
