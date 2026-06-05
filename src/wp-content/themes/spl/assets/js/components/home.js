document.addEventListener("DOMContentLoaded", () => {
  const animNum = (EL) => {
    if (EL._isAnimated) return;
    EL._isAnimated = true;
    $(EL).prop("Counter", 0).animate({
      Counter: EL.dataset.counter
    }, {
      duration: 5e3,
      easing: "linear",
      step: function(now) {
        const text = Math.ceil(now).toLocaleString("en-US");
        const html = text.split(",").map((n) => `<span class="count">${n}</span>`).join(",");
        $(this).html(html);
      }
    });
  };
  const inViewport = (entries, observer) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) animNum(entry.target);
    });
  };
  $("[data-counter]").each((i, EL) => {
    const observer = new IntersectionObserver(inViewport);
    observer.observe(EL);
  });
});
//# sourceMappingURL=home.js.map
