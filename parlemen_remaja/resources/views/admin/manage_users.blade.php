@extends('layouts.admin')

@section('title', 'Kelola Users')

@section('content')
<div class="container mt-4">
    <h1>Kelola Users</h1>
    <a href="#" class="btn btn-success mb-3">Tambah User</a>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            {{-- Dummy Data --}}
            <tr>
                <td>1</td>
                <td>Admin Utama</td>
                <td>admin@example.com</td>
                <td>Admin</td>
                <td>
                    <a href="#" class="btn btn-warning btn-sm">Edit</a>
                    <a href="#" class="btn btn-danger btn-sm">Hapus</a>
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
 
