(function () {
  const navLinks = document.querySelectorAll('a[href^="#"], a[href*="/#"]');

  navLinks.forEach((link) => {
    link.addEventListener("click", () => {
      link.blur();
    });
  });
})();
