<?php

namespace App\View\Composers\WooCommerce\MyAccount;

use Roots\Acorn\View\Composer;
use \WC_Product;

class Dashboard extends Composer
{
    protected static $views = [
        'woocommerce.myaccount.dashboard',
    ];

    public function with()
    {
        $user       = wp_get_current_user();
        $user_id    = get_current_user_id();

        // core links
        $links = [
            'orders'        => wc_get_endpoint_url('orders'),
            'addresses'     => wc_get_endpoint_url('edit-address'),
            'payment'       => wc_get_endpoint_url('payment-methods'),
            'account'       => wc_get_endpoint_url('edit-account'),
            'logout'        => wc_logout_url(),
            'shop'          => wc_get_page_permalink('shop'),
            'cart'          => wc_get_cart_url(),
            'contact'       => home_url('/contact'),
        ];

        // counts
        $statusCounts = $this->ordersByStatus($user_id);
        $openCount    = ($statusCounts['wc-processing'] ?? 0)
                      + ($statusCounts['wc-on-hold'] ?? 0)
                      + ($statusCounts['wc-pending'] ?? 0);

        // recent orders + merch (cache merch a bit)
        $recentOrders = $this->recentOrders($user_id, 5);
        $bestSellers  = $this->cached('dash_best_sellers', 15 * MINUTE_IN_SECONDS, fn () => $this->bestSellers(6));
        $saleProducts = $this->cached('dash_deals', 15 * MINUTE_IN_SECONDS, fn () => $this->onSale(6));

        return [
            'user'          => $user,
            'links'         => $links,
            'statusCounts'  => $statusCounts,
            'totalOrders'   => wc_get_customer_order_count($user_id),
            'openCount'     => $openCount,
            'recentOrders'  => $recentOrders,
            'bestSellers'   => $bestSellers,
            'saleProducts'  => $saleProducts,

            // helpers available in Blade
            'statusPill'    => fn (string $wcStatus) => $this->statusPill($wcStatus),
        ];
    }

    /** ---------------- helpers ---------------- */

    protected function cached(string $key, int $ttl, callable $cb)
    {
        $val = get_transient($key);
        if (false === $val) {
            $val = $cb();
            set_transient($key, $val, $ttl);
        }
        return $val;
    }

    protected function recentOrders(int $user_id, int $limit = 5): array
    {
        return wc_get_orders([
            'customer' => $user_id,
            'limit'    => $limit,
            'orderby'  => 'date',
            'order'    => 'DESC',
            'type'     => 'shop_order',
            'status'   => array_keys(wc_get_order_statuses()),
            'return'   => 'objects',
        ]);
    }

    protected function ordersByStatus(int $user_id): array
    {
        $buckets = ['wc-pending','wc-processing','wc-on-hold','wc-completed','wc-cancelled','wc-refunded','wc-failed'];
        $out = [];
        foreach ($buckets as $s) {
            $query = wc_get_orders([
                'customer' => $user_id,
                'status'   => $s,
                'limit'    => 1,
                'paginate' => true,
                'return'   => 'ids',
            ]);
            $out[$s] = $query->total ?? 0;
        }
        return $out;
    }

    protected function bestSellers(int $limit = 6): array
    {
        return wc_get_products([
            'status'       => 'publish',
            'limit'        => $limit,
            'orderby'      => 'meta_value_num',
            'meta_key'     => 'total_sales',
            'order'        => 'DESC',
            'return'       => 'objects',
            'stock_status' => 'instock',
            'visibility'   => 'catalog',
        ]);
    }

    protected function onSale(int $limit = 6): array
    {
        return wc_get_products([
            'status'       => 'publish',
            'limit'        => $limit,
            'on_sale'      => true,
            'orderby'      => 'date',
            'order'        => 'DESC',
            'return'       => 'objects',
            'stock_status' => 'instock',
            'visibility'   => 'catalog',
        ]);
    }

    protected function statusPill(string $status): array
    {
        $key = str_replace('wc-','',$status);
        $map = [
            'pending'    => ['Pending',    'bg-amber-100 text-amber-800'],
            'processing' => ['Processing', 'bg-blue-100 text-blue-800'],
            'on-hold'    => ['On hold',    'bg-slate-200 text-slate-800'],
            'completed'  => ['Completed',  'bg-emerald-100 text-emerald-800'],
            'cancelled'  => ['Cancelled',  'bg-rose-100 text-rose-800'],
            'refunded'   => ['Refunded',   'bg-purple-100 text-purple-800'],
            'failed'     => ['Failed',     'bg-zinc-200 text-zinc-800'],
        ];
        return $map[$key] ?? [ucfirst($key), 'bg-slate-200 text-slate-800'];
    }
}