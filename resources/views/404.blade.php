@extends('layouts.app')

@section('content')
  @include('partials.page-header')

  @if (! have_posts())
    <div class="p-8">
      <x-alert variant="destructive" closable>
        <x-lucide-alert-triangle /> 
        <x-alert.title>
          {{ __('Page Not Found', 'wordpress-quickstart') }}
        </x-alert.title>
        <x-alert.description>
          {!! __('Sorry, but the page you are trying to view does not exist.', 'wordpress-quickstart') !!}
        </x-alert.description>
      </x-alert>
    </div>

    {!! get_search_form(false) !!}
  @endif
@endsection
