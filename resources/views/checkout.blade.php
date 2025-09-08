{{--
  Template Name: Checkout
--}}

@extends('layouts.woocommerce')

@section('content')
  {!! do_shortcode('[woocommerce_checkout]') !!}
@endsection