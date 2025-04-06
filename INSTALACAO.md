# Instruções de Instalação e Configuração do Sistema de Workflow

Este documento contém instruções detalhadas para configurar o ambiente de desenvolvimento e executar o sistema de workflow.

## Pré-requisitos

- Docker e Docker Compose instalados
- Git
- Composer (opcional, caso queira executar comandos fora do container)
- Node.js e NPM (opcional, para desenvolvimento de frontend)

## Inicializando o Projeto do Zero

### 1. Criar a Estrutura de Diretórios

Primeiro, crie a estrutura de diretórios necessária:

```bash
mkdir -p docker/{app,nginx/conf.d,mysql}
```

### 2. Inicializar o Projeto Laravel

Dentro do container app, execute:

```bash
docker-compose up -d
docker-compose exec app bash
composer create-project laravel/laravel .
```

### 3. Configurar o Laravel

#### Instalar os Pacotes Necessários

```bash
composer require laravel/horizon
composer require laravel/sanctum
composer require spatie/laravel-permission
composer require intervention/image
composer require league/flysystem-aws-s3-v3
```

#### Publicar os Assets

```bash
php artisan horizon:install
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### 4. Estrutura de Domínio

Crie a estrutura básica para seguir o Domain-Driven Design:

```bash
mkdir -p app/Domain/{Workflow,User,Process,Notification}
mkdir -p app/Domain/Workflow/{Models,Services,Repositories,Events,Jobs,Listeners}
mkdir -p app/Domain/User/{Models,Services,Repositories,Events,Jobs,Listeners}
mkdir -p app/Domain/Process/{Models,Services,Repositories,Events,Jobs,Listeners}
mkdir -p app/Domain/Notification/{Models,Services,Repositories,Events,Jobs,Listeners}
```

### 5. Criar Migrations Base

Crie as migrations para as tabelas principais:

```bash
php artisan make:migration create_workflows_table
php artisan make:migration create_workflow_stages_table
php artisan make:migration create_workflow_transitions_table
php artisan make:migration create_processes_table
php artisan make:migration create_process_histories_table
```

Edite as migrations conforme a estrutura detalhada abaixo:

#### Workflows Table

```php
Schema::create('workflows', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->boolean('is_active')->default(true);
    $table->foreignId('created_by')->constrained('users');
    $table->timestamps();
});
```

#### Workflow Stages Table

```php
Schema::create('workflow_stages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('workflow_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->text('description')->nullable();
    $table->integer('order')->default(0);
    $table->string('type')->default('manual'); // manual, automatic, conditional
    $table->json('config')->nullable();
    $table->timestamps();
});
```

#### Workflow Transitions Table

```php
Schema::create('workflow_transitions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('workflow_id')->constrained()->onDelete('cascade');
    $table->foreignId('from_stage_id')->constrained('workflow_stages')->onDelete('cascade');
    $table->foreignId('to_stage_id')->constrained('workflow_stages')->onDelete('cascade');
    $table->json('condition')->nullable();
    $table->string('trigger_type')->default('manual'); // manual, automatic, scheduled
    $table->timestamps();
});
```

#### Processes Table

```php
Schema::create('processes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('workflow_id')->constrained();
    $table->nullableMorphs('reference');
    $table->string('title');
    $table->text('description')->nullable();
    $table->foreignId('current_stage_id')->constrained('workflow_stages');
    $table->string('status')->default('active'); // active, on_hold, completed, cancelled
    $table->json('data')->nullable();
    $table->foreignId('created_by')->constrained('users');
    $table->foreignId('assigned_to')->nullable()->constrained('users');
    $table->timestamps();
});
```

#### Process History Table

```php
Schema::create('process_histories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('process_id')->constrained()->onDelete('cascade');
    $table->foreignId('from_stage_id')->nullable()->constrained('workflow_stages');
    $table->foreignId('to_stage_id')->nullable()->constrained('workflow_stages');
    $table->string('action');
    $table->text('comments')->nullable();
    $table->foreignId('performed_by')->constrained('users');
    $table->timestamps();
});
```

### 6. Modelos Base

Crie os modelos principais de acordo com as migrations:

#### Workflow Model

```php
namespace App\Domain\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domain\Workflow\Models\WorkflowStage;
use App\Domain\Workflow\Models\WorkflowTransition;
use App\Domain\Process\Models\Process;
use App\Models\User;

