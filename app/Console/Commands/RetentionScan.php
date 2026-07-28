<?php

namespace App\Console\Commands;

use App\Services\RetentionEngineService;
use Illuminate\Console\Command;

class RetentionScan extends Command
{
    protected $signature = 'retention:scan';

    protected $description = 'Scan customer data and generate retention scores, messages and smart tasks';

    public function handle(RetentionEngineService $service): int
    {
        $this->info('Scanning customer retention opportunities...');
        $result = $service->run();
        $this->table(['Area', 'Created / updated'], collect($result)->map(fn ($v, $k) => [$k, is_array($v) ? json_encode($v) : $v]));
        $this->info('Retention scan complete.');

        return self::SUCCESS;
    }
}
