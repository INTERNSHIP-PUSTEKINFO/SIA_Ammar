@extends('layouts.app')

@section('title', 'Pengumuman')

@section('content')
<div class="container mt-4">
    <h1>Pengumuman Terbaru</h1>

    {{-- Contoh data dummy --}}
    <div class="list-group">
        <a href="#" class="list-group-item list-group-item-action">
            Pengumuman Seleksi Tahap 1 - 2025
        </a>
        <a href="#" class="list-group-item list-group-item-action">
            Daftar Peserta Lolos Seleksi Tahap 2
        </a>
    </div>
</div>
@endsection
