{{--
  Template Name: Checkout
--}}

@extends('layouts.checkout')

@section('content')
  {!! do_shortcode('[woocommerce_checkout]') !!}
@endsection