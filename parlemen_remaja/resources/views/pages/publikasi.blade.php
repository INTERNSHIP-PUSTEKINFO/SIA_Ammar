@extends('layouts.app')

@section('title', 'Publikasi')

@section('content')
<div class="container mt-4">
    <h1>Publikasi</h1>
    <p>Berikut adalah daftar publikasi resmi Parlemen Remaja:</p>

    {{-- Contoh data dummy --}}
    <ul>
        <li><a href="{{ asset('uploads/publikasi1.pdf') }}" target="_blank">Buku Saku Parlemen Remaja 2025</a></li>
        <li><a href="{{ asset('uploads/publikasi2.pdf') }}" target="_blank">Laporan Kegiatan 2024</a></li>
    </ul>
</div>
@endsection
