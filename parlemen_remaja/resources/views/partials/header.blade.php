@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
<div class="container mt-4">
    <h1 class="mb-3">Selamat Datang di Parlemen Remaja</h1>
    <p>
        Parlemen Remaja adalah program pembelajaran politik bagi generasi muda 
        agar lebih mengenal demokrasi, parlemen, dan kepemimpinan.
    </p>
    <a href="{{ route('tentang') }}" class="btn btn-primary mt-3">Pelajari Lebih Lanjut</a>
</div>
@endsection
