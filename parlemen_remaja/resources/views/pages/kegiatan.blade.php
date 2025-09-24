@extends('layouts.app')

@section('title', 'Kegiatan')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4">Daftar Kegiatan</h1>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Kegiatan 1: Sosialisasi Parlemen Remaja</h5>
            <p class="card-text">Kegiatan ini dilaksanakan untuk memperkenalkan program Parlemen Remaja kepada siswa-siswi SMA/SMK seluruh Indonesia.</p>
            <p class="text-muted">Tanggal: 1 Oktober 2025</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Kegiatan 2: Workshop Online</h5>
            <p class="card-text">Peserta akan dibekali materi seputar sistem parlemen, demokrasi, dan kepemimpinan.</p>
            <p class="text-muted">Tanggal: 10 Oktober 2025</p>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Kegiatan 3: Simulasi Sidang</h5>
            <p class="card-text">Peserta melakukan simulasi sidang layaknya anggota DPR di ruang sidang.</p>
            <p class="text-muted">Tanggal: 20 Oktober 2025</p>
        </div>
    </div>
</div>
@endsection
