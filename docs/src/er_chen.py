"""Generates docs/er-diagram.svg (Chen notation, dark theme). Run: python3 docs/src/er_chen.py"""
import math, html, pathlib

OUT = pathlib.Path(__file__).resolve().parent.parent / 'er-diagram.svg'
W, H = 3000, 2250
BG, GRID = '#1b1b1b', '#262626'
LINE = '#cfcfcf'

nodes = {}   # id -> dict(kind,x,y,w,h,label,...)
edges = []   # drawn first
shapes = []  # drawn after edges
labels = []  # cardinality labels


def tw(text, per=7.4):
    return len(text) * per


def entity(id_, label, x, y, weak=False):
    w, h = max(190, tw(label, 10) + 50), 66
    nodes[id_] = dict(kind='ent', x=x, y=y, w=w, h=h, label=label, weak=weak)


def rel(id_, label, x, y, ident=False):
    w, h = max(190, tw(label, 10) + 70), 104
    nodes[id_] = dict(kind='rel', x=x, y=y, w=w, h=h, label=label, ident=ident)


def attr(id_, label, x, y, kind='plain'):
    rx = max(58, tw(label) / 2 + 22)
    nodes[id_] = dict(kind='attr', x=x, y=y, w=rx * 2, h=50, label=label, akind=kind)


def polar(cx, cy, r, deg):
    a = math.radians(deg)
    return cx + r * math.cos(a), cy + r * math.sin(a)


def fan(owner, items, r, angles):
    """items: [(id,label,kind)]; places each attribute around owner and links it."""
    o = nodes[owner]
    for (i, lab, kind), ang in zip(items, angles):
        x, y = polar(o['x'], o['y'], r, ang)
        attr(i, lab, x, y, kind)
        edges.append((owner, i, False))


def link(a, b, double=False, ca=None, cb=None):
    edges.append((a, b, double))
    if ca: labels.append((a, b, ca))
    if cb: labels.append((b, a, cb))


def boundary(n, tx, ty):
    """distance from node centre toward (tx,ty) to its outline (rect/diamond/ellipse)."""
    dx, dy = tx - n['x'], ty - n['y']
    d = math.hypot(dx, dy) or 1
    ux, uy = abs(dx) / d, abs(dy) / d
    hw, hh = n['w'] / 2, n['h'] / 2
    if n['kind'] == 'ent':
        return min(hw / ux if ux else 1e9, hh / uy if uy else 1e9)
    if n['kind'] == 'rel':
        return 1 / (ux / hw + uy / hh)
    return 1 / math.sqrt((ux / hw) ** 2 + (uy / hh) ** 2)


# ---------------------------------------------------------------- layout
entity('USER', 'USER', 420, 1050)
entity('ADDRESS', 'ADDRESS', 700, 1900)
entity('CART', 'CART', 1500, 260)
entity('ORDER', 'ORDER', 1500, 1900)
entity('PRODUCT', 'PRODUCT', 1500, 1050)
entity('VARIANT', 'PRODUCT_VARIANT', 2620, 1050)
entity('CATEGORY', 'CATEGORY', 1130, 740)
entity('BRAND', 'BRAND', 1870, 740)
entity('IMAGE', 'PRODUCT_IMAGE', 1500, 1500, weak=True)

rel('HAS', 'HAS', 560, 1475)
rel('OWNS', 'OWNS', 700, 850)
rel('PLACES', 'PLACES', 960, 1475)
rel('SHIPS', 'SHIPS_TO', 1100, 1900)
rel('SAVES', 'SAVES', 960, 930)
rel('REVIEWS', 'REVIEWS', 960, 1170)
rel('INCART', 'IN_CART', 2284, 813)
rel('ORDLINE', 'ORDER_LINE', 2060, 1475)
rel('OFFERED', 'OFFERED_AS', 2060, 1050)
rel('CLASSIFIES', 'CLASSIFIES', 1315, 895)
rel('MAKES', 'MAKES', 1685, 895)
rel('SHOWN', 'SHOWN_BY', 1500, 1275, ident=True)

