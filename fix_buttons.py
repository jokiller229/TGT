import os
import re

btn_html = '''<div style="display:flex; align-items:center; gap:1rem;">
        <button class="dashboard-mobile-btn" onclick="document.querySelector('.dashboard-sidebar-dark').classList.toggle('open'); event.stopPropagation();">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <div>'''

dirs = ['siteweb/candidat', 'siteweb/recruteur']

for d in dirs:
    if not os.path.exists(d): continue
    for f in os.listdir(d):
        if not f.endswith('.php'): continue
        if f in ['candidat-dashboard.php', 'recruteur-dashboard.php']: continue
        
        path = os.path.join(d, f)
        with open(path, 'r', encoding='utf-8') as file:
            content = file.read()
            
        if 'dashboard-mobile-btn' in content:
            continue
            
        # The structure is usually:
        # <div class="dashboard-topbar"[^>]*>
        #   <div>
        #     <h1...
        #     <p... (optional)
        #   </div>
        #
        # We want to wrap the <div> holding the <h1> inside our new flex container.
        # We can match: <div class="dashboard-topbar">\s*<div>\s*<h1.*?</div>
        
        pattern = re.compile(r'(<div class="(?:dashboard-topbar|dashboard-header)"[^>]*>\s*)(<div>\s*<h1.*?</div>)', re.DOTALL)
        
        def replacer(match):
            top_div = match.group(1)
            inner_block = match.group(2)
            # wrap inner_block in our flex container and add button
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

