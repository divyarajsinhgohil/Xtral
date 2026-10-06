from pathlib import Path
import json
from pypdf import PdfReader
from PIL import Image, ImageDraw

root = Path(__file__).parent
out = root / 'extracted'
out.mkdir(exist_ok=True)
r = PdfReader('X_TRAL_CATALOGUE.pdf')
records = []
for idx in range(7, 53):
    page = r.pages[idx]
    for key, ref in page.get('/Resources', {}).get('/XObject', {}).items():
        obj = ref.get_object()
        if obj.get('/Width', 0) < 800 or obj.get('/Height', 0) < 850:
            continue
        item = page.images[key]
        im = item.image
        if im.mode == 'RGBA':
            bg = Image.new('RGB', im.size, 'white')
            bg.paste(im, mask=im.getchannel('A'))
            im = bg
        else:
            im = im.convert('RGB')
        name = f'page-{idx+1:02d}-{key[1:]}.jpg'
        im.save(out / name, quality=98, subsampling=0)
        records.append(dict(page=idx+1, key=key, file=name, width=im.width, height=im.height))
        print(name, im.size, flush=True)
    (root / 'extraction-manifest.json').write_text(json.dumps(records, indent=2))
for start in range(0, len(records), 12):
    canvas = Image.new('RGB', (1400, 1080), '#eeeeee')
    draw = ImageDraw.Draw(canvas)
    for j, item in enumerate(records[start:start+12]):
        im = Image.open(out / item['file'])
        im.thumbnail((330, 310))
        x = (j % 4) * 350 + (350-im.width)//2
        y = (j // 4) * 360
        canvas.paste(im, (x,y))
        draw.text(((j%4)*350+8, y+320), item['file'], fill='black')
    canvas.save(root / f'lifestyle-review-{start//12+1}.jpg')
print('TOTAL', len(records), flush=True)