# links with cardinalities (a, b, double=total participation)
link('USER', 'HAS', ca='1'); link('HAS', 'ADDRESS', True, cb='N')
link('USER', 'OWNS', ca='1'); link('OWNS', 'CART', True, cb='1')
link('USER', 'PLACES', ca='1'); link('PLACES', 'ORDER', True, cb='N')
link('ADDRESS', 'SHIPS', ca='1'); link('SHIPS', 'ORDER', True, cb='N')
link('USER', 'SAVES', ca='M'); link('SAVES', 'PRODUCT', cb='N')
link('USER', 'REVIEWS', ca='M'); link('REVIEWS', 'PRODUCT', cb='N')
link('CART', 'INCART', ca='M'); link('INCART', 'VARIANT', cb='N')
link('ORDER', 'ORDLINE', True, ca='M'); link('ORDLINE', 'VARIANT', cb='N')
link('PRODUCT', 'OFFERED', ca='1'); link('OFFERED', 'VARIANT', True, cb='N')
link('CATEGORY', 'CLASSIFIES', ca='1'); link('CLASSIFIES', 'PRODUCT', True, cb='N')
link('BRAND', 'MAKES', ca='1'); link('MAKES', 'PRODUCT', True, cb='N')
link('PRODUCT', 'SHOWN', ca='1'); link('SHOWN', 'IMAGE', True, cb='N')

# attributes
fan('USER', [('u_id', 'user_id', 'key'), ('u_name', 'name', 'plain'), ('u_mail', 'email', 'plain'),
             ('u_pw', 'password', 'plain'), ('u_ph', 'phone', 'plain'), ('u_role', 'role', 'plain')],
    250, [135, 160, 185, 210, 260, 235])
nodes['u_ph']['x'] -= 0
fan('ADDRESS', [('a_id', 'address_id', 'key'), ('a_rec', 'recipient', 'plain'), ('a_def', 'is_default', 'plain'),
                ('a_loc', 'location', 'composite'), ('a_del', 'deleted_at', 'plain')],
    230, [90, 55, 125, 190, 20])
a = nodes['a_loc']
for i, (lab) in enumerate(['line1', 'district', 'province', 'postal_code']):
    x, y = polar(a['x'], a['y'], 190, 140 + i * 35)
    attr('a_' + lab, lab, x, y)
    edges.append(('a_loc', 'a_' + lab, False))
fan('CART', [('c_id', 'cart_id', 'key')], 190, [270])
fan('ORDER', [('o_id', 'order_id', 'key'), ('o_total', 'total', 'plain'), ('o_status', 'status', 'plain'),
              ('o_at', 'placed_at', 'plain')], 240, [75, 105, 135, 45])
fan('CATEGORY', [('cat_id', 'category_id', 'key'), ('cat_name', 'name', 'plain'), ('cat_slug', 'slug', 'plain')],
    230, [300, 335, 10])
fan('BRAND', [('b_id', 'brand_id', 'key'), ('b_name', 'name', 'plain')], 230, [240, 205])
fan('PRODUCT', [('p_id', 'product_id', 'key'), ('p_name', 'name', 'plain'), ('p_desc', 'description', 'plain'),
                ('p_act', 'is_active', 'plain')], 285, [270, 15, 40, 65])
fan('VARIANT', [('v_id', 'variant_id', 'key'), ('v_sku', 'sku', 'plain'), ('v_color', 'color', 'plain'),
                ('v_opt', 'option_name', 'plain'), ('v_price', 'price', 'plain'), ('v_stock', 'stock_qty', 'plain')],
    270, [-70, -35, 0, 35, 70, 105])
