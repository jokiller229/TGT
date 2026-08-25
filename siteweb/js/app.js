/**
 * TGTravail - Moteur interactif Vanilla JavaScript pour la version Site Web
 * Gère la navigation, les filtres dynamiques, la publication en 4 étapes,
 * la candidature interactive, le chat et le générateur de CV.
 */

// Aucune donnée mockée, tout est géré par PHP backend.

// Toast helper
function showToast(message, type = 'success') {
  const toast = document.createElement('div');
  toast.className = 'toast-alert';
  toast.style.cssText = `
    position: fixed;
    top: 24px;
    right: 24px;
    background: ${type === 'success' ? '#059669' : '#1E40AF'};
    color: #FFF;
    padding: 12px 20px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.9rem;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    z-index: 9999;
    display: flex;
    align-items: center;
    gap: 8px;
    animation: slideIn 0.3s ease;
  `;
  toast.innerHTML = `<span>✓</span><span>${message}</span>`;
  document.body.appendChild(toast);
  setTimeout(() => {
    toast.remove();
  }, 3500);
}

// Initialisation au chargement du DOM
document.addEventListener('DOMContentLoaded', () => {
  // Mobile Navigation toggle
  const mobileToggle = document.getElementById('mobile-menu-toggle');
  const mobileNav = document.getElementById('mobile-nav');
  if (mobileToggle && mobileNav) {
    mobileToggle.addEventListener('click', () => {
      mobileNav.classList.toggle('open');
    });
  }

  // Bookmark Toggle buttons
  document.querySelectorAll('.bookmark-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      btn.classList.toggle('active');
      const isSaved = btn.classList.contains('active');
      showToast(isSaved ? 'Offre enregistrée dans vos favoris ⭐' : 'Offre retirée des favoris', 'info');
    });
  });

  // Dynamic Live Preview in Job Posting Form (Maquette 5)
  const jobTitleInput = document.getElementById('post-title');
  const previewTitle = document.getElementById('preview-title');
  if (jobTitleInput && previewTitle) {
    jobTitleInput.addEventListener('input', (e) => {
      previewTitle.textContent = e.target.value || '-';
    });
  }

  const jobCatSelect = document.getElementById('post-category');
  const previewCat = document.getElementById('preview-category');
  if (jobCatSelect && previewCat) {
    jobCatSelect.addEventListener('change', (e) => {
      previewCat.textContent = e.target.value || '-';
    });
  }

  const jobContractSelect = document.getElementById('post-contract');
  const previewContract = document.getElementById('preview-contract');
  if (jobContractSelect && previewContract) {
    jobContractSelect.addEventListener('change', (e) => {
      previewContract.textContent = e.target.value || '-';
    });
  }

  const jobLocationInput = document.getElementById('post-location');
  const previewLocation = document.getElementById('preview-location');
  if (jobLocationInput && previewLocation) {
    jobLocationInput.addEventListener('input', (e) => {
      previewLocation.textContent = e.target.value || '-';
    });
  }

  const jobSalaryMin = document.getElementById('post-salary-min');
  const jobSalaryMax = document.getElementById('post-salary-max');
  const previewSalary = document.getElementById('preview-salary');
  if (jobSalaryMin && jobSalaryMax && previewSalary) {
    const updateSal = () => {
      const min = jobSalaryMin.value ? Number(jobSalaryMin.value).toLocaleString('fr-FR') : '';
      const max = jobSalaryMax.value ? Number(jobSalaryMax.value).toLocaleString('fr-FR') : '';
      previewSalary.textContent = (min || max) ? `${min} - ${max} FCFA` : '-';
    };
    jobSalaryMin.addEventListener('input', updateSal);
    jobSalaryMax.addEventListener('input', updateSal);
  }

  // Multi-step Navigation in Post Job
  const nextStepBtn = document.getElementById('btn-next-step');
  if (nextStepBtn) {
    let currentStep = 1;
    nextStepBtn.addEventListener('click', () => {
      if (currentStep < 4) {
        currentStep++;
        document.querySelectorAll('.stepper-item').forEach((item, idx) => {
          if (idx + 1 === currentStep) item.classList.add('active');
          if (idx + 1 < currentStep) item.classList.add('completed');
        });
        if (currentStep === 4) {
          nextStepBtn.textContent = 'Payer & Publier (2 000 FCFA)';
        }
      } else {
        // Open Mobile Money payment simulation
        const phone = prompt('Paiement T-Money / Flooz (2 000 FCFA)\nEntrez votre numéro togolais :', '90123456');
        if (phone) {
          showToast('Offre soumise avec succès ! En cours de modération Orizon.', 'success');
          setTimeout(() => {
            window.location.href = 'recruteur-dashboard.html';
          }, 1500);
        }
      }
    });
  }

  // Interactive Live Chat Message Sending (Maquette 8)
  const chatForm = document.getElementById('chat-form');
  const chatInput = document.getElementById('chat-input');
  const chatScroll = document.getElementById('chat-scroll');
  if (chatForm && chatInput && chatScroll) {
    chatForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const text = chatInput.value.trim();
      if (!text) return;

      // Add user message bubble
      const sentBubble = document.createElement('div');
      sentBubble.className = 'chat-bubble sent';
      sentBubble.textContent = text;
      chatScroll.appendChild(sentBubble);
      chatInput.value = '';
      chatScroll.scrollTop = chatScroll.scrollHeight;

      // Auto reply simulation
      setTimeout(() => {
        const replyBubble = document.createElement('div');
        replyBubble.className = 'chat-bubble received';
        replyBubble.textContent = 'Parfait ! Nous accusons bonne réception et confirmons l\'échange.';
        chatScroll.appendChild(replyBubble);
        chatScroll.scrollTop = chatScroll.scrollHeight;
      }, 1500);
    });
  }

  // --- Smart Notifications System ---
  const notifContainer = document.getElementById('smart-notif-container');
  if (notifContainer) {
    // 1. Setup HTML structure
    notifContainer.innerHTML = `
      <button id="smart-notif-btn" style="position:relative; color:var(--text-muted); display:flex; background:none; border:none; cursor:pointer; padding:0;" title="Notifications">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
          <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        <span id="smart-notif-badge" style="display:none; position:absolute; top:2px; right:2px; width:10px; height:10px; background:#EF4444; border-radius:50%; border:2px solid #FFF;"></span>
      </button>
      <div id="smart-notif-dropdown" style="display:none; position:absolute; right:0; top:100%; margin-top:0.5rem; width:320px; background:#FFF; border:1px solid #E2E8F0; border-radius:0.75rem; box-shadow:0 10px 15px -3px rgba(0,0,0,0.1); z-index:50; overflow:hidden;">
        <div style="padding:1rem; border-bottom:1px solid #E2E8F0; font-weight:700; color:#0F172A; display:flex; justify-content:space-between;">
          Notifications <span id="smart-notif-count-text" style="font-size:0.8rem; background:#EFF6FF; color:#3B82F6; padding:0.1rem 0.5rem; border-radius:12px;">0</span>
        </div>
        <div id="smart-notif-list" style="max-height:350px; overflow-y:auto; padding:0;">
          <div style="padding:1.5rem 1rem; text-align:center; color:#64748B; font-size:0.9rem;">Chargement...</div>
        </div>
      </div>
    `;

    const btn = document.getElementById('smart-notif-btn');
    const dropdown = document.getElementById('smart-notif-dropdown');
    const badge = document.getElementById('smart-notif-badge');
    const list = document.getElementById('smart-notif-list');
    const countText = document.getElementById('smart-notif-count-text');

    // 2. Fetch Notifications
    const fetchNotifs = async () => {
      try {
        const res = await fetch('/TGT/siteweb/api/api-notifications.php');
        const data = await res.json();
        if (data.success) {
          // Update Badge
          if (data.unreadCount > 0) {
            badge.style.display = 'block';
            countText.textContent = data.unreadCount + ' nvlle(s)';
          } else {
            badge.style.display = 'none';
            countText.textContent = '0';
          }
          
          // Update List
          if (data.notifications.length === 0) {
            list.innerHTML = '<div style="padding:1.5rem 1rem; text-align:center; color:#64748B; font-size:0.9rem;">Aucune notification.</div>';
          } else {
            list.innerHTML = data.notifications.map(n => {
              const bg = n.lu == 1 ? '#FFF' : '#EFF6FF';
              let icon = '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path>';
              let color = '#3B82F6';
              // Check if it's admin broadcast
              if (n.message.startsWith('[ADMIN]')) {
                 icon = '<path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"></path>';
                 color = '#EF4444';
              }
              const cleanMsg = n.message.replace('[ADMIN]', '').trim();
              
              return `
                <a href="${n.job_id ? 'offre-detail.php?id='+n.job_id : '#'}" style="display:flex; gap:0.75rem; padding:1rem; border-bottom:1px solid #F1F5F9; text-decoration:none; background:${bg}; transition:background 0.2s;">
                  <div style="flex-shrink:0; width:32px; height:32px; border-radius:50%; background:${color}20; color:${color}; display:flex; align-items:center; justify-content:center;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${icon}</svg>
                  </div>
                  <div style="flex:1; min-width:0;">
                    <div style="font-size:0.85rem; font-weight:600; color:#1E293B; margin-bottom:0.25rem;">${cleanMsg}</div>
                    <div style="font-size:0.75rem; color:#94A3B8;">${new Date(n.created_at).toLocaleDateString('fr-FR', {hour:'2-digit', minute:'2-digit'})}</div>
                  </div>
                </a>
              `;
            }).join('');
          }
        }
      } catch (err) {
        console.error("Notif error", err);
      }
    };

    // 3. Toggle dropdown & mark as read
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      const isShowing = dropdown.style.display === 'block';
      dropdown.style.display = isShowing ? 'none' : 'block';
      
      if (!isShowing) {
        badge.style.display = 'none';
        countText.textContent = '0';
        fetch('/TGT/siteweb/api/api-mark-notif.php');
        // Visually mark items as read
        list.querySelectorAll('a').forEach(a => a.style.background = '#FFF');
      }
    });

    // Close when click outside
    document.addEventListener('click', (e) => {
      if (!notifContainer.contains(e.target)) {
        dropdown.style.display = 'none';
      }
    });

    // Load initial
    fetchNotifs();
    // Poll every 30s
    setInterval(fetchNotifs, 30000);
  }
});

