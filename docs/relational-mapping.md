# Relational Mapping

Mapping of the [ER diagram](er-diagram.md) to relations. Notation: `#` prefix = primary key, `→` = foreign key, `UK` = unique, trailing `?` = nullable.

## Mapping rules applied

| ER construct | Rule | Where used |
|---|---|---|
| Strong entity | Own relation, simple attributes become columns, key attribute becomes PK. | all entities |
| 1 : N relationship | FK on the N side. | addresses, products, variants, images, orders, order_items, cart_items |
| 1 : 1 / 1 : 0..1 | FK on either side plus UNIQUE. | `carts.user_id` |
| M : N relationship | New relation holding both FKs plus relationship attributes. | `cart_items`, `order_items`, `wishlists`, `reviews` |
| Enumerated attribute | `ENUM` column. | `users.role`, `orders.status` |

## Relations

```
users(#id, name, email UK, email_verified_at?, password, phone?, role, remember_token?, created_at?, updated_at?)

addresses(#id, user_id → users, recipient, line1, district, province, postal_code, is_default, deleted_at?)

categories(#id, name, slug UK)

brands(#id, name UK)

products(#id, category_id → categories, brand_id → brands, name, description?, is_active)

product_variants(#id, product_id → products, sku UK, color?, option_name?, price, stock_qty)

product_images(#id, product_id → products, path, is_primary)

carts(#id, user_id → users UK)

cart_items(#id, cart_id → carts, variant_id → product_variants, quantity, UK(cart_id, variant_id))

orders(#id, user_id → users, address_id → addresses, total, status, placed_at)

order_items(#id, order_id → orders, variant_id → product_variants, quantity, unit_price)

wishlists(#user_id → users, #product_id → products, created_at)

reviews(#id, user_id → users, product_id → products, rating, comment?, UK(user_id, product_id))
```

`created_at` / `updated_at` (nullable timestamps) also exist on every relation except `wishlists`, which has `created_at` only. They are omitted from the compact list above and shown in the column tables below.

## Column definitions (as created in MySQL)

### users
| Column | Type | Null | Key / default |
|---|---|---|---|
| id | BIGINT UNSIGNED | no | PK, auto increment |
| name | VARCHAR(255) | no | |
| email | VARCHAR(255) | no | UK |
| email_verified_at | TIMESTAMP | yes | |
| password | VARCHAR(255) | no | bcrypt hash |
| phone | VARCHAR(20) | yes | |
| role | ENUM('customer','admin') | no | default `customer` |
| remember_token | VARCHAR(100) | yes | |
| created_at, updated_at | TIMESTAMP | yes | |

### addresses
| Column | Type | Null | Key / default |
|---|---|---|---|
| id | BIGINT UNSIGNED | no | PK |
| user_id | BIGINT UNSIGNED | no | FK users, ON DELETE CASCADE |
| recipient | VARCHAR(100) | no | |
| line1 | VARCHAR(255) | no | |
| district | VARCHAR(80) | no | |
| province | VARCHAR(80) | no | |
| postal_code | VARCHAR(10) | no | |
| is_default | TINYINT(1) | no | default 0 |
| deleted_at | TIMESTAMP | yes | soft delete |
| created_at, updated_at | TIMESTAMP | yes | |

### categories
| Column | Type | Null | Key / default |
|---|---|---|---|
| id | BIGINT UNSIGNED | no | PK |
| name | VARCHAR(50) | no | |
| slug | VARCHAR(60) | no | UK |
| created_at, updated_at | TIMESTAMP | yes | |

### brands
| Column | Type | Null | Key / default |
|---|---|---|---|
| id | BIGINT UNSIGNED | no | PK |
| name | VARCHAR(50) | no | UK |
| created_at, updated_at | TIMESTAMP | yes | |

### products
| Column | Type | Null | Key / default |
|---|---|---|---|
| id | BIGINT UNSIGNED | no | PK |
| category_id | BIGINT UNSIGNED | no | FK categories, ON DELETE RESTRICT |
| brand_id | BIGINT UNSIGNED | no | FK brands, ON DELETE RESTRICT |
| name | VARCHAR(120) | no | |
| description | TEXT | yes | |
| is_active | TINYINT(1) | no | default 1 |
| created_at, updated_at | TIMESTAMP | yes | |

### product_variants
| Column | Type | Null | Key / default |
|---|---|---|---|
| id | BIGINT UNSIGNED | no | PK |
| product_id | BIGINT UNSIGNED | no | FK products, ON DELETE CASCADE |
| sku | VARCHAR(40) | no | UK |
| color | VARCHAR(40) | yes | e.g. `Black`, `White/Red`; shown as colour choice |
| option_name | VARCHAR(60) | yes | size/spec, e.g. `4U-G5`, `EU 42`; at least one of `color` / `option_name` is set (checked in the app) |
| price | DECIMAL(10,2) | no | |
| stock_qty | INT UNSIGNED | no | default 0 |
| created_at, updated_at | TIMESTAMP | yes | |

