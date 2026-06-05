document.addEventListener("DOMContentLoaded", function() {
  var _a;
  const loginform = document.querySelector("#loginform");
  if (loginform) {
    loginform.classList.add("otp-loginform");
    const el = document.querySelector("input[name='pwd']");
    if (el) {
      el.setAttribute("autocomplete", "off");
      el.setAttribute("readonly", true);
      setTimeout(() => el.removeAttribute("readonly"), 500);
    }
    const rememberBox = document.querySelector("#rememberme");
    if (rememberBox) {
      rememberBox.checked = false;
      (_a = rememberBox.closest("p")) == null ? void 0 : _a.remove();
    }
  }
  const inputEl = document.querySelector('input.authcode[inputmode="numeric"]');
  const expectedLength = Number(inputEl == null ? void 0 : inputEl.dataset.digits) || 0;
  if (inputEl) {
    inputEl == null ? void 0 : inputEl.addEventListener(
      "input",
      function() {
        let value = this.value.replace(/[^0-9 ]/g, "").trimStart();
        this.value = value;
        if (expectedLength && value.replace(/ /g, "").length === expectedLength && !loginform.submit.disabled) {
          if (void 0 !== loginform.requestSubmit) {
            loginform.requestSubmit();
            loginform.submit.disabled = true;
          }
        }
      }
    );
  }
  const timer = document.querySelector("#countdown");
  if (!timer) return;
  let remaining = Number(timer.dataset.time) || 0;
  const render = () => {
    const mm = String(Math.floor(remaining / 60)).padStart(2, "0");
    const ss = String(remaining % 60).padStart(2, "0");
    timer.textContent = `${mm}:${ss}`;
  };
  const start = () => {
    if (remaining <= 0) {
      render();
      loginform.submit.disabled = true;
      return;
    }
    render();
    const id = setInterval(() => {
      remaining--;
      render();
      if (remaining <= 0) {
        clearInterval(id);
        loginform.submit.disabled = true;
      }
    }, 1e3);
  };
  start();
});
//# sourceMappingURL=login-otp.js.map
