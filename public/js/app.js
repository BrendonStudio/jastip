/* Jastip — interaksi halaman (tanpa library). */
(function () {
  'use strict';

  var $ = function (sel, root) { return (root || document).querySelector(sel); };
  var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };
  var num = function (v) { var n = parseFloat(String(v).replace(',', '.')); return isNaN(n) ? 0 : n; };
  var rp = function (n) { return (n < -0.5 ? '−Rp ' : 'Rp ') + Math.abs(Math.round(n)).toLocaleString('id-ID'); };
  var rpk = function (n) { return (n < 0 ? '−Rp ' : 'Rp ') + Math.abs(n).toLocaleString('id-ID', { maximumFractionDigits: 2 }); };
  var kgBulat = function (b) { return Math.max(1, Math.ceil(b - 1e-9)); };
  var setText = function (root, key, val) { $$('[data-o="' + key + '"]', root).forEach(function (el) { el.textContent = val; }); };
  var checked = function (root, name) { var el = $('input[name="' + name + '"]:checked', root); return el ? el.value : null; };

  /* ---------- Tombol pilihan: tandai yang aktif (cadangan untuk browser tanpa :has) ---------- */
  function syncOpts(root) {
    $$('.opt input', root || document).forEach(function (inp) {
      var lab = inp.closest('.opt');
      if (lab) lab.classList.toggle('is-on', inp.checked);
    });
  }
  document.addEventListener('change', function (e) {
    if (e.target.matches('.opt input')) {
      var name = e.target.name;
      if (name && e.target.type === 'radio') {
        $$('input[name="' + name + '"]', e.target.form || document).forEach(function (r) {
          var lab = r.closest('.opt'); if (lab) lab.classList.toggle('is-on', r.checked);
        });
      } else {
        var lab = e.target.closest('.opt'); if (lab) lab.classList.toggle('is-on', e.target.checked);
      }
    }
  });

  /* ---------- Kalkulator estimasi (beranda) ---------- */
  function initCalc() {
    var root = $('[data-calc]');
    if (!root || !window.HARGA) return;
    var H = window.HARGA;
    function run() {
      var neg = checked(root, 'calc_neg') || 'jp';
      var jp = neg === 'jp';
      var c = H[neg];
      var pk = H.packs[checked(root, 'calc_pack') || 'standar'];
      var sym = jp ? '¥' : '₩';
      var harga = Math.max(0, num($('[data-calc-harga]', root).value));
      var berat = Math.max(0, num($('[data-calc-berat]', root).value));
      var bb = kgBulat(berat);
      var hargaIdr = harga * c.kj;
      var ongkir = bb * c.tj;
      setText(root, 'sym', sym);
      setText(root, 'kursLabel', sym + '1 = ' + rpk(c.kj));
      setText(root, 'kursFmt', rpk(c.kj));
      setText(root, 'hargaFmt', sym + harga.toLocaleString('id-ID'));
      setText(root, 'hargaIdr', rp(hargaIdr));
      setText(root, 'beratBulat', bb);
      setText(root, 'tarifFmt', rp(c.tj));
      setText(root, 'ongkir', rp(ongkir));
      setText(root, 'packLabel', pk.label);
      setText(root, 'packFee', rp(pk.jual));
      setText(root, 'total', rp(hargaIdr + ongkir + pk.jual));
      setText(root, 'gudang', jp ? 'Jepang' : 'Korea');
      var a = $('[data-o-accent]', root); if (a) a.style.background = jp ? '#C2311F' : '#1D4E9E';
    }
    root.addEventListener('input', run);
    root.addEventListener('change', run);
    run();
  }

  /* ---------- Stepper jumlah (− / +) ---------- */
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-step]');
    if (!btn) return;
    var box = btn.closest('[data-stepper]');
    var inp = $('input', box);
    var min = inp.min !== '' ? num(inp.min) : 1;
    var max = inp.max !== '' ? num(inp.max) : 999;
    var v = Math.min(max, Math.max(min, num(inp.value) + num(btn.getAttribute('data-step'))));
    inp.value = v;
    var show = $('[data-step-show]', box); if (show) show.textContent = v;
    inp.dispatchEvent(new Event('input', { bubbles: true }));
  });

  /* ---------- Nama file yang dipilih ---------- */
  document.addEventListener('change', function (e) {
    if (e.target.matches('input[type=file][data-file]')) {
      var box = e.target.closest('.drop');
      var lab = box && $('[data-file-name]', box);
      if (!lab) return;
      var f = e.target.files;
      lab.textContent = f.length === 0 ? lab.getAttribute('data-empty') : (f.length === 1 ? f[0].name : f.length + ' file dipilih');
    }
  });

  /* ---------- Form titip: tambah / hapus barang ---------- */
  function initOrderForms() {
    $$('[data-order-form]').forEach(function (form) {
      var list = $('[data-items]', form);
      var tpl = $('template[data-item-tpl]', form);
      function renumber() {
        var items = $$('[data-item]', list);
        items.forEach(function (it, i) {
          setText(it, 'no', i + 1);
          $$('[data-name]', it).forEach(function (inp) { inp.name = 'items[' + i + '][' + inp.getAttribute('data-name') + ']'; });
          $$('[data-remove-item]', it).forEach(function (b) { b.hidden = items.length < 2; b.setAttribute('aria-label', 'Hapus barang ' + (i + 1)); });
        });
        setText(form, 'nItem', items.length);
      }
      function syncNeg() {
        var neg = checked(form, 'country') || 'jp';
        var sym = neg === 'kr' ? '₩' : '¥';
        $$('[data-sym-ph]', form).forEach(function (inp) { inp.placeholder = sym; });
        setText(form, 'negNama', neg === 'kr' ? 'Korea' : 'Jepang');
        // Bayar sekarang = total harga di toko × kurs jastip
        if (window.HARGA) {
          var kj = window.HARGA[neg].kj, asing = 0;
          $$('[data-item]', list).forEach(function (it) {
            var p = $('[data-name=web_price]', it), q = $('[data-name=qty]', it);
            asing += Math.max(0, num(p ? p.value : 0)) * Math.max(1, num(q ? q.value : 1));
          });
          setText(form, 'bayarAsing', sym + asing.toLocaleString('id-ID'));
          setText(form, 'bayarKurs', sym + '1 = ' + rpk(kj));
          setText(form, 'bayarTotal', rp(asing * kj));
        }
      }
      form.addEventListener('click', function (e) {
        if (e.target.closest('[data-add-item]')) {
          var node = tpl.content.firstElementChild.cloneNode(true);
          list.appendChild(node);
          renumber(); syncNeg();
          var first = $('input', node); if (first) first.focus();
        }
        var rm = e.target.closest('[data-remove-item]');
        if (rm) { rm.closest('[data-item]').remove(); renumber(); syncNeg(); }
      });
      form.addEventListener('change', syncNeg);
      form.addEventListener('input', syncNeg);
      renumber(); syncNeg();
    });
  }

  /* ---------- Form daftar war: total & persetujuan ---------- */
  function initWarForms() {
    $$('[data-war-form]').forEach(function (form) {
      var price = num(form.getAttribute('data-price'));
      var kurs = num(form.getAttribute('data-kurs'));
      var fee = num(form.getAttribute('data-fee'));
      var dp = num(form.getAttribute('data-dp'));
      var agree = $('[data-agree]', form);
      var submit = $('[data-submit]', form);
      function run() {
        var qty = Math.max(1, num($('input[name=qty]', form).value));
        var barang = price * qty * kurs;
        var fees = fee * qty;
        var total = Math.round(barang + fees);
        var dpAmt = Math.round(total * dp / 100);
        setText(form, 'qty', qty);
        setText(form, 'barang', rp(barang));
        setText(form, 'fee', rp(fees));
        setText(form, 'total', rp(total));
        setText(form, 'dp', rp(dpAmt));
        setText(form, 'sisa', rp(total - dpAmt));
        if (submit && agree) submit.disabled = !agree.checked;
      }
      form.addEventListener('input', run);
      form.addEventListener('change', run);
      run();
    });
  }

  /* ---------- Tab sederhana ---------- */
  function initTabs() {
    $$('[data-tabs]').forEach(function (root) {
      root.addEventListener('click', function (e) {
        var b = e.target.closest('[data-tab]');
        if (!b) return;
        var id = b.getAttribute('data-tab');
        $$('[data-tab]', root).forEach(function (x) {
          var on = x.getAttribute('data-tab') === id;
          x.classList.toggle('is-on', on);
          x.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        $$('[data-pane]', root).forEach(function (p) { p.hidden = p.getAttribute('data-pane') !== id; });
      });
    });
  }

  /* ---------- Admin: pengaturan harga + simulasi untung ---------- */
  function initPricing() {
    var root = $('[data-pricing]');
    if (!root) return;
    function v(name) { var el = $('[name="' + name + '"]', root); return el ? num(el.value) : 0; }
    function run() {
      var n = checked(root, 'tab_neg') || 'jp';
      var sym = n === 'jp' ? '¥' : '₩';
      $$('[data-neg-panel]', root).forEach(function (p) { p.hidden = p.getAttribute('data-neg-panel') !== n; });
      setText(root, 'sym', sym);
      setText(root, 'negaraNama', n === 'jp' ? 'Jepang' : 'Korea');
      ['jp', 'kr'].forEach(function (c) {
        var km = v(c + '_kurs_modal'), kj = v(c + '_kurs_jual');
        setText(root, c + '_kursUntung', rpk(kj - km) + (km > 0 ? ' (' + ((kj - km) / km * 100).toFixed(1) + '%)' : ''));
        setText(root, c + '_ongkirUntung', rp(v(c + '_tarif_jual') - v(c + '_tarif_modal')));
      });
      ['standar', 'bubble', 'kayu'].forEach(function (p) { setText(root, 'pk_' + p, rp(v('pack_' + p + '_jual') - v('pack_' + p + '_modal'))); });

      var km = v(n + '_kurs_modal'), kj = v(n + '_kurs_jual'), tm = v(n + '_tarif_modal'), tj = v(n + '_tarif_jual');
      var kursSel = kj - km, ongkirSel = tj - tm;
      var harga = Math.max(0, num($('[data-sim=harga]', root).value));
      var berat = Math.max(0, num($('[data-sim=berat]', root).value));
      var fee = Math.max(0, num($('[data-sim=fee]', root).value));
      var pack = checked(root, 'sim_pack') || 'bubble';
      var labels = { standar: 'Standar', bubble: 'Bubble wrap', kayu: 'Peti kayu' };
      var bb = kgBulat(berat);
      var uKurs = harga * kursSel, uBerat = bb * ongkirSel, uPack = v('pack_' + pack + '_jual') - v('pack_' + pack + '_modal');
      var tahap1 = harga * kj, tahap2 = bb * tj + v('pack_' + pack + '_jual');
      var potong = tahap2 * fee / 100;
      var total = uKurs + uBerat + uPack - potong;
      var tagihan = tahap1 + tahap2;
      var pos = Math.max(0, uKurs) + Math.max(0, uBerat) + Math.max(0, uPack);
      var pct = function (x) { return pos > 0 ? (Math.max(0, x) / pos * 100).toFixed(1) + '%' : '0%'; };
      setText(root, 'tahap1', rp(tahap1));
      setText(root, 'tahap2', rp(tahap2));
      setText(root, 'untungTotal', rp(total));
      setText(root, 'simHargaFmt', sym + harga.toLocaleString('id-ID'));
      setText(root, 'kursSelisih', rpk(kursSel));
      setText(root, 'beratBulat', bb);
      setText(root, 'ongkirUntung', rp(ongkirSel));
      setText(root, 'simPackLabel', labels[pack]);
      setText(root, 'uKurs', rp(uKurs));
      setText(root, 'uBerat', rp(uBerat));
      setText(root, 'uPack', rp(uPack));
      setText(root, 'uShopee', rp(-potong));
      setText(root, 'feePct', fee.toLocaleString('id-ID') + '%');
      setText(root, 'marginPct', tagihan > 0 ? (total / tagihan * 100).toFixed(1) + '%' : '0%');
      var bk = $('[data-bar=kurs]', root), bbr = $('[data-bar=berat]', root), bp = $('[data-bar=pack]', root);
      if (bk) bk.style.width = pct(uKurs);
      if (bbr) bbr.style.width = pct(uBerat);
      if (bp) bp.style.width = pct(uPack);
    }
    root.addEventListener('input', run);
    root.addEventListener('change', run);
    run();
  }

  /* ---------- Admin: jam rilis lokal → WIB ---------- */
  function initWib() {
    $$('[data-wib-from]').forEach(function (out) {
      var inp = document.getElementById(out.getAttribute('data-wib-from'));
      if (!inp) return;
      function run() {
        var p = (inp.value || '00:00').split(':');
        var h = ((parseInt(p[0], 10) || 0) - 2 + 24) % 24;
        var t = String(h).padStart(2, '0') + ':' + (p[1] || '00');
        $$('[data-wib-out]').forEach(function (el) { el.textContent = t; });
      }
      inp.addEventListener('input', run); run();
    });
  }

  /* ---------- Kirim form otomatis saat isian berubah ---------- */
  document.addEventListener('change', function (e) {
    var f = e.target.closest('form[data-autosubmit]');
    if (f && !e.target.matches('[data-no-auto]')) f.submit();
  });

  /* ---------- Konfirmasi sebelum aksi penting ---------- */
  // Popup konfirmasi sendiri (bukan confirm() bawaan browser), tampil di tengah layar.
  var cfm;
  function confirmBox(msg, opt, done) {
    if (!cfm) {
      cfm = document.createElement('dialog');
      cfm.className = 'cfm';
      cfm.setAttribute('aria-labelledby', 'cfm-t');
      cfm.innerHTML = '<div class="cfm-ic" aria-hidden="true"></div>' +
        '<h2 id="cfm-t" class="cfm-t"></h2><p class="cfm-d"></p>' +
        '<div class="cfm-btns"><button type="button" class="cfm-no">Batal</button><button type="button" class="cfm-ok"></button></div>';
      document.body.appendChild(cfm);
      cfm.addEventListener('click', function (e) { if (e.target === cfm) cfm.close('no'); });
      cfm.querySelector('.cfm-no').addEventListener('click', function () { cfm.close('no'); });
      cfm.querySelector('.cfm-ok').addEventListener('click', function () { var fn = cfm._done; cfm._done = null; cfm.close('ok'); if (fn) fn(); });
    }
    var i = msg.indexOf('?'), title = i > -1 ? msg.slice(0, i + 1) : msg, desc = i > -1 ? msg.slice(i + 1).trim() : '';
    var tone = opt.tone || (/^(hapus|cabut|tolak|matikan|kembalikan|batalkan)/i.test(msg) ? 'danger' : (/sudah masuk|ditransfer|lunas|sesuai/i.test(msg) ? 'ok' : 'dark'));
    cfm.setAttribute('data-tone', tone);
    cfm.querySelector('.cfm-ic').textContent = tone === 'danger' ? '!' : (tone === 'ok' ? '✓' : '?');
    cfm.querySelector('.cfm-t').textContent = title;
    var d = cfm.querySelector('.cfm-d'); d.textContent = desc; d.hidden = !desc;
    cfm.querySelector('.cfm-ok').textContent = opt.ok || (tone === 'danger' ? 'Ya, lanjutkan' : (tone === 'ok' ? 'Ya, sudah' : 'Ya, lanjutkan'));
    cfm._done = done;
    cfm.showModal();
    (tone === 'danger' ? cfm.querySelector('.cfm-no') : cfm.querySelector('.cfm-ok')).focus();
  }
  document.addEventListener('submit', function (e) {
    var f = e.target, b = e.submitter || f;
    if (f._cfmOk) { f._cfmOk = false; return; }
    var src = b && b.getAttribute && b.getAttribute('data-confirm') ? b : f;
    var msg = src.getAttribute && src.getAttribute('data-confirm');
    if (!msg) return;
    e.preventDefault();
    if (typeof HTMLDialogElement !== 'function') { if (window.confirm(msg)) { f._cfmOk = true; submitWith(f, e.submitter); } return; }
    confirmBox(msg, { ok: src.getAttribute('data-confirm-ok'), tone: src.getAttribute('data-confirm-tone') }, function () {
      f._cfmOk = true; submitWith(f, e.submitter);
    });
  });
  function submitWith(f, btn) {
    if (f.requestSubmit) { f.requestSubmit(btn && btn !== f ? btn : undefined); return; }
    if (btn && btn.name) { var h = document.createElement('input'); h.type = 'hidden'; h.name = btn.name; h.value = btn.value; f.appendChild(h); }
    f.submit();
  }

  /* ---------- Checklist war: diingat di browser ---------- */
  function initChecklist() {
    $$('[data-remember]').forEach(function (cb) {
      var key = 'jastip:' + cb.getAttribute('data-remember');
      try { cb.checked = localStorage.getItem(key) === '1'; } catch (e) {}
      cb.addEventListener('change', function () { try { localStorage.setItem(key, cb.checked ? '1' : '0'); } catch (e) {} });
    });
  }

  /* ---------- Admin: total penawaran tahap 1 ---------- */
  function initOffer() {
    $$('[data-offer]').forEach(function (f) {
      var kurs = num(f.getAttribute('data-kurs'));
      function run() {
        var t = (Math.max(0, num($('[name=store_price]', f).value)) + Math.max(0, num($('[name=domestic_shipping]', f).value))) * kurs;
        setText(f, 'offerTotal', rp(t));
      }
      f.addEventListener('input', run); run();
    });
  }

  /* ---------- Hitung mundur kurs dikunci / batas bayar ---------- */
  function initCountdown() {
    $$('[data-countdown]').forEach(function (el) {
      var end = new Date(el.getAttribute('data-countdown')).getTime();
      function pad(n) { return (n < 10 ? '0' : '') + n; }
      function tick() {
        var s = Math.max(0, Math.floor((end - Date.now()) / 1000));
        var h = Math.floor(s / 3600), m = Math.floor((s % 3600) / 60), d = s % 60;
        el.textContent = h > 99 ? Math.floor(h / 24) + ' hari' : pad(h) + ':' + pad(m) + ':' + pad(d);
        if (s === 0 && el.hasAttribute('data-countdown-reload')) { el.removeAttribute('data-countdown-reload'); setTimeout(function () { location.reload(); }, 1500); }
      }
      tick(); setInterval(tick, 1000);
    });
  }

  /* ---------- Salin nomor rekening ---------- */
  document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-copy]');
    if (!b) return;
    var txt = b.getAttribute('data-copy');
    var done = function () { var old = b.textContent; b.textContent = 'Tersalin'; setTimeout(function () { b.textContent = old; }, 1600); };
    if (navigator.clipboard) navigator.clipboard.writeText(txt).then(done, done); else done();
  });

  /* ---------- Bottom sheet tugas (admin HP) ---------- */
  document.addEventListener('click', function (e) {
    var o = e.target.closest('[data-sheet-open]');
    if (o) { var d = document.getElementById(o.getAttribute('data-sheet-open')); if (d && d.showModal) { e.preventDefault(); d.showModal(); } return; }
    var c = e.target.closest('[data-sheet-close]');
    if (c) { var dl = c.closest('dialog'); if (dl) dl.close(); return; }
    if (e.target.tagName === 'DIALOG' && e.target.hasAttribute('data-sheet')) e.target.close();
  });

  /* ---------- Koneksi terputus ---------- */
  function initOffline() {
    var bar = document.getElementById('offline-bar');
    if (!bar) return;
    function upd() { bar.hidden = navigator.onLine !== false; }
    window.addEventListener('online', upd); window.addEventListener('offline', upd); upd();
    document.addEventListener('submit', function (e) {
      if (navigator.onLine === false) { e.preventDefault(); bar.hidden = false; bar.scrollIntoView({ block: 'nearest' }); }
    }, true);
  }

  function init() {
    initCountdown();
    initOffline();
    initOffer();
    syncOpts();
    initCalc();
    initOrderForms();
    initWarForms();
    initTabs();
    initPricing();
    initWib();
    initChecklist();
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
})();
