@extends('layouts.app')

@section('title', 'Tambah Karyawan - TrackDoor')

@section('content')
    <main class="min-h-screen bg-slate-950 px-4 py-6 text-slate-100 sm:px-6 lg:px-8"><div class="mx-auto max-w-3xl"><a href="{{ route('admin.karyawan.index') }}" class="text-sm font-semibold text-yellow-400 hover:text-yellow-300">&larr; Kembali ke data karyawan</a><h1 class="mt-3 text-2xl font-bold text-white">Tambah karyawan</h1><section class="mt-6 rounded-2xl border border-slate-800 bg-slate-900 p-4 shadow-xl sm:p-6"><form method="POST" action="{{ route('admin.karyawan.store') }}" class="space-y-6">@php($submitLabel = 'Simpan karyawan')@include('admin.karyawan.form')</form></section></div></main>
@endsection