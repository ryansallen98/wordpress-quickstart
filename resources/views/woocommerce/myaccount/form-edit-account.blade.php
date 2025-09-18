{{-- resources/views/woocommerce/myaccount/form-edit-account.blade.php --}}

@php
    /** @var WP_User $user */
    $input = 'input-text';
    $label = 'mb-1 block text-sm font-medium text-foreground';
    $help  = 'mt-1 block text-xs text-muted-foreground';
@endphp

@php do_action('woocommerce_before_edit_account_form'); @endphp

<form
    class="woocommerce-EditAccountForm edit-account"
    action=""
    method="post"
    @php do_action('woocommerce_edit_account_form_tag'); @endphp
>
    <div>
        <div class="pb-4 border-b border-border">
            <h2 class="text-2xl font-semibold tracking-tight text-card-foreground">
                {{ esc_html__('Account details', 'woocommerce') }}
            </h2>
        </div>

        <div class="pt-4 space-y-6">
            @php do_action('woocommerce_edit_account_form_start'); @endphp

            {{-- Name row --}}
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label for="account_first_name" class="{{ $label }}">
                        {{ esc_html__('First name', 'woocommerce') }}
                        <span class="required text-destructive" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="text"
                        id="account_first_name"
                        name="account_first_name"
                        class="{{ $input }}"
                        autocomplete="given-name"
                        value="{{ esc_attr($user->first_name) }}"
                        aria-required="true"
                        required
                    />
                </div>

                <div>
                    <label for="account_last_name" class="{{ $label }}">
                        {{ esc_html__('Last name', 'woocommerce') }}
                        <span class="required text-destructive" aria-hidden="true">*</span>
                    </label>
                    <input
                        type="text"
                        id="account_last_name"
                        name="account_last_name"
                        class="{{ $input }}"
                        autocomplete="family-name"
                        value="{{ esc_attr($user->last_name) }}"
                        aria-required="true"
                        required
                    />
                </div>
            </div>

            {{-- Display name --}}
            <div>
                <label for="account_display_name" class="{{ $label }}">
                    {{ esc_html__('Display name', 'woocommerce') }}
                    <span class="required text-destructive" aria-hidden="true">*</span>
                </label>
                <input
                    type="text"
                    id="account_display_name"
                    name="account_display_name"
                    class="{{ $input }}"
                    aria-describedby="account_display_name_description"
                    value="{{ esc_attr($user->display_name) }}"
                    aria-required="true"
                    required
                />
                <span id="account_display_name_description" class="{{ $help }}">
                    <em>{{ esc_html__('This will be how your name will be displayed in the account section and in reviews', 'woocommerce') }}</em>
                </span>
            </div>

            {{-- Email --}}
            <div>
                <label for="account_email" class="{{ $label }}">
                    {{ esc_html__('Email address', 'woocommerce') }}
                    <span class="required text-destructive" aria-hidden="true">*</span>
                </label>
                <input
                    type="email"
                    id="account_email"
                    name="account_email"
                    class="{{ $input }}"
                    autocomplete="email"
                    value="{{ esc_attr($user->user_email) }}"
                    aria-required="true"
                    required
                />
            </div>

            {{-- Extra fields hook --}}
            @php
                /**
                 * Hook where additional fields should be rendered.
                 * @since 8.7.0
                 */
                do_action('woocommerce_edit_account_form_fields');
            @endphp

            {{-- Password change --}}
            <fieldset class="border border-border rounded-xl p-4">
                <legend class="px-1 text-sm font-medium text-muted-foreground">
                    {{ esc_html__('Password change', 'woocommerce') }}
                </legend>

                <div class="grid gap-4 md:grid-cols-3">
                    <div class="md:col-span-1 woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <label for="password_current" class="{{ $label }}">
                            {{ esc_html__('Current password (leave blank to leave unchanged)', 'woocommerce') }}
                        </label>
                        <input
                            type="password"
                            id="password_current"
                            name="password_current"
                            class="woocommerce-Input woocommerce-Input--password input-text"
                            autocomplete="off"
                        />
                    </div>

                    <div class="md:col-span-1 woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <label for="password_1" class="{{ $label }}">
                            {{ esc_html__('New password (leave blank to leave unchanged)', 'woocommerce') }}
                        </label>
                        <input
                            type="password"
                            id="password_1"
                            name="password_1"
                            class="woocommerce-Input woocommerce-Input--password input-text"
                            autocomplete="off"
                        />
                    </div>

                    <div class="md:col-span-1 woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
                        <label for="password_2" class="{{ $label }}">
                            {{ esc_html__('Confirm new password', 'woocommerce') }}
                        </label>
                        <input
                            type="password"
                            id="password_2"
                            name="password_2"
                            class="woocommerce-Input woocommerce-Input--password input-text"
                            autocomplete="off"
                        />
                    </div>
                </div>
            </fieldset>

            @php
                /**
                 * My Account edit account form.
                 * @since 2.6.0
                 */
                do_action('woocommerce_edit_account_form');
            @endphp

            <div class="flex items-center gap-3 pt-2">
                @php wp_nonce_field('save_account_details', 'save-account-details-nonce'); @endphp

                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-md border border-transparent bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                    name="save_account_details"
                    value="{{ esc_attr__('Save changes', 'woocommerce') }}"
                >
                    {{ esc_html__('Save changes', 'woocommerce') }}
                </button>

                <input type="hidden" name="action" value="save_account_details" />
            </div>

            @php do_action('woocommerce_edit_account_form_end'); @endphp
        </div>
    </div>
</form>

@php do_action('woocommerce_after_edit_account_form'); @endphp