### product_images
| Column | Type | Null | Key / default |
|---|---|---|---|
| id | BIGINT UNSIGNED | no | PK |
| product_id | BIGINT UNSIGNED | no | FK products, ON DELETE CASCADE |
| path | VARCHAR(255) | no | |
| is_primary | TINYINT(1) | no | default 0 |
| created_at, updated_at | TIMESTAMP | yes | |

### carts
| Column | Type | Null | Key / default |
|---|---|---|---|
| id | BIGINT UNSIGNED | no | PK |
| user_id | BIGINT UNSIGNED | no | FK users, UK, ON DELETE CASCADE |
| created_at, updated_at | TIMESTAMP | yes | |

### cart_items
| Column | Type | Null | Key / default |
|---|---|---|---|
| id | BIGINT UNSIGNED | no | PK |
| cart_id | BIGINT UNSIGNED | no | FK carts, ON DELETE CASCADE |
| variant_id | BIGINT UNSIGNED | no | FK product_variants, ON DELETE CASCADE |
| quantity | INT UNSIGNED | no | CHECK (quantity > 0) |
| created_at, updated_at | TIMESTAMP | yes | |

Composite UNIQUE (`cart_id`, `variant_id`): a variant appears once per cart.

### orders
| Column | Type | Null | Key / default |
|---|---|---|---|
| id | BIGINT UNSIGNED | no | PK |
| user_id | BIGINT UNSIGNED | no | FK users, ON DELETE RESTRICT |
| address_id | BIGINT UNSIGNED | no | FK addresses, ON DELETE RESTRICT |
| total | DECIMAL(12,2) | no | |
| status | ENUM('pending','paid','shipped','done','cancelled') | no | default `pending`; index (`status`, `placed_at`) |
| placed_at | TIMESTAMP | no | default CURRENT_TIMESTAMP |
| created_at, updated_at | TIMESTAMP | yes | |

### order_items
| Column | Type | Null | Key / default |
|---|---|---|---|
| id | BIGINT UNSIGNED | no | PK |
| order_id | BIGINT UNSIGNED | no | FK orders, ON DELETE CASCADE |
| variant_id | BIGINT UNSIGNED | no | FK product_variants, ON DELETE RESTRICT |
| quantity | INT UNSIGNED | no | CHECK (quantity > 0) |
| unit_price | DECIMAL(10,2) | no | price at purchase time |
| created_at, updated_at | TIMESTAMP | yes | |

### wishlists
| Column | Type | Null | Key / default |
|---|---|---|---|
| user_id | BIGINT UNSIGNED | no | PK part 1, FK users, ON DELETE CASCADE |
| product_id | BIGINT UNSIGNED | no | PK part 2, FK products, ON DELETE CASCADE |
| created_at | TIMESTAMP | no | default CURRENT_TIMESTAMP |

### reviews
| Column | Type | Null | Key / default |
|---|---|---|---|
| id | BIGINT UNSIGNED | no | PK |
| user_id | BIGINT UNSIGNED | no | FK users, ON DELETE CASCADE |
| product_id | BIGINT UNSIGNED | no | FK products, ON DELETE CASCADE |
| rating | TINYINT UNSIGNED | no | CHECK (rating BETWEEN 1 AND 5) |
| comment | TEXT | yes | |
| created_at, updated_at | TIMESTAMP | yes | |

Composite UNIQUE (`user_id`, `product_id`): one review per user per product.

## Referential actions

| Rule | Reason |
|---|---|
| CASCADE: user → addresses, cart, wishlists, reviews | Personal data goes with the account. |
| CASCADE: product → variants, images, wishlists, reviews | Child rows have no meaning without the product. |
| CASCADE: cart → cart_items, order → order_items | Line items belong to their parent. |
| RESTRICT: order_items → variants, orders → users / addresses | Sales history must not vanish. The app hides a sold product (`is_active = 0`) instead of deleting it. |
| RESTRICT: products → categories / brands | A category or brand in use cannot be deleted. |

## Normalization

- Every relation is in **3NF**: non-key attributes depend on the key only. Product facts (name, brand) are not repeated on variants; category and brand names live once in their own relations; address fields are not repeated on orders.
- **Deliberate redundancy**:
  - `order_items.unit_price` copies `product_variants.price` so history stays correct after a price change.
  - `orders.total` equals the sum of `quantity × unit_price` over its items. It is stored for fast reports and set once at checkout.
- Product attributes vary by category (weight for rackets, size for shoes). They are stored as two text columns per variant, `color` and `option_name` (size or spec), rather than an attribute-value table, which keeps the model small for a shop of this size.

## Integrity rules enforced in the application

- Checkout runs in one transaction: lock variant rows (`SELECT ... FOR UPDATE`), verify stock, create the order and lines, decrement stock, empty the cart.
- Cancelling an order returns its stock.
- Only customers with a non-cancelled order containing the product may review it.
- An address used for an order must belong to the ordering user.
