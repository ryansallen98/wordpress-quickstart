<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;
use Log1x\Navi\Navi;

class App extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        '*',
    ];

    /**
     * Retrieve the site name.
     */
    public function siteName(): string
    {
        return get_bloginfo('name', 'display');
    }


    public function mainMenu(): array
    {
        $menu = app(Navi::class)->build('primary_navigation');
        $tree = $menu ? $menu->toArray() : [];

        // Normalize to arrays (handles any nested stdClass/Collections)
        $tree = json_decode(json_encode($tree), true);

        // Attach ACF to each item (and children)
        $tree = $this->mapMenuWithAcf($tree);

        return $tree;
    }

    /**
     * Recursively attach ACF fields to each menu item in an array tree.
     *
     * @param array $items
     * @return array
     */
    protected function mapMenuWithAcf(array $items): array
    {
        foreach ($items as &$item) {
            $id = $item['id'] ?? $item['ID'] ?? null;

            if ($id) {
                // ACF usually works with the raw ID for menu items.
                // If your fields don't load, try: 'nav_menu_item_' . $id
                $acf = get_fields($id);
                if ($acf === false) {
                    $acf = get_fields('nav_menu_item_' . $id) ?: [];
                }
                $item['acf'] = is_array($acf) ? $acf : [];
            } else {
                $item['acf'] = [];
            }

            if (!empty($item['children']) && is_array($item['children'])) {
                $item['children'] = $this->mapMenuWithAcf($item['children']);
            }
        }

        return $items;
    }
}
