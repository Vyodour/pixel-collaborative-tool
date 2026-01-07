@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div id="app">
    <!-- Vue Login Component will be mounted here -->
    <login-page></login-page> 
</div>

<script>
    // We can mount specific Vue components here
</script>
@endsection
