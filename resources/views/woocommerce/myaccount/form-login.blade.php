{{-- resources/views/woocommerce/myaccount/form-login.blade.php --}}
@php
    $registration_enabled = get_option('woocommerce_enable_myaccount_registration') === 'yes';
    $gen_username_off = get_option('woocommerce_registration_generate_username') === 'no';
    $gen_password_off = get_option('woocommerce_registration_generate_password') === 'no';
    $input = 'input-text';
    $label = 'input-label';
    $help = 'mt-1 block text-xs text-muted-foreground';
    $btn = 'btn btn-primary';
    $btnGhost = 'btn btn-outline';
@endphp


@push('header')
    <div class="flex items-center gap-1">
        <button type="button" class="relative rounded-md px-3 py-2 text-sm font-medium cursor-pointer"
            :class="tab === 'login' ? 'text-foreground' : 'text-muted-foreground hover:text-foreground'"
            @click="tab = 'login'">
            {{ esc_html__('Login', 'woocommerce') }}
            <span class="absolute left-0 right-0 -bottom-px h-0.5 rounded bg-primary transition-opacity"
                :class="tab === 'login' ? 'opacity-100' : 'opacity-0'"></span>
        </button>

        @if ($registration_enabled)
            <button type="button" class="relative rounded-md px-3 py-2 text-sm font-medium cursor-pointer"
                :class="tab === 'register' ? 'text-foreground' : 'text-muted-foreground hover:text-foreground'"
                @click="tab = 'register'">
                {{ esc_html__('Register', 'woocommerce') }}
                <span class="absolute left-0 right-0 -bottom-px h-0.5 rounded bg-primary transition-opacity"
                    :class="tab === 'register' ? 'opacity-100' : 'opacity-0'"></span>
            </button>
        @endif
    </div>
@endpush