class Workflow extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'created_by',
    ];

    public function stages()
    {
        return $this->hasMany(WorkflowStage::class)->orderBy('order');
    }

    public function transitions()
    {
        return $this->hasMany(WorkflowTransition::class);
    }

    public function processes()
    {
        return $this->hasMany(Process::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
```

#### WorkflowStage Model

```php
namespace App\Domain\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domain\Workflow\Models\Workflow;
use App\Domain\Workflow\Models\WorkflowTransition;

class WorkflowStage extends Model
{
    protected $fillable = [
        'workflow_id',
        'name',
        'description',
        'order',
        'type',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function incomingTransitions()
    {
        return $this->hasMany(WorkflowTransition::class, 'to_stage_id');
    }

    public function outgoingTransitions()
    {
        return $this->hasMany(WorkflowTransition::class, 'from_stage_id');
    }
}
```

### 7. Serviços Base

Crie os serviços principais para gerenciar workflows e processos:

#### WorkflowService

```php
namespace App\Domain\Workflow\Services;

use App\Domain\Workflow\Models\Workflow;
use App\Domain\Workflow\Models\WorkflowStage;
use App\Domain\Workflow\Models\WorkflowTransition;
use Illuminate\Support\Facades\DB;

class WorkflowService
{
    public function createWorkflow(array $data)
    {
        return DB::transaction(function () use ($data) {
            $workflow = Workflow::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'created_by' => auth()->id(),
            ]);

            if (isset($data['stages']) && is_array($data['stages'])) {
                foreach ($data['stages'] as $order => $stageData) {
                    $stage = $workflow->stages()->create([
                        'name' => $stageData['name'],
                        'description' => $stageData['description'] ?? null,
                        'order' => $order,
                        'type' => $stageData['type'] ?? 'manual',
                        'config' => $stageData['config'] ?? null,
                    ]);
                }
            }

            return $workflow;
        });
    }

    public function updateWorkflow(Workflow $workflow, array $data)
    {
        return DB::transaction(function () use ($workflow, $data) {
            $workflow->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? $workflow->description,
                'is_active' => $data['is_active'] ?? $workflow->is_active,
            ]);

            return $workflow;
        });
    }

    public function addStage(Workflow $workflow, array $stageData)
    {
        $lastOrder = $workflow->stages()->max('order') ?? -1;
        
        return $workflow->stages()->create([
            'name' => $stageData['name'],
            'description' => $stageData['description'] ?? null,
            'order' => $lastOrder + 1,
            'type' => $stageData['type'] ?? 'manual',
            'config' => $stageData['config'] ?? null,
        ]);
    }

    public function addTransition(Workflow $workflow, array $transitionData)
    {
        return $workflow->transitions()->create([
            'from_stage_id' => $transitionData['from_stage_id'],
            'to_stage_id' => $transitionData['to_stage_id'],
            'condition' => $transitionData['condition'] ?? null,
            'trigger_type' => $transitionData['trigger_type'] ?? 'manual',
        ]);
    }
}
```

#### ProcessService

```php
namespace App\Domain\Process\Services;

use App\Domain\Process\Models\Process;
use App\Domain\Workflow\Models\Workflow;
use App\Domain\Workflow\Models\WorkflowStage;
use Illuminate\Support\Facades\DB;
use App\Domain\Process\Events\ProcessCreated;
use App\Domain\Process\Events\ProcessStageChanged;

