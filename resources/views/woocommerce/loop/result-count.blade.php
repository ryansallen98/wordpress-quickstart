@if(!empty($wc_result_count_html))
  <p
    class="text-sm text-muted-foreground"
    role="alert"
    aria-relevant="all"
    @if($wc_result_is_sorted) data-is-sorted-by="true" @endif
  >
    {!! $wc_result_count_html !!}
  </p>
@endif