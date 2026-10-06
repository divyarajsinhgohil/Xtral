import csv, json, html
from pathlib import Path

audit=Path(r'C:\Users\ADMIN\.codex\visualizations\2026\09\29\01a0ed1d-8b85-7b70-a6aa-4347e2b475c5\audit')
products=json.loads((audit/'series-split-live-products.json').read_text(encoding='utf-8-sig'))['data']
out=Path(__file__).parent/'series-split'
out.mkdir(exist_ok=True)
headers=['Category','SubCategory','Series','ProductName','ProductCode','ProductType','Price','PriceZone2','DisplayOrder','Dimensions','Specifications','Image1']
rows=[]
manifest=[]
for source,target,series,colour in [(62,90,'CORA BLACK','Black'),(80,91,'FLORA ROSEGOLD','Rose Gold'),(80,92,'FLORA GOLD','Gold')]:
    for order,p in enumerate(sorted([p for p in products if p['series_id']==source],key=lambda p:p['display_order']),1):
        v=next(v for v in p['variants'] if v['name']==colour)
        name=p['name'].replace(' - EXPOSED','') if p['id']==319 else p['name']
        dims=p['dimensions'] or ''
        if p['id'] in (321,322): dims='200 mm' if p['id']==321 else '300 mm'
        if p['id'] in (323,324): dims='18 inches' if p['id']==323 else '24 inches'
        if p['id']==325: dims='6 x 6 inches'
        if p['id'] in (592,595): dims='24 inches'
        spec=f'<p>{html.escape(name)} from the {html.escape(series)} series, in {colour} finish.</p><ul><li>Product code: {v["code"]}</li><li>Finish: {colour}</li>'
        if dims: spec+=f'<li>Size: {dims}</li>'
        if source==80 or p['id'] in (321,322,325): spec+='<li>Material: Stainless Steel 304</li>'
        spec+='</ul>'
        assert v['image'] and v['price']>0
        rows.append([p['category'],'',series,name,v['code'],'simple',int(v['price']),v['price_zone2'] or '',order,dims,spec,v['image']])
        manifest.append({'source_id':p['id'],'series_id':target,'series':series,'colour':colour,'name':name,'code':v['code'],'price':v['price'],'image':v['image'],'dimensions':dims,'specifications':spec})
assert len(rows)==35
assert len({(x['series_id'],x['code']) for x in manifest})==35
with (out/'new-colour-products.csv').open('w',newline='',encoding='utf-8-sig') as f:
    w=csv.writer(f);w.writerow(headers);w.writerows(rows)
(out/'manifest.json').write_text(json.dumps(manifest,indent=2),encoding='utf-8')
(out/'original-products-backup.json').write_text(json.dumps([p for p in products if p['series_id'] in (62,80)],indent=2),encoding='utf-8')
print('Prepared 35 new products; original product data backed up. CSV:',out/'new-colour-products.csv')
retained=[]
for p in products:
    if p['series_id'] not in (62,80): continue
    colour='Rose Gold' if p['series_id']==62 else 'Chrome'
    v=next(v for v in p['variants'] if v['name']==colour)
    name=p['name'].replace(' - EXPOSED','') if p['id']==319 else p['name']
    series=p['series']
    dims=p['dimensions'] or ''
    if p['id'] in (321,322): dims='200 mm' if p['id']==321 else '300 mm'
    if p['id'] in (323,324): dims='18 inches' if p['id']==323 else '24 inches'
    if p['id']==325: dims='6 x 6 inches'
    if p['id'] in (592,595): dims='24 inches'
    spec=f'<p>{html.escape(name)} from the {html.escape(series)} series, in {colour} finish.</p><ul><li>Product code: {v["code"]}</li><li>Finish: {colour}</li>'
    if dims: spec+=f'<li>Size: {dims}</li>'
    if p['series_id']==80 or p['id'] in (321,322,323,324,325): spec+='<li>Material: Stainless Steel 304</li>'
    spec+='</ul>'
    retained.append([p['category'],'',series,name,v['code'],'simple',int(v['price']),v['price_zone2'] or '',p['display_order'],dims,spec,v['image'],p['id']])
with (out/'retain-original-colours.csv').open('w',newline='',encoding='utf-8-sig') as f:
    w=csv.writer(f);w.writerow(headers+['ProductID']);w.writerows(retained)
print('Prepared updates for 25 retained original products.')
