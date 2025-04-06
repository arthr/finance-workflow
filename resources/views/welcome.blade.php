<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'WorkflowSystem') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fa;
        }

        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .hero-content {
            padding: 4rem 0;
        }

        .title {
            font-size: 3rem;
            font-weight: 700;
            color: #212529;
        }

        .subtitle {
            font-size: 1.5rem;
            color: #6c757d;
            margin-bottom: 2rem;
        }

        .btn-primary {
            padding: 0.75rem 1.5rem;
            font-size: 1.1rem;
        }

        .features {
            margin-top: 3rem;
        }

        .feature-icon {
            font-size: 2.5rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container-fluid">
                <a class="navbar-brand fw-bold" href="#">{{ config('app.name', 'WorkflowSystem') }}</a>
                <div class="d-flex">
                    @if (Route::has('login'))
                    @auth
                    <a href="{{ url('/dashboard') }}" class="btn btn-outline-primary me-2">Dashboard</a>
                    @else
                    <a href="{{ route('login') }}" class="btn btn-outline-primary">Login</a>
                    @endauth
                    @endif
                </div>
            </div>
        </nav>

        <div class="hero">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="title mb-3">Sistema de Gestão de Processos e Workflows</h1>
                        <p class="subtitle">Uma solução completa para gerenciar seus processos de trabalho, otimizar fluxos e aumentar a produtividade da sua equipe.</p>
                        @if (Route::has('login'))
                        @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-primary">Acessar Dashboard</a>
                        @else
                        <a href="{{ route('login') }}" class="btn btn-primary">Começar Agora</a>
                        @endauth
                        @endif
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="https://source.unsplash.com/random/600x400/?workflow" alt="Workflow" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>

        <div class="features">
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 p-4">
                        <div class="feature-icon">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                        <h3>Workflows Personalizados</h3>
                        <p>Crie workflows adaptados às necessidades específicas da sua empresa com estágios e transições flexíveis.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 p-4">
                        <div class="feature-icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <h3>Gestão de Processos</h3>
                        <p>Acompanhe facilmente o status de cada processo e gerencie as transições entre estágios.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 p-4">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Análise de Desempenho</h3>
                        <p>Visualize métricas importantes e acompanhe o desempenho dos seus processos e workflows.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-light py-4 mt-5">
        <div class="container text-center">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'WorkflowSystem') }}. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>

</html>
