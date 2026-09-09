import os
import glob
import re

def inject_favicons():
    root_files = sorted(glob.glob('*.html'))
    public_files = sorted(glob.glob('public/*.html'))
    paginas_level1 = sorted(glob.glob('public/paginas/*.html'))
    paginas_level2 = sorted(glob.glob('public/paginas/*/*.html'))

    # Tags for root / public/*.html
    tags_root = '''  <link rel="icon" type="image/svg+xml" href="favicon.svg"/>
  <link rel="alternate icon" type="image/x-icon" href="favicon.ico"/>
  <link rel="apple-touch-icon" href="apple-touch-icon.png"/>'''

    # Tags for public/paginas/*.html (1 level down)
    tags_paginas1 = '''  <link rel="icon" type="image/svg+xml" href="../favicon.svg"/>
  <link rel="alternate icon" type="image/x-icon" href="../favicon.ico"/>
  <link rel="apple-touch-icon" href="../apple-touch-icon.png"/>'''

    # Tags for public/paginas/*/*.html (2 levels down)
    tags_paginas2 = '''  <link rel="icon" type="image/svg+xml" href="../../favicon.svg"/>
  <link rel="alternate icon" type="image/x-icon" href="../../favicon.ico"/>
  <link rel="apple-touch-icon" href="../../apple-touch-icon.png"/>'''

    def process_file(filepath, tags):
        with open(filepath, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
        
        # If already has favicon, don't re-inject
        if 'rel="icon"' in content or 'rel="shortcut icon"' in content:
            return False

        # Inject right after <meta name="viewport" ...> or after <head>
        if '<meta name="viewport"' in content:
            new_content = re.sub(
                r'(<meta name="viewport"[^>]*>)',
                rf'\1\n{tags}',
                content,
                count=1
            )
        elif '<head>' in content:
            new_content = content.replace('<head>', f'<head>\n{tags}', 1)
        else:
            return False

        if new_content != content:
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(new_content)
            return True
        return False

    c = 0
    for f in root_files + public_files:
        if process_file(f, tags_root):
            c += 1
    for f in paginas_level1:
        if process_file(f, tags_paginas1):
            c += 1
    for f in paginas_level2:
        if process_file(f, tags_paginas2):
            c += 1

    print(f"Injected favicon tags into {c} HTML files.")

if __name__ == '__main__':
    inject_favicons()
