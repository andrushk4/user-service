<?php

namespace App\Console\Commands;

use Throwable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends Command
{
    protected $signature = 'app:install';

    protected $description = 'Установка приложения';

    public function handle()
    {
        $this->info('Установка приложения...');

        if (empty(env('APP_KEY'))) {
            $this->info('Генерация ключа приложения...');
            Artisan::call('key:generate', [], $this->output);
            $this->info('Ключ приложения успешно сгенерирован.');
        } else {
            $this->warn('Ключ приложения уже установлен. Пропускаем генерацию.');
        }

        Artisan::call('config:clear', [], $this->output);

        $this->info('Запуск миграций...');
        try {
            Artisan::call('migrate', ['--force' => true], $this->output);
            $this->info('Миграции успешно выполнены.');
        } catch (Throwable $e) {
            $this->error('Ошибка при выполнении миграций: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $this->info('Приложение успешно установлено!');
        return Command::SUCCESS;
    }
}
