<?php

namespace LaravelModular\Commands;

use Illuminate\Support\Str;

class MakeNotificationCommand extends BaseCommand
{
    protected $signature = 'module:notification {module : Module name} {name : Notification name}';
    protected $description = 'Create a new notification in a module';

    public function handle(): int
    {
        $module    = Str::studly($this->argument('module'));
        $name      = Str::studly($this->argument('name'));
        $namespace = $this->moduleNamespace() . '\\' . $module;
        $path      = $this->modulePath($module, "Notifications/{$name}.php");

        if ($this->files->exists($path)) {
            $this->components->warn("Notification [{$name}] already exists!");
            return self::FAILURE;
        }

        $contents = <<<PHP
<?php

namespace {$namespace}\\Notifications;

use Illuminate\\Bus\\Queueable;
use Illuminate\\Contracts\\Queue\\ShouldQueue;
use Illuminate\\Notifications\\Messages\\MailMessage;
use Illuminate\\Notifications\\Notification;

class {$name} extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via(mixed \$notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed \$notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('{$name}')
            ->line('The introduction to the notification.')
            ->action('Action Text', url('/'))
            ->line('Thank you for using our application!');
    }

    public function toArray(mixed \$notifiable): array
    {
        return [];
    }
}
PHP;
        $this->writeFile($path, $contents);
        $this->success("Notification [{$name}] created at [{$path}]");
        return self::SUCCESS;
    }
}
