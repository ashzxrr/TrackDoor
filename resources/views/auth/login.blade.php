@extends('layouts.app')

@section('title', 'Login Security - TrackDoor')

@section('content')
    <main class="flex min-h-screen items-center justify-center bg-slate-900 px-4 py-8 text-slate-100">
        <div class="w-full max-w-[360px]">
            <div class="mb-6 text-center">
                <h1 class="text-3xl font-bold tracking-tight text-white">TrackDoor</h1>
                <p class="mt-2 text-sm font-medium text-slate-400">Login Security</p>
            </div>

            <section class="rounded-2xl bg-slate-800 p-6 shadow-2xl shadow-black/30 sm:p-8">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-white">Masuk ke akun</h2>
                    <p class="mt-1 text-sm text-slate-400">Gunakan akun security untuk melanjutkan.</p>
                </div>

                @if ($errors->any())
                    <div role="alert" class="mb-5 rounded-xl border border-red-400/30 bg-red-500/10 px-4 py-3 text-sm font-medium leading-6 text-red-300">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.authenticate') }}" class="flex flex-col gap-5">
                    @csrf
                    <div>
                        <label for="username" class="mb-2 block text-sm font-semibold text-slate-200">Username</label>
                        <input id="username" name="username" type="text" value="{{ old('username') }}" required autofocus autocomplete="username" class="min-h-14 w-full rounded-xl border border-slate-600 bg-slate-900 px-4 text-base text-white outline-none transition placeholder:text-slate-500 focus:border-yellow-400 focus:ring-4 focus:ring-yellow-400/15">
                    </div>
                    <div>
                        <label for="password" class="mb-2 block text-sm font-semibold text-slate-200">Password</label>
                        <input id="password" name="password" type="password" required autocomplete="current-password" class="min-h-14 w-full rounded-xl border border-slate-600 bg-slate-900 px-4 text-base text-white outline-none transition placeholder:text-slate-500 focus:border-yellow-400 focus:ring-4 focus:ring-yellow-400/15">
                    </div>
                    <button type="submit" class="min-h-14 w-full rounded-xl bg-yellow-400 px-4 text-base font-bold text-slate-900 transition hover:bg-yellow-300 focus:outline-none focus:ring-4 focus:ring-yellow-400/30 active:bg-yellow-500">
                        Login
                    </button>
                </form>
            </section>
        </div>
    </main>
@endsection