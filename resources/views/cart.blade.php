{{--
  Template Name: Cart
--}}

@extends('layouts.app')

@section('content')
  {!! do_shortcode('[woocommerce_cart]') !!}
@endsection