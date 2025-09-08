{{--
  Template Name: Account
--}}

@extends('layouts.woocommerce')

@section('content')
  {!! do_shortcode('[woocommerce_my_account]') !!}
@endsection