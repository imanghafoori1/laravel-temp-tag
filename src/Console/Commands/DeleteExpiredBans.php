<?php

namespace Imanghafoori\Tags\Console\Commands;

use Illuminate\Console\Command;
use Imanghafoori\Tags\Services\TagService;

class DeleteExpiredBans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tag:delete-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired temporary tag models.';

    protected $service;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        app(TagService::class)->deleteExpiredTags();
    }
}
