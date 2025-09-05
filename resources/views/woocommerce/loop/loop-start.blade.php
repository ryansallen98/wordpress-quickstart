@php $cols = (int) (wc_get_loop_prop('columns') ?: 4); @endphp

<ul
  class="grid grid-cols-2 sm:grid-cols-3 lg:[grid-template-columns:repeat(var(--cols),minmax(0,1fr))] gap-8 gap-y-16 mb-8"
  style="--cols: {{ $cols }}"
>