fan('IMAGE', [('i_path', 'path', 'partial'), ('i_prim', 'is_primary', 'plain')], 230, [110, 160])
fan('REVIEWS', [('r_rate', 'rating', 'plain'), ('r_com', 'comment', 'plain')], 150, [90, 140])
fan('INCART', [('ic_q', 'quantity', 'plain')], 150, [300])
fan('ORDLINE', [('ol_q', 'quantity', 'plain'), ('ol_p', 'unit_price', 'plain')], 160, [70, 110])

# ---------------------------------------------------------------- svg
out = []
add = out.append
add(f'<svg xmlns="http://www.w3.org/2000/svg" width="{W}" height="{H}" viewBox="0 0 {W} {H}" font-family="Helvetica, Arial, sans-serif">')
add(f'<defs><pattern id="g" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M40 0H0V40" fill="none" stroke="{GRID}" stroke-width="1"/></pattern></defs>')
add(f'<rect width="{W}" height="{H}" fill="{BG}"/><rect width="{W}" height="{H}" fill="url(#g)"/>')
add(f'<text x="40" y="60" fill="#fff" font-size="30" font-weight="700">Smash Badminton Shop: ER diagram (Chen notation)</text>')

for a_, b_, dbl in edges:
    A, B = nodes[a_], nodes[b_]
    if dbl:
        add(f'<line x1="{A["x"]}" y1="{A["y"]}" x2="{B["x"]}" y2="{B["y"]}" stroke="{LINE}" stroke-width="7"/>')
        add(f'<line x1="{A["x"]}" y1="{A["y"]}" x2="{B["x"]}" y2="{B["y"]}" stroke="{BG}" stroke-width="3"/>')
    else:
        add(f'<line x1="{A["x"]}" y1="{A["y"]}" x2="{B["x"]}" y2="{B["y"]}" stroke="{LINE}" stroke-width="1.6"/>')

for nid, n in nodes.items():
    x, y, w, h, lab = n['x'], n['y'], n['w'], n['h'], html.escape(n['label'])
    if n['kind'] == 'ent':
        fill, st = ('#4a2a2a', '#d46a6a') if n['weak'] else ('#1f2d4a', '#4a7ab8')
        add(f'<rect x="{x-w/2}" y="{y-h/2}" width="{w}" height="{h}" fill="{fill}" stroke="{st}" stroke-width="2.5"/>')
        if n['weak']:
            add(f'<rect x="{x-w/2+7}" y="{y-h/2+7}" width="{w-14}" height="{h-14}" fill="none" stroke="{st}" stroke-width="2"/>')
        add(f'<text x="{x}" y="{y+6}" fill="#fff" font-size="17" font-weight="700" text-anchor="middle">{lab}</text>')
    elif n['kind'] == 'rel':
        pts = f'{x},{y-h/2} {x+w/2},{y} {x},{y+h/2} {x-w/2},{y}'
        add(f'<polygon points="{pts}" fill="#3b2f55" stroke="#9a85d0" stroke-width="2"/>')
        if n['ident']:
            k = 9
            pts2 = f'{x},{y-h/2+k} {x+w/2-k*1.8},{y} {x},{y+h/2-k} {x-w/2+k*1.8},{y}'
            add(f'<polygon points="{pts2}" fill="none" stroke="#9a85d0" stroke-width="2"/>')
        add(f'<text x="{x}" y="{y+5}" fill="#fff" font-size="14" font-weight="700" text-anchor="middle">{lab}</text>')
    else:
        k = n['akind']
        fill, st = {'key': ('#3a2a00', '#c79a1a'), 'partial': ('#4a2a2a', '#d46a6a')}.get(k, ('#202020', '#bdbdbd'))
        add(f'<ellipse cx="{x}" cy="{y}" rx="{w/2}" ry="{h/2}" fill="{fill}" stroke="{st}" stroke-width="{2.2 if k=="composite" else 1.6}"/>')
        deco = ' text-decoration="underline"' if k == 'key' else (' text-decoration="underline" ' if k == 'partial' else '')
        weight = '700' if k in ('key', 'composite') else '400'
        if k == 'partial':
            add(f'<text x="{x}" y="{y+5}" fill="#fff" font-size="14" font-weight="700" text-anchor="middle" text-decoration="underline" style="text-decoration-style:dashed">{lab}</text>')
        else:
            add(f'<text x="{x}" y="{y+5}" fill="#fff" font-size="14" font-weight="{weight}" text-anchor="middle"{deco}>{lab}</text>')

