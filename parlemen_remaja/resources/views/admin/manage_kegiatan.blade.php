@extends('layouts.admin')

@section('title', 'Kelola Kegiatan')

@section('content')
<div class="container mt-4">
    <h1>Kelola Kegiatan</h1>
    <a href="#" class="btn btn-success mb-3">Tambah Kegiatan</a>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Nama Kegiatan</th>
                <th>Tanggal</th>
                <th>Lokasi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            {{-- Dummy Data --}}
            <tr>
                <td>1</td>
                <td>Simulasi Sidang</td>
                <td>05-10-2025</td>
                <td>Jakarta</td>
                <td>
                    <a href="#" class="btn btn-warning btn-sm">Edit</a>
                    <a href="#" class="btn btn-danger btn-sm">Hapus</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
 
