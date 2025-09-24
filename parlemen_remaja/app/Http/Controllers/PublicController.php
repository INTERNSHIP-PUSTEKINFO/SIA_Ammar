<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PublicController extends Controller
{
    // Halaman Home
    public function home()
    {
        return view('pages.home');
    }

    // Halaman Tentang
    public function tentang()
    {
        return view('pages.tentang');
    }

    // Halaman Ketentuan
    public function ketentuan()
    {
        return view('pages.ketentuan');
    }

    // Halaman Pengumuman
    public function pengumuman()
    {
        return view('pages.pengumuman');
    }

    // Halaman Publikasi
    public function publikasi()
    {
        return view('pages.publikasi');
    }

    // Halaman Kontak
    public function kontak()
    {
        return view('pages.kontak');
    }

    public function index()
{
    return view('public.index');
}

public function kegiatan()
{
    return view('pages.kegiatan');
}

}
