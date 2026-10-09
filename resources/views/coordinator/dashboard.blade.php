@extends("layouts.app")

@section("title", "Dashboard - Coordenador")

@section("content")
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Dashboard do Coordenador</h4>
                </div>
                <div class="card-body">
                    <h3>Bem-vindo, {{ Auth::user()->name }}!</h3>
                    <p class="lead">Você está logado como Coordenador.</p>
                    <hr>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Esta área permite acompanhar os dados dos ex-estudantes da sua instituição.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
