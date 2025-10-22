@extends('layouts.app')

@section('title', 'Install Addon')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">🔌 Install Addon</h2>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('addons.install') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="manifest_url" class="form-label">Manifest URL</label>
            <input type="url" class="form-control" id="manifest_url" name="manifest_url" placeholder="https://example.com/manifest.json" required>
        </div>
        <button type="submit" class="btn btn-primary">Install</button>
        <a href="{{ route('addons.list') }}" class="btn btn-secondary ms-2">View Installed Addons</a>
    </form>
</div>
@endsection
