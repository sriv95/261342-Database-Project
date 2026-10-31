"""Generates docs/schema-design.svg: relational schema (tables, keys, FK arrows), dark theme. Run: python3 docs/src/schema_design.py"""
import html, pathlib

OUT = pathlib.Path(__file__).resolve().parent.parent / 'schema-design.svg'
BG, GRID = '#1b1b1b', '#262626'
RH, HH, TW = 25, 38, 400

# name: (x, y, [(col, type, flags)]) flags: PK FK UK NULL, plus note
T = {
 'users': (60, 90, [('id', 'BIGINT UNSIGNED', 'PK'), ('name', 'VARCHAR(255)', ''), ('email', 'VARCHAR(255)', 'UK'),
     ('email_verified_at', 'TIMESTAMP', 'NULL'), ('password', 'VARCHAR(255)', ''), ('phone', 'VARCHAR(20)', 'NULL'),
     ('role', "ENUM('customer','admin')", ''), ('remember_token', 'VARCHAR(100)', 'NULL'), ('created_at, updated_at', 'TIMESTAMP', 'NULL')]),
 'addresses': (60, 440, [('id', 'BIGINT UNSIGNED', 'PK'), ('user_id', 'BIGINT UNSIGNED', 'FK'), ('recipient', 'VARCHAR(100)', ''),
     ('line1', 'VARCHAR(255)', ''), ('district', 'VARCHAR(80)', ''), ('province', 'VARCHAR(80)', ''), ('postal_code', 'VARCHAR(10)', ''),
     ('is_default', 'TINYINT(1)', ''), ('deleted_at', 'TIMESTAMP', 'NULL')]),
 'orders': (60, 790, [('id', 'BIGINT UNSIGNED', 'PK'), ('user_id', 'BIGINT UNSIGNED', 'FK'), ('address_id', 'BIGINT UNSIGNED', 'FK'),
     ('total', 'DECIMAL(12,2)', ''), ('status', 'ENUM (5 statuses)', ''), ('placed_at', 'TIMESTAMP', ''),
     ('created_at, updated_at', 'TIMESTAMP', 'NULL')]),
 'carts': (700, 90, [('id', 'BIGINT UNSIGNED', 'PK'), ('user_id', 'BIGINT UNSIGNED', 'FK UK'), ('created_at, updated_at', 'TIMESTAMP', 'NULL')]),
 'cart_items': (700, 270, [('id', 'BIGINT UNSIGNED', 'PK'), ('cart_id', 'BIGINT UNSIGNED', 'FK'), ('variant_id', 'BIGINT UNSIGNED', 'FK'),
     ('quantity', 'INT UNSIGNED', 'CHECK > 0'), ('created_at, updated_at', 'TIMESTAMP', 'NULL')]),
 'order_items': (700, 790, [('id', 'BIGINT UNSIGNED', 'PK'), ('order_id', 'BIGINT UNSIGNED', 'FK'), ('variant_id', 'BIGINT UNSIGNED', 'FK'),
     ('quantity', 'INT UNSIGNED', 'CHECK > 0'), ('unit_price', 'DECIMAL(10,2)', ''), ('created_at, updated_at', 'TIMESTAMP', 'NULL')]),
 'wishlists': (700, 1100, [('user_id', 'BIGINT UNSIGNED', 'PK FK'), ('product_id', 'BIGINT UNSIGNED', 'PK FK'), ('created_at', 'TIMESTAMP', '')]),
 'reviews': (700, 1270, [('id', 'BIGINT UNSIGNED', 'PK'), ('user_id', 'BIGINT UNSIGNED', 'FK'), ('product_id', 'BIGINT UNSIGNED', 'FK'),
     ('rating', 'TINYINT UNSIGNED', 'CHECK 1..5'), ('comment', 'TEXT', 'NULL'), ('created_at, updated_at', 'TIMESTAMP', 'NULL')]),
 'product_variants': (1340, 90, [('id', 'BIGINT UNSIGNED', 'PK'), ('product_id', 'BIGINT UNSIGNED', 'FK'), ('sku', 'VARCHAR(40)', 'UK'),
     ('color', 'VARCHAR(40)', 'NULL'), ('option_name', 'VARCHAR(60)', 'NULL'), ('price', 'DECIMAL(10,2)', ''),
     ('stock_qty', 'INT UNSIGNED', ''), ('created_at, updated_at', 'TIMESTAMP', 'NULL')]),
 'products': (1340, 470, [('id', 'BIGINT UNSIGNED', 'PK'), ('category_id', 'BIGINT UNSIGNED', 'FK'), ('brand_id', 'BIGINT UNSIGNED', 'FK'),
     ('name', 'VARCHAR(120)', ''), ('description', 'TEXT', 'NULL'), ('is_active', 'TINYINT(1)', ''), ('created_at, updated_at', 'TIMESTAMP', 'NULL')]),
 'product_images': (1340, 790, [('id', 'BIGINT UNSIGNED', 'PK'), ('product_id', 'BIGINT UNSIGNED', 'FK'), ('path', 'VARCHAR(255)', ''),
     ('is_primary', 'TINYINT(1)', ''), ('created_at, updated_at', 'TIMESTAMP', 'NULL')]),
 'categories': (1980, 470, [('id', 'BIGINT UNSIGNED', 'PK'), ('name', 'VARCHAR(50)', ''), ('slug', 'VARCHAR(60)', 'UK'), ('created_at, updated_at', 'TIMESTAMP', 'NULL')]),
 'brands': (1980, 680, [('id', 'BIGINT UNSIGNED', 'PK'), ('name', 'VARCHAR(50)', 'UK'), ('created_at, updated_at', 'TIMESTAMP', 'NULL')]),
}
# fk: (table, column) -> (target table, delete rule)
FK = [
 ('addresses', 'user_id', 'users', 'CASCADE'), ('carts', 'user_id', 'users', 'CASCADE'),
 ('orders', 'user_id', 'users', 'RESTRICT'), ('orders', 'address_id', 'addresses', 'RESTRICT'),
 ('cart_items', 'cart_id', 'carts', 'CASCADE'), ('cart_items', 'variant_id', 'product_variants', 'CASCADE'),
 ('order_items', 'order_id', 'orders', 'CASCADE'), ('order_items', 'variant_id', 'product_variants', 'RESTRICT'),
 ('wishlists', 'user_id', 'users', 'CASCADE'), ('wishlists', 'product_id', 'products', 'CASCADE'),
 ('reviews', 'user_id', 'users', 'CASCADE'), ('reviews', 'product_id', 'products', 'CASCADE'),
 ('product_variants', 'product_id', 'products', 'CASCADE'), ('product_images', 'product_id', 'products', 'CASCADE'),
 ('products', 'category_id', 'categories', 'RESTRICT'), ('products', 'brand_id', 'brands', 'RESTRICT'),
]
EXTRA = {  # composite constraints shown under the table
 'cart_items': 'UNIQUE (cart_id, variant_id)', 'reviews': 'UNIQUE (user_id, product_id)',
 'wishlists': 'PRIMARY KEY (user_id, product_id)', 'orders': 'INDEX (status, placed_at)',
}
W, H = 2580, 1520


