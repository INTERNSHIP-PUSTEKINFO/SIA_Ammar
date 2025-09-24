@extends('layouts.admin')

@section('title', 'Kelola Pengumuman')

@section('content')
<div class="container mt-4">
    <h1>Kelola Pengumuman</h1>
    <a href="#" class="btn btn-success mb-3">Tambah Pengumuman</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Judul</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            {{-- Dummy Data --}}
            <tr>
                <td>1</td>
                <td>Seleksi Tahap 1 Dibuka</td>
                <td>01-09-2025</td>
                <td>
                    <a href="#" class="btn btn-warning btn-sm">Edit</a>
                    <a href="#" class="btn btn-danger btn-sm">Hapus</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
 
