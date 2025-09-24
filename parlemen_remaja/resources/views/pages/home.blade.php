@extends('layouts.app')

@section('title', 'Parlemen Remaja')

@section('content')
    {{-- Hero Section --}}
    <section class="hero position-relative">
        <img src="{{ asset('images/gedung-dpr.jpg') }}" class="img-fluid w-100" alt="Gedung DPR">
        <div class="hero-overlay position-absolute top-50 start-50 translate-middle text-center text-white">
            <h2>Sekilas Parlemen Remaja</h2>
            <div class="p-5 bg-primary rounded-3 text-white mt-3">
                Your Content
            </div>
        </div>
    </section>

    {{-- Tujuan Parlemen Remaja --}}
    <section class="container mt-5">
        <h3 class="mb-4 fw-bold text-center">TUJUAN PARLEMEN REMAJA</h3>
        <div class="row text-center">
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm h-100">
                    <img src="{{ asset('images/tujuan1.jpg') }}" class="card-img-top" alt="Tujuan 1">
                    <div class="card-body">
                        <h5 class="card-title">Belajar Demokrasi</h5>
                        <p class="card-text">Peserta memahami sistem demokrasi Indonesia.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm h-100">
                    <img src="{{ asset('images/tujuan2.jpg') }}" class="card-img-top" alt="Tujuan 2">
                    <div class="card-body">
                        <h5 class="card-title">Simulasi Sidang</h5>
                        <p class="card-text">Mengikuti sidang seperti anggota DPR.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm h-100">
                    <img src="{{ asset('images/tujuan3.jpg') }}" class="card-img-top" alt="Tujuan 3">
                    <div class="card-body">
                        <h5 class="card-title">Kepemimpinan</h5>
                        <p class="card-text">Meningkatkan jiwa kepemimpinan generasi muda.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm h-100">
                    <img src="{{ asset('images/tujuan4.jpg') }}" class="card-img-top" alt="Tujuan 4">
                    <div class="card-body">
                        <h5 class="card-title">Kolaborasi</h5>
                        <p class="card-text">Bekerjasama antar siswa seluruh Indonesia.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Publikasi --}}
    <section class="container mt-5">
        <h3 class="mb-4 fw-bold text-center">PUBLIKASI</h3>
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm h-100">
                    <img src="{{ asset('images/pub1.jpg') }}" class="card-img-top" alt="Publikasi 1">
                    <div class="card-body">
                        <p class="card-text">Artikel atau publikasi terbaru terkait Parlemen Remaja.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm h-100">
                    <img src="{{ asset('images/pub2.jpg') }}" class="card-img-top" alt="Publikasi 2">
                    <div class="card-body">
                        <p class="card-text">Informasi kegiatan dan hasil program.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm h-100">
                    <img src="{{ asset('images/pub3.jpg') }}" class="card-img-top" alt="Publikasi 3">
                    <div class="card-body">
                        <p class="card-text">Dokumentasi kegiatan Parlemen Remaja.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm h-100">
                    <img src="{{ asset('images/pub4.jpg') }}" class="card-img-top" alt="Publikasi 4">
                    <div class="card-body">
                        <p class="card-text">Liputan media dan update terbaru.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