def box_h(name):
    return HH + RH * len(T[name][2]) + (22 if name in EXTRA else 0)


def row_y(name, col):
    x, y, cols = T[name]
    i = [c[0] for c in cols].index(col)
    return y + HH + RH * i + RH / 2


o = []
a = o.append
a(f'<svg xmlns="http://www.w3.org/2000/svg" width="{W}" height="{H}" viewBox="0 0 {W} {H}" font-family="Helvetica, Arial, sans-serif">')
a(f'<defs><pattern id="g" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M40 0H0V40" fill="none" stroke="{GRID}"/></pattern>'
  '<marker id="ar" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="9" markerHeight="9" orient="auto-start-reverse"><path d="M0 0L10 5L0 10z" fill="#9a85d0"/></marker></defs>')
a(f'<rect width="{W}" height="{H}" fill="{BG}"/><rect width="{W}" height="{H}" fill="url(#g)"/>')
a('<g transform="translate(90,0)">')
a('<text x="-50" y="56" fill="#fff" font-size="30" font-weight="700">Smash Badminton Shop: relational schema design (MySQL)</text>')

# FK arrows first (under boxes)
loop = {}
for src, col, dst, rule in FK:
    sx, sy, _ = T[src]; dx, dy, _ = T[dst]
    y1 = row_y(src, col); y2 = row_y(dst, 'id' if dst != 'x' else 'id')
    if sx == dx:  # same column: loop on the left side
        n = loop.get(sx, 0); loop[sx] = n + 1
        lx = sx - 28 - n * 16
        d = f'M{sx},{y1} C{lx},{y1} {lx},{y2} {sx},{y2}'
        x1, x2 = sx, sx
        lab = (sx - 20, y1 - 6)
    elif sx < dx:
        x1, x2 = sx + TW, dx
        mx = (x1 + x2) / 2
        d = f'M{x1},{y1} C{mx},{y1} {mx},{y2} {x2},{y2}'
    else:
        x1, x2 = sx, dx + TW
        mx = (x1 + x2) / 2
        d = f'M{x1},{y1} C{mx},{y1} {mx},{y2} {x2},{y2}'
    a(f'<path d="{d}" fill="none" stroke="#9a85d0" stroke-width="1.8" marker-end="url(#ar)" opacity="0.9"/>')
    # rule label near source end
    tx = x1 + (14 if sx < dx else -14) if sx != dx else sx - 14
    anchor = 'start' if sx < dx else 'end'
    if sx == dx: anchor = 'end'
    a(f'<text x="{tx}" y="{y1-5}" fill="#b8a8e8" font-size="11" text-anchor="{anchor}" paint-order="stroke" stroke="{BG}" stroke-width="4">{rule}</text>')

