from pathlib import Path
from pypdf import PdfReader
import zipfile

pdf=PdfReader(r'C:\Users\ADMIN\AppData\Roaming\Output Messenger\CDAAA\Received Files\202609\FINAL X_TRAL_CATALOGUE(1).pdf')
out=Path(__file__).parent/'series-images'
out.mkdir(exist_ok=True)
for page,image_name,filename in [(23,'Im125.jp2','90-cora-black.jpg'),(45,'Im49.jp2','91-flora-rosegold.jpg'),(46,'Im152.jp2','92-flora-gold.jpg')]:
    item=next(i for i in pdf.pages[page-1].images if i.name==image_name)
    item.image.convert('RGB').save(out/filename,quality=95,subsampling=0)
    print(filename,item.image.size)
with zipfile.ZipFile(out.parent/'X-Tral-New-Series-Images.zip','w',zipfile.ZIP_DEFLATED) as z:
    for image in out.glob('*.jpg'): z.write(image,image.name)
