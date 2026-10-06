import os
from pathlib import Path
import pymupdf
from PIL import Image, ImageFilter

def create_series_images():
    output_dir = Path(r"c:\xampp\htdocs\Xtral\output\series-images-1000x1000")
    output_dir.mkdir(parents=True, exist_ok=True)
    
    pdf_path = r"c:\xampp\htdocs\Xtral\FINAL X_TRAL_CATALOGUE(1).pdf"
    logo_path = r"c:\xampp\htdocs\Xtral\xadmin\uploads\catalogue\products\logo_full.png"
    
    doc = pymupdf.open(pdf_path)
    
    # 4 Series configurations:
    # 1. ONE PC FULL PED WASH BASIN: PDF Page 17 (0-indexed 16), xref 1154
    # 2. HALF PEDESTAL BASINS: PDF Page 18 (0-indexed 17), xref 1222
    # 3. FULL PEDESTAL BASINS: PDF Page 18 (0-indexed 17), xref 1256
    # 4. URINALS: PDF Page 21 (0-indexed 20), xref 1423
    series_configs = [
        {
            "name": "one_pc_full_ped_wash_basin",
            "title": "ONE PC FULL PED WASH BASIN",
            "page_num": 17,
            "xref": 1154,
            "y_crop_offset": 0.12, # slightly below top to frame the basin & floor nicely
        },
        {
            "name": "half_pedestal_basins",
            "title": "HALF PEDESTAL BASINS",
            "page_num": 18,
            "xref": 1222,
            "y_crop_offset": 0.15, # center on the wall-hung basin
        },
        {
            "name": "full_pedestal_basins",
            "title": "FULL PEDESTAL BASINS",
            "page_num": 18,
            "xref": 1256,
            "y_crop_offset": 0.10, # beautiful pedestal view
        },
        {
            "name": "urinals",
            "title": "URINALS",
            "page_num": 21,
            "xref": 1423,
            "y_crop_offset": 0.08, # frame the urinal fixture cleanly
        }
    ]
    
    # Load and prepare logos
    logo_img = Image.open(logo_path).convert("RGBA")
    
    # Generate white logo version for dark backgrounds
    r, g, b, a = logo_img.split()
    white_logo = Image.merge("RGBA", (Image.new("L", logo_img.size, 255),
                                      Image.new("L", logo_img.size, 255),
                                      Image.new("L", logo_img.size, 255),
                                      a))
    
    results = []
    
    for cfg in series_configs:
        print(f"Processing {cfg['title']}...")
        pix = pymupdf.Pixmap(doc, cfg["xref"])
        
        # Convert CMYK to RGB if needed
        if pix.colorspace and pix.colorspace.n >= 4:
            pix_rgb = pymupdf.Pixmap(pymupdf.csRGB, pix)
            im = Image.frombytes("RGB", [pix_rgb.width, pix_rgb.height], pix_rgb.samples)
        else:
            im = Image.frombytes("RGB", [pix.width, pix.height], pix.samples)
            
        w, h = im.size
        sq_size = min(w, h)
        
        # Compute crop
        max_y = h - sq_size
        y0 = int(max_y * cfg["y_crop_offset"])
        y0 = max(0, min(y0, max_y))
        
        crop_box = (0, y0, sq_size, y0 + sq_size)
        cropped = im.crop(crop_box)
        
        # Resize to 1000x1000 high quality
        resized = cropped.resize((1000, 1000), Image.Resampling.LANCZOS)
        
        # Determine whether top-right background is light or dark
        top_right_region = resized.crop((680, 40, 960, 150)).convert("L")
        region_brightness = sum(top_right_region.getdata()) / (top_right_region.width * top_right_region.height)
        
        # Target logo width ~250px on 1000px canvas (looks clean and balanced)
        target_logo_w = 250
        logo_aspect = logo_img.height / logo_img.width
        target_logo_h = int(target_logo_w * logo_aspect)
        
        # Choose logo variant
        if region_brightness < 140:
            selected_logo = white_logo.resize((target_logo_w, target_logo_h), Image.Resampling.LANCZOS)
        else:
            selected_logo = logo_img.resize((target_logo_w, target_logo_h), Image.Resampling.LANCZOS)
            
        # Add subtle soft shadow/glow behind logo for contrast & premium look
        logo_canvas = Image.new("RGBA", (1000, 1000), (0, 0, 0, 0))
        logo_pos = (1000 - target_logo_w - 45, 45) # 45px padding from top & right
        logo_canvas.paste(selected_logo, logo_pos, selected_logo)
        
        # Composite onto main image
        final_im = resized.convert("RGBA")
        final_im = Image.alpha_composite(final_im, logo_canvas).convert("RGB")
        
        # Save output
        out_filename = f"{cfg['name']}_1000x1000.jpg"
        out_filepath = output_dir / out_filename
        final_im.save(out_filepath, quality=95, subsampling=0)
        
        # Also save a png version
        out_png = output_dir / f"{cfg['name']}_1000x1000.png"
        final_im.save(out_png)
        
        print(f"Saved: {out_filename} (1000x1000)")
        results.append({
            "name": cfg["title"],
            "file": out_filename,
            "path": str(out_filepath)
        })
        
    print("Done! All 4 images created successfully.")

if __name__ == "__main__":
    create_series_images()
