import os
import glob

# Files to update
files = glob.glob('c:/MAMP/htdocs/TGT/siteweb/*.php')

target = '''        <div class="company-selector-dropdown">
          <span style="color: #2563EB;">●</span>
          <span><?= htmlspecialchars($companyName) ?></span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
        </div>'''

replacement = '''        <div class="company-selector-dropdown" style="position: relative; cursor: pointer;" onclick="this.querySelector('.dropdown-menu').classList.toggle('show')">
          <span style="color: #2563EB;">●</span>
          <span><?= htmlspecialchars($companyName ?? 'Mon Profil') ?></span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
          
          <div class="dropdown-menu" style="position: absolute; top: 100%; right: 0; margin-top: 0.5rem; background: white; border: 1px solid #E2E8F0; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); min-width: 200px; display: none; z-index: 100; flex-direction: column;">
            <a href="index.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.25rem; color: #1e293b; text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: background 0.2s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
              Accueil du site
            </a>
            <a href="parametres.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.25rem; color: #1e293b; text-decoration: none; font-size: 0.9rem; font-weight: 500; border-top: 1px solid #F1F5F9; transition: background 0.2s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
              Mon profil
            </a>
          </div>
          <style>
            .company-selector-dropdown .dropdown-menu.show { display: flex !important; }
          </style>
          <script>
            document.addEventListener('click', function(e) {
              if (!e.target.closest('.company-selector-dropdown')) {
                document.querySelectorAll('.company-selector-dropdown .dropdown-menu').forEach(function(menu) {
                  menu.classList.remove('show');
                });
              }
            });
          </script>
        </div>'''

for file in files:
    if file.endswith('recruteur-dashboard.php'):
        continue
    with open(file, 'r', encoding='utf-8') as f:
        content = f.read()
    if target in content:
        content = content.replace(target, replacement)
        with open(file, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated {file}")
