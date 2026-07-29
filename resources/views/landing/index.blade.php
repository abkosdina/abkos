@extends('landing.layout')

@section('content')
    <x-landing.header />
    <main class="overflow-hidden">
        <x-landing.hero />
        <x-landing.stats />
        <x-landing.how-it-works />
        <x-landing.listings />
        <x-landing.why-us />
        <x-landing.cta />
    </main>
    <x-landing.footer />
@endsection
