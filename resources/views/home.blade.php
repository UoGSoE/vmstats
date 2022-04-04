@extends('layouts.app')
@section('content')

                <h1 class="title is-3">
                    VM Stats
                    @auth
                        <form method="POST" action="{{ route('auth.logout') }}" class="is-pulled-right">
                            @csrf
                            <button class="button">Logout</button>
                        </form>
                    @endauth
                </h1>
                <hr>
                @livewire('vm-list')
@endsection
