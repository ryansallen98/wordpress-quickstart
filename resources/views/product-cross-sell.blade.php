{{--
  Template Name: Product Cross Sell
--}}

@extends('layouts.woocommerce')

@section('content')
  {!! do_shortcode('[wc_product_cross_sell_offer]') !!}
@endsection