for name, (x, y, cols) in T.items():
    h = box_h(name)
    a(f'<rect x="{x}" y="{y}" width="{TW}" height="{h}" rx="6" fill="#202428" stroke="#4a7ab8" stroke-width="2"/>')
    a(f'<path d="M{x},{y+HH} V{y+6} Q{x},{y} {x+6},{y} H{x+TW-6} Q{x+TW},{y} {x+TW},{y+6} V{y+HH}z" fill="#1f2d4a"/>')
    a(f'<text x="{x+14}" y="{y+25}" fill="#fff" font-size="17" font-weight="700">{name}</text>')
    for i, (c, t, f) in enumerate(cols):
        ry = y + HH + RH * i
        if i % 2 == 1:
            a(f'<rect x="{x+1}" y="{ry}" width="{TW-2}" height="{RH}" fill="#ffffff08"/>')
        fl = f.split()
        key = 'PK' in fl
        col_fill = '#f2c14e' if key else ('#c8b6ff' if 'FK' in fl else '#e8e8e8')
        tag = ' '.join(x_ for x_ in fl if x_ in ('PK', 'FK', 'UK'))
        a(f'<text x="{x+14}" y="{ry+17}" fill="{col_fill}" font-size="13" font-weight="{700 if key else 400}"{" text-decoration=\"underline\"" if key else ""}>{html.escape(c)}</text>')
        a(f'<text x="{x+190}" y="{ry+17}" fill="#9aa0a6" font-size="12">{html.escape(t)}</text>')
        rest = [x_ for x_ in f.split(' ') if x_ not in ('PK', 'FK', 'UK')]
        note = ('NULL' if 'NULL' in f.split() else '') or ' '.join(rest)
        if tag:
            a(f'<text x="{x+TW-12}" y="{ry+17}" fill="{col_fill}" font-size="12" font-weight="700" text-anchor="end">{tag}</text>')
        if note and not tag:
            a(f'<text x="{x+TW-12}" y="{ry+17}" fill="#7d838a" font-size="11" text-anchor="end">{html.escape(note)}</text>')
        elif note and tag:
            a(f'<text x="{x+TW-52}" y="{ry+17}" fill="#7d838a" font-size="11" text-anchor="end">{html.escape(note)}</text>')
    if name in EXTRA:
        a(f'<text x="{x+14}" y="{y+h-8}" fill="#8fd18f" font-size="12">{html.escape(EXTRA[name])}</text>')

# legend
lx, ly = 1980, 1000
a(f'<rect x="{lx}" y="{ly}" width="440" height="330" rx="10" fill="#202020" stroke="#555"/>')
a(f'<text x="{lx+22}" y="{ly+36}" fill="#fff" font-size="19" font-weight="700">Legend</text>')
L = [('#f2c14e', 'PK  primary key (underlined)'), ('#c8b6ff', 'FK  foreign key, arrow points to the parent table'),
     ('#e8e8e8', 'UK  unique key'), ('#7d838a', 'NULL = nullable column'), ('#b8a8e8', 'CASCADE / RESTRICT = ON DELETE rule'),
     ('#8fd18f', 'Composite constraints and indexes (green)')]
for i, (c, t) in enumerate(L):
    a(f'<circle cx="{lx+30}" cy="{ly+72+i*40}" r="7" fill="{c}"/><text x="{lx+50}" y="{ly+77+i*40}" fill="#ddd" font-size="14">{html.escape(t)}</text>')
a('</g></svg>')
OUT.write_text('\n'.join(o))
print('wrote', OUT)
