import os
import shutil
from PIL import Image, ImageDraw, ImageFont

def generate_favicons():
    # 1. SVG Favicon
    svg_content = '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
  <circle cx="32" cy="32" r="30" fill="#ffb442"/>
  <!-- Precise bold geometric K matching Kubica design system -->
  <path d="M22 17h6.5v12.2l8.8-12.2h8.2L34.2 30.5 46.5 47H38L28.5 33.8V47H22V17z" fill="#140b00"/>
</svg>
'''
    with open('favicon.svg', 'w', encoding='utf-8') as f:
        f.write(svg_content)
    with open('public/favicon.svg', 'w', encoding='utf-8') as f:
        f.write(svg_content)

    os.makedirs('assets/img', exist_ok=True)
    os.makedirs('public/assets/img', exist_ok=True)
    with open('assets/img/favicon.svg', 'w', encoding='utf-8') as f:
        f.write(svg_content)
    with open('public/assets/img/favicon.svg', 'w', encoding='utf-8') as f:
        f.write(svg_content)

    # 2. Raster icons (PNG and ICO) using Pillow
    def create_k_image(size):
        img = Image.new('RGBA', (size, size), (0, 0, 0, 0))
        draw = ImageDraw.Draw(img)
        
        # Circle background in Amber Forge (#ffb442)
        pad = max(1, size // 32)
        draw.ellipse([pad, pad, size - pad, size - pad], fill=(255, 180, 66, 255))
        
        # Draw "K" shape scaled to size
        scale = size / 64.0
        points_stem = [
            (22 * scale, 17 * scale),
            (28.5 * scale, 17 * scale),
            (28.5 * scale, 47 * scale),
            (22 * scale, 47 * scale),
        ]
        draw.polygon(points_stem, fill=(20, 11, 0, 255))
        
        points_upper = [
            (28.5 * scale, 29.2 * scale),
            (37.3 * scale, 17 * scale),
            (45.5 * scale, 17 * scale),
            (34.2 * scale, 30.5 * scale),
        ]
        draw.polygon(points_upper, fill=(20, 11, 0, 255))
        
        points_lower = [
            (33.0 * scale, 28.5 * scale),
            (46.5 * scale, 47 * scale),
            (38.0 * scale, 47 * scale),
            (28.5 * scale, 33.8 * scale),
        ]
        draw.polygon(points_lower, fill=(20, 11, 0, 255))
        
        return img

    # Generate multi-size ICO
    img16 = create_k_image(16)
    img32 = create_k_image(32)
    img48 = create_k_image(48)
    img64 = create_k_image(64)
    img180 = create_k_image(180)

    img64.save('favicon.ico', format='ICO', sizes=[(16, 16), (32, 32), (48, 48), (64, 64)])
    img64.save('public/favicon.ico', format='ICO', sizes=[(16, 16), (32, 32), (48, 48), (64, 64)])

    img180.save('apple-touch-icon.png', format='PNG')
    img180.save('public/apple-touch-icon.png', format='PNG')
    img32.save('assets/img/favicon-32x32.png', format='PNG')
    img32.save('public/assets/img/favicon-32x32.png', format='PNG')

    print("Generated favicons (SVG, ICO, PNG) in root, public/, and assets/img/")

if __name__ == '__main__':
    generate_favicons()
