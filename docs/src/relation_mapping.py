"""Generates docs/relational-mapping.svg: relations in the notation of relational-mapping.md (#PK, FK arrows, 1:N labels). Run: python3 docs/src/relation_mapping.py"""
import html, pathlib

OUT = pathlib.Path(__file__).resolve().parent.parent / 'relational-mapping.svg'
BG, GRID = '#1b1b1b', '#262626'
RH, HH, TW = 24, 36, 340
ENT, MN = '#4a7ab8', '#e08a3c'  # border: entity relation / relation from M:N or 1:1 rule

# name: (x, y, kind, [(col, flags)]) flags: PK FK UK NULL
T = {
 'users': (60, 90, ENT, [('id', 'PK'), ('name', ''), ('email', 'UK'), ('email_verified_at', 'NULL'), ('password', ''), ('phone', 'NULL'), ('role', ''), ('remember_token', 'NULL')]),
 'addresses': (60, 400, ENT, [('id', 'PK'), ('user_id', 'FK'), ('recipient', ''), ('line1', ''), ('district', ''), ('province', ''), ('postal_code', ''), ('is_default', ''), ('deleted_at', 'NULL')]),
 'orders': (60, 730, ENT, [('id', 'PK'), ('user_id', 'FK'), ('address_id', 'FK'), ('total', ''), ('status', ''), ('placed_at', '')]),
 'carts': (640, 90, MN, [('id', 'PK'), ('user_id', 'FK UK')]),
 'cart_items': (640, 250, MN, [('id', 'PK'), ('cart_id', 'FK'), ('variant_id', 'FK'), ('quantity', '')]),
 'order_items': (640, 560, MN, [('id', 'PK'), ('order_id', 'FK'), ('variant_id', 'FK'), ('quantity', ''), ('unit_price', '')]),
 'wishlists': (640, 860, MN, [('user_id', 'PK FK'), ('product_id', 'PK FK'), ('created_at', '')]),
 'reviews': (640, 1040, MN, [('id', 'PK'), ('user_id', 'FK'), ('product_id', 'FK'), ('rating', ''), ('comment', 'NULL')]),
 'product_variants': (1220, 90, ENT, [('id', 'PK'), ('product_id', 'FK'), ('sku', 'UK'), ('color', 'NULL'), ('option_name', 'NULL'), ('price', ''), ('stock_qty', '')]),
 'products': (1220, 480, ENT, [('id', 'PK'), ('category_id', 'FK'), ('brand_id', 'FK'), ('name', ''), ('description', 'NULL'), ('is_active', '')]),
 'product_images': (1220, 800, ENT, [('id', 'PK'), ('product_id', 'FK'), ('path', ''), ('is_primary', '')]),
 'categories': (1800, 480, ENT, [('id', 'PK'), ('name', ''), ('slug', 'UK')]),
 'brands': (1800, 680, ENT, [('id', 'PK'), ('name', 'UK')]),
}
# (child, column, parent, label at child, label at parent)
FK = [
 ('addresses', 'user_id', 'users', 'N', '1'), ('carts', 'user_id', 'users', '1', '1'),
 ('orders', 'user_id', 'users', 'N', '1'), ('orders', 'address_id', 'addresses', 'N', '1'),
 ('cart_items', 'cart_id', 'carts', 'N', '1'), ('cart_items', 'variant_id', 'product_variants', 'N', '1'),
 ('order_items', 'order_id', 'orders', 'N', '1'), ('order_items', 'variant_id', 'product_variants', 'N', '1'),
 ('wishlists', 'user_id', 'users', 'N', '1'), ('wishlists', 'product_id', 'products', 'N', '1'),
 ('reviews', 'user_id', 'users', 'N', '1'), ('reviews', 'product_id', 'products', 'N', '1'),
 ('product_variants', 'product_id', 'products', 'N', '1'), ('product_images', 'product_id', 'products', 'N', '1'),
 ('products', 'category_id', 'categories', 'N', '1'), ('products', 'brand_id', 'brands', 'N', '1'),
]
EXTRA = {'cart_items': 'UK (cart_id, variant_id)', 'reviews': 'UK (user_id, product_id)', 'wishlists': 'PK (user_id, product_id)'}
W, H = 2330, 1330


def box_h(n):
    return HH + RH * len(T[n][3]) + (22 if n in EXTRA else 8)


def row_y(n, col):
    x, y, _, cols = T[n]
    return y + HH + RH * [c[0] for c in cols].index(col) + RH / 2


o = []
a = o.append
a(f'<svg xmlns="http://www.w3.org/2000/svg" width="{W}" height="{H}" viewBox="0 0 {W} {H}" font-family="Helvetica, Arial, sans-serif">')
a(f'<defs><pattern id="g" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M40 0H0V40" fill="none" stroke="{GRID}"/></pattern>'
  '<marker id="ar" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="9" markerHeight="9" orient="auto-start-reverse"><path d="M0 0L10 5L0 10z" fill="#9a85d0"/></marker></defs>')
