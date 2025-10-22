@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Install Stremio Addon</h1>
    <form method="post" action="{{ route('addons.install') }}">
        @csrf
        <div class="input-group mb-3">
            <input type="text" name="manifest_url" class="form-control" placeholder="Enter manifest URL..." required>
            <button class="btn btn-success" type="submit">Install</button>
        </div>
    </form>
</div>
@endsection