for owner, other, text in labels:
    O, T = nodes[owner], nodes[other]
    dx, dy = T['x'] - O['x'], T['y'] - O['y']
    d = math.hypot(dx, dy)
    ux, uy = dx / d, dy / d
    dist = boundary(O, T['x'], T['y']) + 26
    px, py = O['x'] + ux * dist - uy * 14, O['y'] + uy * dist + ux * 14
    add(f'<text x="{px}" y="{py+6}" fill="#fff" font-size="19" font-weight="700" text-anchor="middle" paint-order="stroke" stroke="{BG}" stroke-width="5">{text}</text>')

# legend
lx, ly = 2300, 1700
add(f'<rect x="{lx}" y="{ly}" width="640" height="480" rx="10" fill="#202020" stroke="#555"/>')
add(f'<text x="{lx+24}" y="{ly+40}" fill="#fff" font-size="20" font-weight="700">Legend</text>')
items = [
    ('ent', 'Entity'), ('weak', 'Weak entity (identified by its owner)'), ('rel', 'Relationship (1, N, M = cardinality)'),
    ('dbl', 'Double line = total participation'), ('key', 'Key attribute'), ('partial', 'Partial key (weak entity)'),
    ('comp', 'Composite attribute (has sub-attributes)'), ('attr', 'Simple attribute')]
for i, (k, text) in enumerate(items):
    yy = ly + 80 + i * 48
    cx = lx + 70
    if k == 'ent':
        add(f'<rect x="{cx-34}" y="{yy-14}" width="68" height="28" fill="#1f2d4a" stroke="#4a7ab8" stroke-width="2"/>')
    elif k == 'weak':
        add(f'<rect x="{cx-34}" y="{yy-14}" width="68" height="28" fill="#4a2a2a" stroke="#d46a6a" stroke-width="2"/><rect x="{cx-29}" y="{yy-9}" width="58" height="18" fill="none" stroke="#d46a6a" stroke-width="1.5"/>')
    elif k == 'rel':
        add(f'<polygon points="{cx},{yy-18} {cx+34},{yy} {cx},{yy+18} {cx-34},{yy}" fill="#3b2f55" stroke="#9a85d0" stroke-width="2"/>')
    elif k == 'dbl':
        add(f'<line x1="{cx-34}" y1="{yy}" x2="{cx+34}" y2="{yy}" stroke="{LINE}" stroke-width="7"/><line x1="{cx-34}" y1="{yy}" x2="{cx+34}" y2="{yy}" stroke="#202020" stroke-width="3"/>')
    else:
        fill, st = {'key': ('#3a2a00', '#c79a1a'), 'partial': ('#4a2a2a', '#d46a6a')}.get(k, ('#202020', '#bdbdbd'))
        add(f'<ellipse cx="{cx}" cy="{yy}" rx="34" ry="16" fill="{fill}" stroke="{st}" stroke-width="{2.2 if k=="comp" else 1.6}"/>')
        if k == 'key': add(f'<line x1="{cx-14}" y1="{yy+6}" x2="{cx+14}" y2="{yy+6}" stroke="#fff"/>')
        if k == 'partial': add(f'<line x1="{cx-14}" y1="{yy+6}" x2="{cx+14}" y2="{yy+6}" stroke="#fff" stroke-dasharray="3 2"/>')
    add(f'<text x="{lx+130}" y="{yy+5}" fill="#ddd" font-size="15">{html.escape(text)}</text>')

add('</svg>')
OUT.write_text('\n'.join(out))
print('wrote', OUT)
