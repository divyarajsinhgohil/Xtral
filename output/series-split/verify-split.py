import json, urllib.request, hashlib, concurrent.futures, io
from PIL import Image, ImageChops, ImageStat
from pathlib import Path
root=Path(__file__).parent
with urllib.request.urlopen('https://x-tral.com/xadmin/api/webapi/products.php',timeout=60) as r:
    raw=r.read()
(root/'verified-live-products.json').write_bytes(raw)
products=json.loads(raw)['data']
expected=json.loads((root/'manifest.json').read_text())
checks=[]
for e in expected:
    matches=[p for p in products if p['series_id']==e['series_id'] and p['code']==e['code']]
    assert len(matches)==1,(e['code'],len(matches))
    p=matches[0]
    assert p['name']==e['name'] and p['price']==e['price'] and p['variant_type']=='none',e['code']
    checks.append((e,p))
def check_image(pair):
    e,p=pair
    with urllib.request.urlopen(p['image'],timeout=40) as r: content=r.read()
    source=(root/'original-images'/e['image'].rsplit('/',1)[1]).read_bytes()
    actual=Image.open(io.BytesIO(content)).convert('RGB')
    original=Image.open(io.BytesIO(source)).convert('RGB')
    assert actual.size==original.size,e['code']
    # Hosting may recompress JPEGs; compare decoded pixels as well as dimensions.
    error=max(ImageStat.Stat(ImageChops.difference(actual,original)).mean)
    assert error<2,(e['code'],error)
    return p['id']
with concurrent.futures.ThreadPoolExecutor(max_workers=8) as pool: verified=list(pool.map(check_image,checks))
print(f'Verified all {len(verified)} new products: series, code, price, title and matching image content.')
for sid in (62,80,90,91,92):
    ps=[p for p in products if p['series_id']==sid]
    print(sid,len(ps),'products;',sum(bool(p['variants']) for p in ps),'with remaining colour options')
    assert all(p['variant_type']=='none' and not p['variants'] for p in ps)
    if sid==62: assert all('black' not in p['specifications'].lower() for p in ps)
    if sid==80: assert all('gold' not in p['specifications'].lower() for p in ps)
