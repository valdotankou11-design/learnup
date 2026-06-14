/**
 * LearnUp — assets/js/app.js
 * Utilitaires globaux : Ajax, Toast, Modales, Progression
 */

/* ══════════════════════════════════════════════
   AJAX
   ══════════════════════════════════════════════ */
async function ajax(action, params = {}, method = 'POST') {
  const body = new URLSearchParams({ action, ...params });
  try {
    const res = await fetch('/api/index.php', {
      method,
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body,
    });
    const data = await res.json();
    return data;
  } catch (e) {
    console.error('[LearnUp] Erreur Ajax:', e);
    return { succes: false, message: 'Erreur réseau.' };
  }
}

async function ajaxFormData(action, formData) {
  formData.append('action', action);
  try {
    const res = await fetch('/api/index.php', { method: 'POST', body: formData });
    return await res.json();
  } catch (e) {
    return { succes: false, message: 'Erreur réseau.' };
  }
}

/* ══════════════════════════════════════════════
   TOAST
   ══════════════════════════════════════════════ */
function toast(message, type = 'info', duree = 3500) {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);
  }

  const icons = { succes: '✅', erreur: '❌', info: 'ℹ️' };
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.innerHTML = `<span>${icons[type] || 'ℹ️'}</span><span>${escHtml(message)}</span>`;
  container.appendChild(el);

  setTimeout(() => {
    el.style.animation = 'none';
    el.style.opacity = '0';
    el.style.transform = 'translateX(20px)';
    el.style.transition = 'all 0.3s ease';
    setTimeout(() => el.remove(), 300);
  }, duree);
}

/* ══════════════════════════════════════════════
   MODALES
   ══════════════════════════════════════════════ */
function ouvrirModal(id) {
  const m = document.getElementById(id);
  if (m) {
    m.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
}

function fermerModal(id) {
  const m = document.getElementById(id);
  if (m) {
    m.classList.remove('active');
    document.body.style.overflow = '';
  }
}

// Fermer en cliquant dehors
document.addEventListener('click', e => {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('active');
    document.body.style.overflow = '';
  }
});

// Fermer avec Échap
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.active').forEach(m => {
      m.classList.remove('active');
      document.body.style.overflow = '';
    });
  }
});

/* ══════════════════════════════════════════════
   PROGRESSION CIRCULAIRE
   ══════════════════════════════════════════════ */
function animerProgressionCirculaire(svgEl, pct) {
  const fill = svgEl.querySelector('.fill');
  if (!fill) return;
  const rayon  = 34;
  const circonf = 2 * Math.PI * rayon;
  fill.style.strokeDasharray  = circonf;
  fill.style.strokeDashoffset = circonf * (1 - pct / 100);
}

function animerToutesProgressions() {
  document.querySelectorAll('.progress-circle').forEach(el => {
    const pct = parseInt(el.dataset.pct || '0', 10);
    const svg = el.querySelector('svg');
    if (svg) animerProgressionCirculaire(svg, pct);
    const label = el.querySelector('.pct');
    if (label) {
      let n = 0;
      const step = () => {
        n = Math.min(n + 2, pct);
        label.textContent = n + '%';
        if (n < pct) requestAnimationFrame(step);
      };
      requestAnimationFrame(step);
    }
  });
}

/* ══════════════════════════════════════════════
   PROGRESSION LINÉAIRE
   ══════════════════════════════════════════════ */
function animerBarresProgression() {
  document.querySelectorAll('.progress-fill').forEach(el => {
    const pct = el.dataset.pct || '0';
    el.style.width = '0%';
    setTimeout(() => { el.style.width = pct + '%'; }, 100);
  });
}

/* ══════════════════════════════════════════════
   UTILITAIRES
   ══════════════════════════════════════════════ */
function escHtml(s) {
  return String(s)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function formatDate(ts) {
  return new Date(ts).toLocaleDateString('fr-FR', {
    day: '2-digit', month: 'short', year: 'numeric'
  });
}

function formatDuree(min) {
  if (min < 60) return `${min} min`;
  const h = Math.floor(min / 60);
  const m = min % 60;
  return m ? `${h}h ${m}min` : `${h}h`;
}

// Confirmer une action destructive
function confirmer(message, callback) {
  if (window.confirm(message)) callback();
}

/* ══════════════════════════════════════════════
   SIDEBAR MOBILE
   ══════════════════════════════════════════════ */
function toggleSidebar() {
  document.querySelector('.sidebar')?.classList.toggle('ouverte');
}

/* ══════════════════════════════════════════════
   INIT
   ══════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
  animerToutesProgressions();
  animerBarresProgression();
});
