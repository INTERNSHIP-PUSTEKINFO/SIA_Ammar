@extends('layouts.admin')

@section('title', 'Kelola Publikasi')

@section('content')
<div class="container mt-4">
    <h1>Kelola Publikasi</h1>
    <a href="#" class="btn btn-success mb-3">Tambah Publikasi</a>

    <table class="table table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Judul</th>
                <th>File</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            {{-- Dummy Data --}}
            <tr>
                <td>1</td>
                <td>Buku Saku Parlemen Remaja</td>
                <td><a href="{{ asset('uploads/publikasi1.pdf') }}" target="_blank">Lihat</a></td>
                <td>
                    <a href="#" class="btn btn-warning btn-sm">Edit</a>
                    <a href="#" class="btn btn-danger btn-sm">Hapus</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
 
