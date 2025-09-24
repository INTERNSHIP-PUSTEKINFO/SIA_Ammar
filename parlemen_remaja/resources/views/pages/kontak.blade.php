@extends('layouts.app')

@section('title', 'Kontak')

@section('content')
<div class="container mt-4">
    <h1>Hubungi Kami</h1>
    <p>
        Jika ada pertanyaan, silakan hubungi kami melalui formulir di bawah ini.
    </p>

    <form>
        <div class="mb-3">
            <label for="nama" class="form-label">Nama</label>
            <input type="text" id="nama" class="form-control" placeholder="Masukkan nama Anda">
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" class="form-control" placeholder="Masukkan email Anda">
        </div>

        <div class="mb-3">
            <label for="pesan" class="form-label">Pesan</label>
            <textarea id="pesan" class="form-control" rows="4" placeholder="Tulis pesan Anda"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Kirim</button>
    </form>
</div>
@endsection
