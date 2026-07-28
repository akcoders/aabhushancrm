<?php

namespace App\Console\Commands;

use App\Services\SmartTaskEngineService;
use Illuminate\Console\Command;

class GenerateSmartTasks extends Command
{
    protected $signature = 'smart-tasks:generate';

    protected $description = 'Analyze CRM data and generate personalized, assigned smart tasks';

    public function handle(SmartTaskEngineService $service): int
    {
        $this->info('Generating intelligent staff work...');
        $result = $service->scanAndCreateTasks();
        $this->table(['Module', 'Tasks created'], collect($result)->map(fn ($v, $k) => [$k, $v]));
        $this->info('Smart tasks generated with duplicate prevention.');

        return self::SUCCESS;
    }
}
