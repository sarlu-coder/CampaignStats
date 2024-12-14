<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CampaignStats;
use App\Models\Campaign;
use Carbon\Carbon;

class ImportStatsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-stats {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import stats from CSV files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = storage_path($this->argument('filename')); 
        
        if (!file_exists($filename)) { 
          $this->error('CSV file not found!'); return; 
        } 
        $file = fopen($filename, 'r'); 
        $header = fgetcsv($file); 

        while (($row = fgetcsv($file)) !== false) {
            if((!empty($row[0]) && $row[0] != 'NULL') && (!empty($row[1]) && $row[1] != 'NULL')){

                $campaign = Campaign::where('utm_campaign','=',$row[0])->first();
                if(isset($campaign)){
                    echo 'Campaign name >>>>>>> '.$campaign->utm_campaign;
                    $dt = Carbon::create($row[2]);
                    
                    CampaignStats::create([
                        'utm_campaign' => $campaign->id,
                        'utm_term' => $row[1],
                        'monetization_timestamp' => $dt->toDateTimeString(),
                        'revenue' => $row[3],
                    ]);
                }
            }
        } 
        fclose($file); 
        $this->info('Import completed successfully!'); 
    }
}
