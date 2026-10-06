import json, urllib.request, concurrent.futures
from pathlib import Path
p=Path(__file__).parent
items=json.loads((p/'original-products-backup.json').read_text())
folder=p/'original-images';folder.mkdir(exist_ok=True)
urls=sorted({u for x in items for u in (x['images']+[v['image'] for v in x['variants']]) if u})
def save(url):
    dest=folder/url.rsplit('/',1)[1]
    if not dest.exists():
        with urllib.request.urlopen(url,timeout=45) as response: dest.write_bytes(response.read())
    return dest.stat().st_size
with concurrent.futures.ThreadPoolExecutor(max_workers=8) as pool:
    sizes=list(pool.map(save,urls))
assert min(sizes)>100
print(f'Backed up {len(sizes)} original product images.')
