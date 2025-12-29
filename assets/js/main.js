window.addEventListener("DOMContentLoaded", () => {
  if (window.AOS && typeof AOS.init === "function") {
    AOS.init({ duration: 800, once: true });
  }
  document.querySelectorAll(".navbar-toggler").forEach(function (toggler) {
    var sel =
      toggler.getAttribute("data-bs-target") ||
      toggler.getAttribute("data-target") ||
      "#navbarsExample";
    var target = sel ? document.querySelector(sel) : null;
    if (target) {
      toggler.addEventListener("click", function () {
        target.classList.toggle("show");
      });
    }
  });

  function setupTypeahead(input) {
    if (!input) return;
    var menu = document.createElement("div");
    menu.className = "th-menu";
    document.body.appendChild(menu);
    var selectedIndex = -1;
    var items = [];

    function positionMenu() {
      var r = input.getBoundingClientRect();
      menu.style.position = "fixed";
      menu.style.left = r.left + "px";
      menu.style.top = r.bottom + 4 + "px";
      menu.style.width = r.width + "px";
    }
    function hide() {
      menu.style.display = "none";
      selectedIndex = -1;
      items = [];
    }
    function show() {
      positionMenu();
      menu.style.display = "block";
    }
    function render(list) {
      menu.innerHTML = "";
      items = list || [];
      selectedIndex = -1;
      if (!items.length) {
        hide();
        return;
      }
      var ul = document.createElement("div");
      ul.className = "th-list";
      items.forEach(function (txt, i) {
        var it = document.createElement("div");
        it.className = "th-item";
        it.textContent = txt;
        it.addEventListener("mousedown", function (e) {
          e.preventDefault();
          input.value = txt;
          hide();
          input.focus();
        });
        ul.appendChild(it);
      });
      menu.appendChild(ul);
      show();
    }
    function fetchSuggest(q) {
      if (!q) {
        hide();
        return;
      }
      fetch("suggest.php?q=" + encodeURIComponent(q))
        .then((r) => (r.ok ? r.json() : []))
        .then(render)
        .catch(() => hide());
    }
    input.addEventListener("input", function () {
      fetchSuggest(input.value.trim());
    });
    input.addEventListener("focus", function () {
      if (input.value.trim()) fetchSuggest(input.value.trim());
    });
    input.addEventListener("blur", function () {
      setTimeout(hide, 150);
    });
    window.addEventListener("scroll", positionMenu, true);
    window.addEventListener("resize", positionMenu);
    input.addEventListener("keydown", function (e) {
      if (menu.style.display !== "block") return;
      if (e.key === "ArrowDown") {
        e.preventDefault();
        selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
        updateActive();
      }
      if (e.key === "ArrowUp") {
        e.preventDefault();
        selectedIndex = Math.max(selectedIndex - 1, 0);
        updateActive();
      }
      if (e.key === "Enter") {
        if (selectedIndex >= 0) {
          e.preventDefault();
          input.value = items[selectedIndex];
          hide();
        }
      }
      if (e.key === "Escape") {
        hide();
      }
    });
    function updateActive() {
      var els = menu.querySelectorAll(".th-item");
      els.forEach(function (el, i) {
        el.classList.toggle("active", i === selectedIndex);
      });
      var el = els[selectedIndex];
      if (el) {
        el.scrollIntoView({ block: "nearest" });
      }
    }
  }
  setupTypeahead(document.querySelector('input[name="dest"]'));
});

function downloadInvoice() {
  if (!window.jspdf) {
    return window.print();
  }
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();
  const el = document.getElementById("invoice");
  if (!el) {
    return window.print();
  }
  let y = 10;
  doc.setFontSize(14);
  doc.text("Invoice", 105, y, { align: "center" });
  y += 10;
  doc.setFontSize(10);
  el.querySelectorAll("*").forEach((node) => {
    if (
      node.tagName === "H1" ||
      node.tagName === "H2" ||
      node.tagName === "H3"
    ) {
      doc.setFont(undefined, "bold");
    }
    const t = node.innerText?.trim();
    if (t) {
      doc.text(t.substring(0, 95), 14, y);
      y += 6;
    }
    if (y > 280) {
      doc.addPage();
      y = 10;
    }
  });
  doc.save("invoice.pdf");
}