a(f'<rect width="{W}" height="{H}" fill="{BG}"/><rect width="{W}" height="{H}" fill="url(#g)"/>')
a('<text x="40" y="56" fill="#fff" font-size="30" font-weight="700">Smash Badminton Shop: relational mapping</text>')
a('<text x="40" y="80" fill="#9aa0a6" font-size="15">Relations derived from the ER diagram. Arrow = foreign key, from child (N) to parent (1).</text>')

loop = {}
for src, col, dst, ls, ld in FK:
    sx, sy = T[src][0], T[src][1]; dx = T[dst][0]
    y1, y2 = row_y(src, col), row_y(dst, 'id')
    if sx == dx:
        n = loop.get(sx, 0); loop[sx] = n + 1
        lx = sx - 30 - n * 16
        d = f'M{sx},{y1} C{lx},{y1} {lx},{y2} {sx},{y2}'
        x1 = x2 = sx; s1 = s2 = -1
    elif sx < dx:
        x1, x2 = sx + TW, dx; mx = (x1 + x2) / 2; s1, s2 = 1, -1
        d = f'M{x1},{y1} C{mx},{y1} {mx},{y2} {x2},{y2}'
    else:
        x1, x2 = sx, dx + TW; mx = (x1 + x2) / 2; s1, s2 = -1, 1
        d = f'M{x1},{y1} C{mx},{y1} {mx},{y2} {x2},{y2}'
    a(f'<path d="{d}" fill="none" stroke="#9a85d0" stroke-width="1.8" marker-end="url(#ar)" opacity="0.9"/>')
    for xx, yy, sd, lab in ((x1, y1, s1, ls), (x2, y2, s2, ld)):
        a(f'<text x="{xx + sd * 14}" y="{yy - 6}" fill="#f2c14e" font-size="14" font-weight="700" text-anchor="{"start" if sd > 0 else "end"}" paint-order="stroke" stroke="{BG}" stroke-width="4">{lab}</text>')

for name, (x, y, border, cols) in T.items():
    h = box_h(name)
    a(f'<rect x="{x}" y="{y}" width="{TW}" height="{h}" rx="6" fill="#202428" stroke="{border}" stroke-width="2"/>')
    a(f'<path d="M{x},{y+HH} V{y+6} Q{x},{y} {x+6},{y} H{x+TW-6} Q{x+TW},{y} {x+TW},{y+6} V{y+HH}z" fill="{"#1f2d4a" if border == ENT else "#4a2f14"}"/>')
    a(f'<text x="{x+14}" y="{y+24}" fill="#fff" font-size="17" font-weight="700">{name}</text>')
    for i, (c, f) in enumerate(cols):
        ry = y + HH + RH * i
        if i % 2: a(f'<rect x="{x+1}" y="{ry}" width="{TW-2}" height="{RH}" fill="#ffffff08"/>')
        fl = f.split()
        pk = 'PK' in fl
        fill = '#f2c14e' if pk else ('#c8b6ff' if 'FK' in fl else '#e8e8e8')
        a(f'<text x="{x+14}" y="{ry+17}" fill="{fill}" font-size="14" font-weight="{700 if pk else 400}"{" text-decoration=\"underline\"" if pk else ""}>{"#" if pk else ""}{c}{"?" if "NULL" in fl else ""}</text>')
        tag = ' '.join(t for t in fl if t in ('FK', 'UK'))
        if tag: a(f'<text x="{x+TW-12}" y="{ry+17}" fill="{fill}" font-size="12" font-weight="700" text-anchor="end">{tag}</text>')
    if name in EXTRA:
        a(f'<text x="{x+14}" y="{y+h-8}" fill="#8fd18f" font-size="12">{html.escape(EXTRA[name])}</text>')

lx, ly = 1780, 920
a(f'<rect x="{lx}" y="{ly}" width="500" height="370" rx="10" fill="#202020" stroke="#555"/>')
a(f'<text x="{lx+22}" y="{ly+36}" fill="#fff" font-size="19" font-weight="700">Legend</text>')
L = [(ENT, 'Blue border: strong entity, own relation'), (MN, 'Orange border: from M:N / 1:1 rule (junction relation)'),
     ('#f2c14e', '#col  primary key (underlined)'), ('#c8b6ff', 'FK  foreign key'), ('#e8e8e8', 'UK  unique'),
     ('#7d838a', 'col?  nullable column'), ('#8fd18f', 'Composite key / unique constraint')]
for i, (c, t) in enumerate(L):
    a(f'<circle cx="{lx+30}" cy="{ly+72+i*38}" r="7" fill="{c}"/><text x="{lx+50}" y="{ly+77+i*38}" fill="#ddd" font-size="14">{html.escape(t)}</text>')
a(f'<text x="{lx+22}" y="{ly+350}" fill="#9aa0a6" font-size="12">1 / N = cardinality at each end. created_at, updated_at omitted.</text>')
a('</svg>')
OUT.write_text('\n'.join(o))
print('wrote', OUT)
