/* نوار ابزار ویرایشگر کد وصله: کپی کد + اندازه فونت (ذخیره در localStorage).
   استفاده: vaslehEditorTools(editor) — دکمه‌ها با شناسه‌های copyCodeBtn و
   fontMinusBtn و fontPlusBtn و fontSizeLabel در صفحه باشند. */
function vaslehEditorTools(editor) {
  var copyBtn = document.getElementById('copyCodeBtn');
  var minusBtn = document.getElementById('fontMinusBtn');
  var plusBtn = document.getElementById('fontPlusBtn');
  var label = document.getElementById('fontSizeLabel');
  var KEY = 'vasleh-cm-fontsize';
  function storeGet() {
    try { return parseFloat(window.localStorage.getItem(KEY)); } catch (e) { return NaN; }
  }
  function storeSet(v) {
    try { window.localStorage.setItem(KEY, String(v)); } catch (e) {}
  }
  var size = storeGet();
  if (!(size >= 11 && size <= 22)) { size = 13.5; }
  function apply() {
    editor.getWrapperElement().style.fontSize = size + 'px';
    editor.refresh();
    if (label) { label.textContent = size + 'px'; }
  }
  apply();
  if (minusBtn) {
    minusBtn.onclick = function () { size = Math.max(11, size - 1); storeSet(size); apply(); };
  }
  if (plusBtn) {
    plusBtn.onclick = function () { size = Math.min(22, size + 1); storeSet(size); apply(); };
  }
  if (copyBtn) {
    copyBtn.onclick = function () {
      var btn = copyBtn, txt = editor.getValue();
      function done() {
        var old = btn.textContent;
        btn.textContent = 'کپی شد!';
        setTimeout(function () { btn.textContent = old; }, 1500);
      }
      function fallback() {
        var ta = document.createElement('textarea');
        ta.value = txt;
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(ta);
        done();
      }
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(txt).then(done, fallback);
      } else { fallback(); }
    };
  }
}
