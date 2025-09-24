 
@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container mt-4">
    <h1 class="mb-3">Dashboard Admin</h1>
    <p>Selamat datang, Admin 👋</p>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Users</h5>
                    <p class="card-text">Jumlah User: 120</p>
                    <a href="{{ route('admin.manage_users') }}" class="btn btn-primary btn-sm">Kelola</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Pengumuman</h5>
                    <p class="card-text">Total: 10</p>
                    <a href="{{ route('admin.manage_pengumuman') }}" class="btn btn-primary btn-sm">Kelola</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-3">
                <div class="card-body text-center">
                    <h5 class="card-title">Publikasi</h5>
                    <p class="card-text">Total: 8</p>
                    <a href="{{ route('admin.manage_publikasi') }}" class="btn btn-primary btn-sm">Kelola</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
