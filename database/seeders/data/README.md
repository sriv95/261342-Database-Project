# Catalog data

One PHP file per brand, loaded by `DatabaseSeeder` (`glob('data/*.php')`). Each file returns a list of products:

```php
<?php

return [
    [
        'brand' => 'Yonex',                       // exactly: Yonex, Li-Ning, Victor, Mizuno, Kawasaki
        'category' => 'Rackets',                  // exactly: Rackets, Shoes, Shuttlecocks, Strings, Grips & Accessories, Apparel & Bags
        'name' => 'Astrox 99 Pro',                // max 120 chars
        'description' => 'One or two sentences in our own words.',
        'images' => ['images/products/yonex/astrox-99-pro.jpg'],   // paths under public/, first one is primary; [] if none found
        'image_source' => 'https://...',          // page the image came from (not stored in DB, for credit)
        'variants' => [
            // sku unique across ALL brands, max 40 chars; option max 60 chars; price in THB; stock 3-40
            ['sku' => 'YNX-AX99P-3UG5', 'option' => '3U-G5', 'price' => 7900, 'stock' => 12],
            // colour choice: use 'color' (optional); a variant can have 'color', 'option', or both
            // ['sku' => 'YNX-BG65-WHITE', 'color' => 'White', 'option' => '0.70 mm', 'price' => 320, 'stock' => 10],
        ],
    ],
];
```
