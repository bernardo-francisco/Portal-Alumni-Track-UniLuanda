@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="card shadow">

    <div class="card-body">

        <h2>
            Bem-vindo,
            {{ auth()->user()->name }}
        </h2>

        <p>
            Login realizado com sucesso.
        </p>

    </div>

</div>

@endsection