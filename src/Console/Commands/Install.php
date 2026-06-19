<?php

namespace Webkul\Google\Console\Commands;

use Illuminate\Console\Command;
use Webkul\Google\Database\Seeders\DatabaseSeeder;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:install {--seed : Seed demo Google Integration data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Krayin Google Integration package.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Installing Krayin Google Integration...');

        $this->comment('Running migrations...');

        $this->call('migrate');

        $this->comment('Publishing assets...');

        $this->call('vendor:publish', [
            '--provider' => 'Webkul\Google\Providers\GoogleServiceProvider',
            '--tag'      => 'public',
            '--force'    => true,
        ]);

        if ($this->option('seed')) {
            $this->comment('Seeding demo data...');

            $this->call('db:seed', [
                '--class' => DatabaseSeeder::class,
                '--force' => true,
            ]);
        }

        $this->info('Krayin Google Integration has been installed successfully.');

        $this->warn('Remember to set GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in your .env file.');
    }
}
