<div class="min-h-screen w-full bg-gradient-to-br from-background via-muted/40 to-background">
    <div class="container mx-auto flex min-h-screen items-center justify-center p-4">
        <div class="w-full max-w-xl">
            {{-- Card --}}
            <div class="overflow-hidden rounded-lg border border-border bg-card shadow-lg">
                {{-- Top: brand / heading --}}
                <div class="border-b border-border bg-muted/30 px-6 pt-6 pb-0">
                    {{-- Optional logo slot (replace with your logo) --}}
                    <div class="mb-4 flex items-center gap-3">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10">
                            <span class="h-3 w-3 rounded-sm bg-primary"></span>
                        </span>
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">{{ get_bloginfo('name') }}</p>
                            <h1 class="text-lg font-semibold text-card-foreground">{!! $title !!}</h1>
                        </div>
                    </div>

                    {!! $header ?? '' !!}
                    @empty($header)
                        @stack('header')
                    @endempty
                </div>

                {{-- Body --}}
                <div class="p-6">
                    {!! $body ?? '' !!}
                    @empty($body)
                        @stack('body')
                    @endempty
                </div>

                {{-- Bottom helper (optional) --}}
                <div
                    class="flex items-center justify-between gap-3 border-t border-border bg-muted/30 px-6 py-4 text-xs text-muted-foreground">
                    <span>&copy; {{ date('Y') }} {{ get_bloginfo('name') }}</span>
                    <div class="flex items-center gap-2">
                        <a href="{{ esc_url(home_url('/')) }}"
                            class="no-underline! hover:underline!">{{ esc_html__('Back to site', 'woocommerce') }}</a>
                    </div>
                </div>
            </div>

            <div>
                {!! $footer ?? '' !!}
                @empty($footer)
                    @stack('footer')
                @endempty
            </div>
        </div>
    </div>
</div>