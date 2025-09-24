<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function manageUsers()
    {
        return view('admin.manage_users');
    }

    public function managePengumuman()
    {
        return view('admin.manage_pengumuman');
    }

    public function managePublikasi()
    {
        return view('admin.manage_publikasi');
    }

    public function manageKegiatan()
    {
        return view('admin.manage_kegiatan');
    }
}
