import os
import re

btn_html = '''<div class="dashboard-header-left" style="display:flex; align-items:center; gap:1rem;">
        <button class="dashboard-mobile-btn" onclick="document.querySelector('.dashboard-sidebar-dark').classList.toggle('open'); event.stopPropagation();">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <div>'''

dirs = ['siteweb/recruteur']

for d in dirs:
    if not os.path.exists(d): continue
    for f in ['mes-offres.php', 'parametres.php', 'publier-offre.php']:
        path = os.path.join(d, f)
        if not os.path.exists(path): continue
        with open(path, 'r', encoding='utf-8') as file:
            content = file.read()
            
        pattern = re.compile(r'(<div class="dashboard-top-nav"[^>]*>\s*)(<div>\s*<h1.*?</div>)', re.DOTALL)
        
        def replacer(match):
            top_div = match.group(1)
            inner_block = match.group(2)
            # if we wrap, we replace <div> with <div class="dashboard-header-left" ...> <button> ... <div>
            # The regex matched <div> and the closing </div> inside inner_block. 
            # So inner_block contains the full <div> <h1>...</h1> <p>...</p> </div>
            # Let's replace the first <div> of inner_block with the button and wrapper <div>.
            # Wait, inner_block starts with <div>\s*<h1.
            # Let's just prepend the button and change the wrapping.
            # Actually, 	op_div is <div class="dashboard-top-nav">
            # Let's do a simple replace:
            return top_div + '''<div class="dashboard-header-left" style="display:flex; align-items:center; gap:1rem;">
        <button class="dashboard-mobile-btn" onclick="document.querySelector('.dashboard-sidebar-dark').classList.toggle('open'); event.stopPropagation();">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        ''' + inner_block + '\n      </div>'
            
        new_content = pattern.sub(replacer, content, count=1)
        
        if new_content != content:
            with open(path, 'w', encoding='utf-8') as file:
                file.write(new_content)
            print(f"Updated {path}")
        else:
            print(f"Skipped {path}")