@push('body')
    {{-- LOGIN --}}
    <div x-show="tab === 'login'">
        <form class="woocommerce-form woocommerce-form-login login space-y-5" method="post" novalidate>
            @php do_action('woocommerce_login_form_start'); @endphp

            <div>
                <div class="form-row">
                    <label for="username" class="{{ $label }}">
                        {{ esc_html__('Username or email address', 'woocommerce') }}
                        <span class="required text-destructive" aria-hidden="true">*</span>
                        <span class="sr-only">{{ esc_html__('Required', 'woocommerce') }}</span>
                    </label>
                    <input type="text" id="username" name="username" class="{{ $input }}" autocomplete="username"
                        value="{{ !empty($_POST['username']) && is_string($_POST['username']) ? esc_attr(wp_unslash($_POST['username'])) : '' }}"
                        required aria-required="true" />
                </div>

                <div class="form-row">
                    <label for="password" class="{{ $label }}">
                        {{ esc_html__('Password', 'woocommerce') }}
                        <span class="required text-destructive" aria-hidden="true">*</span>
                        <span class="sr-only">{{ esc_html__('Required', 'woocommerce') }}</span>
                    </label>
                    <input type="password" id="password" name="password"
                        class="{{ $input }} woocommerce-Input woocommerce-Input--password" autocomplete="current-password"
                        required aria-required="true" />
                </div>
            </div>

            @php do_action('woocommerce_login_form'); @endphp

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <label for="rememberme" class="checkbox">
                    <input name="rememberme" type="checkbox" id="rememberme" value="forever" />
                    <span>{{ esc_html__('Remember me', 'woocommerce') }}</span>
                </label>

                <div class="flex items-center gap-3">
                    @php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); @endphp
                    <button type="submit" class="{{ $btn }} woocommerce-form-login__submit" name="login"
                        value="{{ esc_attr__('Log in', 'woocommerce') }}">
                        {{ esc_html__('Log in', 'woocommerce') }}
                    </button>
                </div>
            </div>

            <p class="woocommerce-LostPassword lost_password text-sm">
                <a class="underline hover:no-underline!" href="{{ esc_url(wp_lostpassword_url()) }}">
                    {{ esc_html__('Lost your password?', 'woocommerce') }}
                </a>
            </p>

            @php do_action('woocommerce_login_form_end'); @endphp
        </form>
    </div>

    {{-- REGISTER --}}
    @if ($registration_enabled)
        <div x-show="tab === 'register'">
            <form method="post" class="woocommerce-form woocommerce-form-register register space-y-5" @php do_action('woocommerce_register_form_tag'); @endphp>
                @php do_action('woocommerce_register_form_start'); @endphp

                <div>
                    @if ($gen_username_off)
                        <div class="form-row">
                            <label for="reg_username" class="{{ $label }}">
                                {{ esc_html__('Username', 'woocommerce') }}
                                <span class="required text-destructive" aria-hidden="true">*</span>
                                <span class="sr-only">{{ esc_html__('Required', 'woocommerce') }}</span>
                            </label>
                            <input type="text" id="reg_username" name="username" class="{{ $input }}" autocomplete="username"
                                value="{{ !empty($_POST['username']) ? esc_attr(wp_unslash($_POST['username'])) : '' }}" required
                                aria-required="true" />
                        </div>
                    @endif

                    <div class="form-row">
                        <label for="reg_email" class="{{ $label }}">
                            {{ esc_html__('Email address', 'woocommerce') }}
                            <span class="required text-destructive" aria-hidden="true">*</span>
                            <span class="sr-only">{{ esc_html__('Required', 'woocommerce') }}</span>
                        </label>
                        <input type="email" id="reg_email" name="email" class="{{ $input }}" autocomplete="email"
                            value="{{ !empty($_POST['email']) ? esc_attr(wp_unslash($_POST['email'])) : '' }}" required
                            aria-required="true" />
                    </div>

                    @if ($gen_password_off)
                        <div class="form-row">
                            <label for="reg_password" class="{{ $label }}">
                                {{ esc_html__('Password', 'woocommerce') }}
                                <span class="required text-destructive" aria-hidden="true">*</span>
                                <span class="sr-only">{{ esc_html__('Required', 'woocommerce') }}</span>
                            </label>
                            <input type="password" id="reg_password" name="password"
                                class="{{ $input }} woocommerce-Input woocommerce-Input--password" autocomplete="new-password"
                                required aria-required="true" />
                        </div>
                    @else
                        <p class="{{ $help }}">
                            {{ esc_html__('A link to set a new password will be sent to your email address.', 'woocommerce') }}
                        </p>
                    @endif
                </div>

                @php do_action('woocommerce_register_form'); @endphp

                <div class="flex items-center gap-3">
                    @php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); @endphp
                    <button type="submit" class="{{ $btn }} woocommerce-form-register__submit" name="register"
                        value="{{ esc_attr__('Register', 'woocommerce') }}">
                        {{ esc_html__('Register', 'woocommerce') }}
                    </button>
                    <button type="button" class="{{ $btnGhost }}" @click="tab='login'">
                        {{ esc_html__('I already have an account', 'woocommerce') }}
                    </button>
                </div>

                @php do_action('woocommerce_register_form_end'); @endphp
            </form>
        </div>
    @endif
@endpush

@push('footer')
    {{-- Toggle between Login/Register on small screens --}}
    @if ($registration_enabled)
        <div class="mt-4 text-center text-sm text-muted-foreground">
            <template x-if="tab === 'login'">
                <p>
                    {!! esc_html__("Don't have an account?", 'woocommerce') !!}
                    <button type="button" class="underline hover:no-underline cursor-pointer"
                        @click="tab='register'">{{ esc_html__('Register', 'woocommerce') }}</button>
                </p>
            </template>
            <template x-if="tab === 'register'">
                <p>
                    {!! esc_html__('Already have an account?', 'woocommerce') !!}
                    <button type="button" class="underline hover:no-underline cursor-pointer"
                        @click="tab='login'">{{ esc_html__('Log in', 'woocommerce') }}</button>
                </p>
            </template>
        </div>
    @endif
@endpush

@php do_action('woocommerce_before_customer_login_form'); @endphp

<div x-data="{
        tab: '{{ $registration_enabled ? (isset($_POST['register']) ? 'register' : 'login') : 'login' }}'
      }">
    @include(
        'woocommerce.layouts.auth-shell',
        [
            'title' => __('Welcome back', 'woocommerce'),
        ]
    )
</div>

@php do_action('woocommerce_after_customer_login_form'); @endphp