@extends('layouts.app')

@section('title', 'Index')

@section('content')
<div class="container mt-5 text-center">
    <h1>Selamat Datang di Website Parlemen Remaja</h1>
    <p class="lead">
        Ini adalah halaman index utama.  
        Silakan pilih menu di atas untuk masuk ke halaman publik.
    </p>
    <a href="{{ url('/') }}" class="btn btn-primary mt-3">
        Masuk ke Halaman Utama
    </a>
</div>
@endsection
