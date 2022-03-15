<?php

namespace App\V1\Console\Commands;

use Illuminate\Console\Command;
use App\V1\Models\Company;
use App\V1\Models\User;

class SetupCompanyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'company:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup new company';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        while (true) {
            $companyName = trim($this->ask('Company name'));
            if ($companyName === '') {
                $this->error('Company name can not be empty');
                continue;
            }
            break;
        }

        $authorityLevel = max(1, intval($this->ask('Company authority level (default is 1)')));

        while (true) {
            $username = trim($this->ask('User login'));
            if ($username === '') {
                $this->error('Username can not be empty');
                continue;
            }
            if (User::where('login', $username)->exists()) {
                $this->error('This username has already been taken');
                continue;
            }
            break;
        }

        while (true) {
            $password = $this->secret('User password');
            if ($password === '') {
                $this->error('Password can not be empty');
                continue;
            }
            break;
        }

        while (true) {
            $passwordConfirm = $this->secret('User password confirmation');
            if ($password !== $passwordConfirm) {
                $this->error('Password confirmation does not match');
                continue;
            }
            break;
        }

        while (true) {
            $clinicName = trim($this->ask('Clinic name'));
            if ($clinicName === '') {
                $this->error('Clinic name can not be empty');
                continue;
            }
            break;
        }

        while (true) {
            $directorFirstName = trim($this->ask('Director first name'));
            if ($directorFirstName === '') {
                $this->error('Director first name can not be empty');
                continue;
            }
            break;
        }

        $directorLastName = trim($this->ask('Director last name'));

        $company = new Company();
        $company->name = $companyName;
        $company->authority_level = $authorityLevel;
        $company->status = Company::STATUS_PENDING;
        $company->config = [
            'setup' => [
                'username' => $username,
                'password' => $password,
                'employee' => [
                    'last_name' => $directorLastName,
                    'first_name' => $directorFirstName,
                ],
                'clinic' => [
                    'name' => $clinicName,
                ],
            ],
        ];
        $company->save();

        $this->info('Company setup task was scheduled');
    }
}
