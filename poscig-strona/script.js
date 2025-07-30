document.addEventListener("DOMContentLoaded", function () {
  const include = (selector, file) => {
    fetch(`components/${file}`)
      .then(res => res.text())
      .then(data => {
        document.querySelector(selector).outerHTML = data;
      });
  };

  include("#header", "header.html");
  include("#footer", "footer.html");
});
