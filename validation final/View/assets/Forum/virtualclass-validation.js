/* ============================================================
   e-lite | Classes Virtuelles — Validation & UI JavaScript
   ============================================================ */
(function () {
  'use strict';

  function showError(fieldId, msg) {
    var field = document.getElementById(fieldId);
    if (!field) return;
    field.classList.add('input-error');
    field.classList.remove('input-ok');
    var wrapper = field.closest('.form-group') || field.parentElement;
    var existing = wrapper.querySelector('.field-error-msg');
    if (existing) { existing.textContent = msg; }
    else { var span = document.createElement('span'); span.className = 'field-error-msg'; span.textContent = msg; wrapper.appendChild(span); }
  }

  function clearError(fieldId) {
    var field = document.getElementById(fieldId);
    if (!field) return;
    field.classList.remove('input-error');
    field.classList.add('input-ok');
    var wrapper = field.closest('.form-group') || field.parentElement;
    var existing = wrapper.querySelector('.field-error-msg');
    if (existing) existing.remove();
  }

  function showToast(msg, type) {
    type = type || 'error';
    var old = document.getElementById('vc-toast');
    if (old) old.remove();
    var t = document.createElement('div');
    t.id = 'vc-toast';
    t.className = 'vc-toast vc-toast-' + type;
    t.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + '"></i> ' + msg;
    document.body.appendChild(t);
    requestAnimationFrame(function () { t.classList.add('show'); });
    setTimeout(function () { t.classList.remove('show'); setTimeout(function () { if (t.parentNode) t.remove(); }, 400); }, 3500);
  }

  function injectValidationStyles() {
    if (document.getElementById('vc-val-style')) return;
    var style = document.createElement('style');
    style.id = 'vc-val-style';
    style.textContent = '.input-error{border-color:#ef4444!important;box-shadow:0 0 0 3px rgba(239,68,68,.18)!important}.input-ok{border-color:#10b981!important;box-shadow:0 0 0 3px rgba(16,185,129,.12)!important}.field-error-msg{display:block;color:#ef4444;font-size:.78rem;margin-top:.35rem}.vc-toast{position:fixed;top:1.4rem;right:1.6rem;z-index:9999;padding:.85rem 1.4rem;border-radius:12px;font-size:.92rem;font-weight:500;display:flex;align-items:center;gap:.7rem;opacity:0;transform:translateY(-18px) scale(.97);transition:all .35s cubic-bezier(.34,1.56,.64,1);pointer-events:none;backdrop-filter:blur(12px);max-width:360px}.vc-toast.show{opacity:1;transform:translateY(0) scale(1);pointer-events:auto}.vc-toast-error{background:rgba(239,68,68,.14);border:1px solid rgba(239,68,68,.4);color:#fca5a5}.vc-toast-success{background:rgba(16,185,129,.14);border:1px solid rgba(16,185,129,.38);color:#6ee7b7}.char-counter{font-size:.75rem;color:#9ca3af;text-align:right;margin-top:.25rem}.char-counter.warn{color:#f59e0b}.char-counter.over{color:#ef4444;font-weight:600}@keyframes shake{0%,100%{transform:translateX(0)}20%,60%{transform:translateX(-6px)}40%,80%{transform:translateX(6px)}}.shake{animation:shake .4s ease}.search-highlight{background:rgba(234,179,8,.28);border-radius:3px;padding:0 2px}th[data-sort]{cursor:pointer;user-select:none}th[data-sort]:hover{color:var(--accent,#eab308)}th[data-sort] .sort-icon{margin-left:5px;opacity:.4;font-size:.75rem}th[data-sort].asc .sort-icon::before{content:"▲";opacity:1}th[data-sort].desc .sort-icon::before{content:"▼";opacity:1}th[data-sort]:not(.asc):not(.desc) .sort-icon::before{content:"⇅"}.stats-bar{display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1.2rem}.stats-chip{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:.55rem 1rem;font-size:.82rem;display:flex;align-items:center;gap:.55rem}.stats-chip .chip-val{font-size:1.15rem;font-weight:700;color:var(--accent,#eab308)}.search-wrapper{position:relative}.search-wrapper input{padding-left:2.2rem}.search-wrapper .search-icon{position:absolute;left:.7rem;top:50%;transform:translateY(-50%);color:#9ca3af;pointer-events:none}';
    document.head.appendChild(style);
  }

  function attachCharCounter(fieldId, max) {
    var field = document.getElementById(fieldId);
    if (!field) return;
    var counter = document.createElement('div');
    counter.className = 'char-counter';
    (field.closest('.form-group') || field.parentElement).appendChild(counter);
    function update() { var len = field.value.length; counter.textContent = len + ' / ' + max + ' caractères'; counter.className = 'char-counter' + (len > max ? ' over' : len > max * 0.85 ? ' warn' : ''); }
    field.addEventListener('input', update); update();
  }

  function attachLiveValidation(fieldId, validator) {
    var field = document.getElementById(fieldId);
    if (!field) return;
    ['input', 'change', 'blur'].forEach(function (ev) {
      field.addEventListener(ev, function () { var err = validator(field.value); if (err) showError(fieldId, err); else clearError(fieldId); });
    });
  }

  function validateTitre(val) { val = val.trim(); if (!val) return 'Le titre est obligatoire.'; if (val.length < 3) return 'Min 3 caractères.'; if (val.length > 120) return 'Max 120 caractères.'; return null; }
  function validateLienAcces(val) { val = val.trim(); if (!val) return "Le lien d'accès est obligatoire."; if (!/^https?:\/\/.+/.test(val)) return 'Le lien doit commencer par http:// ou https://'; return null; }
  function validateSelect(val, label) { return (!val || val === '') ? (label + ' est obligatoire.') : null; }
  function validateIdCourse(val) { if (!val || val === '') return null; var n = parseInt(val, 10); return (isNaN(n) || n <= 0) ? 'Cours invalide.' : null; }
  function validateDescription(val) { if (val.trim().length > 500) return 'Max 500 caractères.'; return null; }

  function initVirtualClassForm() {
    var form = document.getElementById('virtualClassForm');
    if (!form) return;
    attachCharCounter('vc_titre', 120);
    attachCharCounter('vc_description', 500);
    attachLiveValidation('vc_titre', validateTitre);
    attachLiveValidation('vc_description', validateDescription);
    attachLiveValidation('vc_lienAcces', validateLienAcces);
    attachLiveValidation('vc_plateforme', function (v) { return validateSelect(v, 'La plateforme'); });
    attachLiveValidation('vc_idCourse', validateIdCourse);
    form.addEventListener('submit', function (e) {
      var errors = [];
      [['vc_titre', validateTitre], ['vc_description', validateDescription], ['vc_lienAcces', validateLienAcces],
       ['vc_plateforme', function(v){return validateSelect(v,'La plateforme');}], ['vc_idCourse', validateIdCourse]
      ].forEach(function(pair) {
        var el = document.getElementById(pair[0]);
        var err = pair[1](el ? el.value : '');
        if (err) { showError(pair[0], err); errors.push(err); } else clearError(pair[0]);
      });
      if (errors.length > 0) {
        e.preventDefault();
        form.classList.add('shake');
        setTimeout(function () { form.classList.remove('shake'); }, 400);
        showToast('Veuillez corriger les ' + errors.length + ' erreur(s).', 'error');
        var first = form.querySelector('.input-error');
        if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });
  }

  function initSessionForm() {
    var form = document.getElementById('sessionForm');
    if (!form) return;
    function validateDate(val) { return !val ? 'La date est obligatoire.' : null; }
    function validateHeureFin(val) {
      var debut = document.getElementById('se_heureDebut') ? document.getElementById('se_heureDebut').value : '';
      if (!val) return "L'heure de fin est obligatoire.";
      if (debut && val <= debut) return "L'heure de fin doit être supérieure à l'heure de début.";
      return null;
    }
    function validateIdClass(val) { var n = parseInt(val, 10); return (isNaN(n) || n <= 0) ? 'Sélectionnez une classe virtuelle.' : null; }
    attachLiveValidation('se_dateSession', validateDate);
    attachLiveValidation('se_heureDebut', function (v) { return !v ? "L'heure de début est obligatoire." : null; });
    attachLiveValidation('se_heureFin', validateHeureFin);
    attachLiveValidation('se_statut', function (v) { return validateSelect(v, 'Le statut'); });
    attachLiveValidation('se_idClass', validateIdClass);
    form.addEventListener('submit', function (e) {
      var errors = [];
      function check(id, fn) { var el = document.getElementById(id); var err = fn(el ? el.value : ''); if (err) { showError(id, err); errors.push(err); } else clearError(id); }
      check('se_idClass', validateIdClass);
      check('se_dateSession', validateDate);
      check('se_heureDebut', function (v) { return !v ? "L'heure de début est obligatoire." : null; });
      check('se_heureFin', validateHeureFin);
      check('se_statut', function (v) { return validateSelect(v, 'Le statut'); });
      if (errors.length > 0) {
        e.preventDefault();
        form.classList.add('shake');
        setTimeout(function () { form.classList.remove('shake'); }, 400);
        showToast('Veuillez corriger les ' + errors.length + ' erreur(s).', 'error');
        var first = form.querySelector('.input-error');
        if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    });
  }

  function initDeleteConfirm() {
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var msg = form.getAttribute('data-confirm');
        var overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;inset:0;z-index:10000;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.6);backdrop-filter:blur(6px);';
        overlay.innerHTML = '<div style="background:#111;border:1px solid rgba(255,255,255,0.1);border-radius:18px;padding:2rem 2.5rem;max-width:400px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.6);"><div style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem;"><div style="width:44px;height:44px;border-radius:12px;background:rgba(239,68,68,0.12);display:flex;align-items:center;justify-content:center;color:#ef4444;font-size:1.3rem;"><i class="fas fa-exclamation-triangle"></i></div><h3 style="margin:0;font-size:1.1rem;">Confirmation</h3></div><p style="color:#9ca3af;margin:0 0 1.5rem;line-height:1.6;">' + msg + '</p><div style="display:flex;gap:0.8rem;justify-content:flex-end;"><button id="vc-cancel" style="padding:0.6rem 1.3rem;border-radius:10px;border:1px solid rgba(255,255,255,0.1);background:transparent;color:#9ca3af;cursor:pointer;">Annuler</button><button id="vc-ok" style="padding:0.6rem 1.3rem;border-radius:10px;border:none;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;cursor:pointer;font-weight:600;"><i class="fas fa-trash"></i> Supprimer</button></div></div>';
        document.body.appendChild(overlay);
        overlay.querySelector('#vc-cancel').addEventListener('click', function () { overlay.remove(); });
        overlay.querySelector('#vc-ok').addEventListener('click', function () { overlay.remove(); form.submit(); });
        overlay.addEventListener('click', function (e) { if (e.target === overlay) overlay.remove(); });
      });
    });
  }

  function initTableSearch(searchInputId, tableId) {
    var input = document.getElementById(searchInputId);
    var table = document.getElementById(tableId);
    if (!input || !table) return;
    var counterEl = document.getElementById(searchInputId + '-counter');
    input.addEventListener('input', function () {
      var term = input.value.trim().toLowerCase();
      var rows = table.querySelectorAll('tbody tr');
      var visible = 0;
      rows.forEach(function (row) {
        var match = !term || row.textContent.toLowerCase().includes(term);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
      });
      if (counterEl) counterEl.textContent = term ? (visible + ' résultat(s)') : '';
    });
  }

  function initTableSort(tableId) {
    var table = document.getElementById(tableId);
    if (!table) return;
    var headers = table.querySelectorAll('thead th[data-sort]');
    headers.forEach(function (th) {
      var icon = document.createElement('span'); icon.className = 'sort-icon'; th.appendChild(icon);
      th.addEventListener('click', function () {
        var col = parseInt(th.getAttribute('data-sort'), 10);
        var asc = !th.classList.contains('asc');
        headers.forEach(function (h) { h.classList.remove('asc', 'desc'); });
        th.classList.add(asc ? 'asc' : 'desc');
        var tbody = table.querySelector('tbody');
        var rows = Array.from(tbody.querySelectorAll('tr'));
        rows.sort(function (a, b) {
          var ta = (a.cells[col] ? a.cells[col].textContent : '').trim();
          var tb = (b.cells[col] ? b.cells[col].textContent : '').trim();
          var na = parseFloat(ta), nb = parseFloat(tb);
          if (!isNaN(na) && !isNaN(nb)) return asc ? na - nb : nb - na;
          return asc ? ta.localeCompare(tb, 'fr') : tb.localeCompare(ta, 'fr');
        });
        rows.forEach(function (r) { tbody.appendChild(r); });
      });
    });
  }

  function initStatsBar(containerId, tableId) {
    var container = document.getElementById(containerId);
    var table = document.getElementById(tableId);
    if (!container || !table) return;
    function compute() {
      var rows = Array.from(table.querySelectorAll('tbody tr')).filter(function (r) { return r.style.display !== 'none' && r.cells.length > 2; });
      var bar = document.getElementById(containerId + '-bar');
      if (!bar) { bar = document.createElement('div'); bar.id = containerId + '-bar'; bar.className = 'stats-bar'; container.prepend(bar); }
      var chips = [{ icon: 'fa-list', label: 'Total', val: rows.length }];
      if (container.dataset.mode === 'virtualclass') {
        var platforms = {};
        rows.forEach(function (r) { var p = r.cells[2] ? r.cells[2].textContent.trim() : '?'; platforms[p] = (platforms[p] || 0) + 1; });
        var top = Object.entries(platforms).sort(function (a, b) { return b[1] - a[1]; })[0];
        if (top) chips.push({ icon: 'fa-trophy', label: 'Top plateforme', val: top[0] + ' (' + top[1] + ')' });
      }
      if (container.dataset.mode === 'session') {
        var statuses = {};
        rows.forEach(function (r) { var badge = r.querySelector('.badge'); var s = badge ? badge.textContent.trim() : '?'; statuses[s] = (statuses[s] || 0) + 1; });
        Object.keys(statuses).forEach(function (s) { chips.push({ icon: 'fa-tag', label: s, val: statuses[s] }); });
      }
      bar.innerHTML = chips.map(function (c) { return '<div class="stats-chip"><i class="fas ' + c.icon + '" style="color:var(--accent,#eab308)"></i><span>' + c.label + '</span><span class="chip-val">' + c.val + '</span></div>'; }).join('');
    }
    compute();
    var si = document.querySelector('[id$="-search"]');
    if (si) si.addEventListener('input', function () { setTimeout(compute, 50); });
  }

  document.addEventListener('DOMContentLoaded', function () {
    injectValidationStyles();
    initVirtualClassForm();
    initSessionForm();
    initDeleteConfirm();
    initTableSearch('vc-search', 'vc-table');
    initTableSearch('se-search', 'se-table');
    initTableSort('vc-table');
    initTableSort('se-table');
    initStatsBar('vc-stats', 'vc-table');
    initStatsBar('se-stats', 'se-table');
  });
})();
