<?php

namespace haxibiao\task\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TaskInstall extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '任务模块的安装脚本 db,views ...';

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
     *
     * @return mixed
     */
    public function handle()
    {

        $this->comment('发布 Service Provider...');
        $this->callSilent('vendor:publish', ['--tag' => 'task-provider']);

        $this->comment('发布 资源文件 ...');
        $this->callSilent('task:publish', ['--force' => true]);

        $this->comment("注册 TaskServiceProvider ...");
        $this->registerServiceProvider();

        copy($this->resolveStubPath('/stubs/Task.stub'), app_path('Task.php'));
        copy($this->resolveStubPath('/stubs/Assignment.stub'), app_path('Assignment.php'));

        copy($this->resolveStubPath('/stubs/Nova/Task.stub'), app_path('Nova/Task.php'));

        copy($this->resolveStubPath('/stubs/Nova/Filters/Task/TaskType.stub'), app_path('Nova/Filters/Task/TaskType.php'));
        copy($this->resolveStubPath('/stubs/Nova/Filters/Task/TaskStatus.stub'), app_path('Nova/Filters/Task/TaskStatus.php'));

    }

    protected function resolveStubPath($stub)
    {
        return __DIR__ . $stub;
    }

    protected function registerServiceProvider()
    {
        //避免重复添加
        $appConfigPHPStr = file_get_contents(config_path('app.php'));
        if (Str::contains($appConfigPHPStr, 'TaskServiceProvider::class')) {
            return;
        }

        $namespace = "App";

        file_put_contents(config_path('app.php'), str_replace(
            "{$namespace}\\Providers\EventServiceProvider::class," . PHP_EOL,
            "{$namespace}\\Providers\EventServiceProvider::class," . PHP_EOL . "        haxibiao\\task\\TaskServiceProvider::class," . PHP_EOL,
            file_get_contents(config_path('app.php'))
        ));
    }
}
