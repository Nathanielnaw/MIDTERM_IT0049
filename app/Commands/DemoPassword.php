<?php

namespace App\Commands;

use App\Models\UserModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DemoPassword extends BaseCommand
{
    protected $group = 'Tasks';
    protected $name = 'demo:password';
    protected $description = 'Set the demo password from the local TFA4_DEMO_PASSWORD environment value.';

    public function run(array $params)
    {
        $password = (string) env('TFA4_DEMO_PASSWORD', '');
        $model = new UserModel();
        $user = $model->demoUser();
        if ($password === '' || $user === null) {
            CLI::error('Set TFA4_DEMO_PASSWORD locally and seed the demo user first.');
            return EXIT_ERROR;
        }
        $model->update($user['id'], ['password' => password_hash($password, PASSWORD_DEFAULT)]);
        CLI::write('Demo password updated.', 'green');
    }
}