class ProcessService
{
    public function createProcess(array $data)
    {
        return DB::transaction(function () use ($data) {
            $workflow = Workflow::findOrFail($data['workflow_id']);
            $firstStage = $workflow->stages()->orderBy('order')->first();
            
            if (!$firstStage) {
                throw new \Exception('Workflow has no stages');
            }
            
            $process = Process::create([
                'workflow_id' => $workflow->id,
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'current_stage_id' => $firstStage->id,
                'status' => 'active',
                'data' => $data['data'] ?? null,
                'created_by' => auth()->id(),
                'assigned_to' => $data['assigned_to'] ?? null,
            ]);
            
            $process->histories()->create([
                'to_stage_id' => $firstStage->id,
                'action' => 'process_created',
                'comments' => $data['comments'] ?? null,
                'performed_by' => auth()->id(),
            ]);
            
            event(new ProcessCreated($process));
            
            return $process;
        });
    }
    
    public function moveToNextStage(Process $process, array $data)
    {
        return DB::transaction(function () use ($process, $data) {
            $currentStage = $process->currentStage;
            $transition = $currentStage->outgoingTransitions()
                ->where('to_stage_id', $data['to_stage_id'])
                ->first();
                
            if (!$transition) {
                throw new \Exception('Invalid transition');
            }
            
            $fromStageId = $process->current_stage_id;
            $process->current_stage_id = $data['to_stage_id'];
            $process->assigned_to = $data['assigned_to'] ?? $process->assigned_to;
            $process->save();
            
            $process->histories()->create([
                'from_stage_id' => $fromStageId,
                'to_stage_id' => $data['to_stage_id'],
                'action' => 'stage_changed',
                'comments' => $data['comments'] ?? null,
                'performed_by' => auth()->id(),
            ]);
            
            event(new ProcessStageChanged($process, $fromStageId));
            
            return $process;
        });
    }
}
```

### 8. Controladores API Base

Crie os controladores base para as APIs:

```bash
php artisan make:controller API/WorkflowController --api
php artisan make:controller API/ProcessController --api
```

### 9. Configurar CORS para APIs

Edite o arquivo `config/cors.php`:

```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

### 10. Configurar o Laravel Horizon

Edite o arquivo `config/horizon.php` para otimizar o processamento de filas:

```php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['default', 'workflows', 'notifications'],
            'balance' => 'auto',
            'processes' => 10,
            'tries' => 3,
        ],
    ],
    'local' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['default', 'workflows', 'notifications'],
            'balance' => 'auto',
            'processes' => 3,
            'tries' => 3,
        ],
    ],
],
```

## Executando o Projeto

Após configurar tudo, execute o projeto com:

```bash
docker-compose up -d
```

Acesse a aplicação em:
- Frontend: http://localhost:8000
- Laravel Horizon: http://localhost:8000/horizon
- PHPMyAdmin: http://localhost:8080

## Fluxo de Desenvolvimento

1. Crie um novo branch para sua feature:
   ```bash
   git checkout -b feature/nome-da-feature
   ```

2. Desenvolva e teste localmente

3. Execute os testes:
   ```bash
   docker-compose exec app php artisan test
   ```

4. Faça o commit e push de suas alterações:
   ```bash
   git add .
   git commit -m "Descrição da alteração"
   git push origin feature/nome-da-feature
   ```

5. Crie um Pull Request para o branch principal

## Debugging

Para ativar o XDebug no ambiente de desenvolvimento:

1. Adicione ao arquivo `docker/app/php.ini`:
   ```ini
   [xdebug]
   xdebug.mode=debug
   xdebug.client_host=host.docker.internal
   xdebug.client_port=9003
   xdebug.start_with_request=yes
   ```

2. Reconstrua o container:
   ```bash
   docker-compose build app
   docker-compose up -d
   ```

3. Configure seu IDE para receber conexões do XDebug na porta 9003. 