@php
    global $product;
@endphp

@if(isset($product) && ($priceHtml = $product->get_price_html()))
    <span class="text-primary text-sm mb-2 block font-bold my-2">{!! $priceHtml !!}</span>
@endif