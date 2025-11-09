document.addEventListener("DOMContentLoaded", function () {
  const cards = document.querySelectorAll(".card");

  cards.forEach(card => {
    // click para alternar
    card.addEventListener("click", function (e) {
      // evita que clique em link dentro do card dispare o flip
      if(e.target.closest('a')) return;
      card.classList.toggle("flipped");
      const pressed = card.classList.contains("flipped");
      card.setAttribute("aria-pressed", pressed ? "true" : "false");
    });

    // permitir abrir/fechar com Enter ou Space quando em foco (acessibilidade)
    card.addEventListener("keydown", function (e) {
      if (e.key === "Enter" || e.key === " ") {
        e.preventDefault();
        card.classList.toggle("flipped");
        const pressed = card.classList.contains("flipped");
        card.setAttribute("aria-pressed", pressed ? "true" : "false");
      } else if (e.key === "Escape") {
        card.classList.remove("flipped");
        card.setAttribute("aria-pressed", "false");
      }
    });
  });

  // opcional: fechar todos os cartões ao clicar fora
  document.addEventListener("click", function (e) {
    if(!e.target.closest(".card")) {
      document.querySelectorAll(".card.flipped").forEach(c => {
        c.classList.remove("flipped");
        c.setAttribute("aria-pressed", "false");
      });
    }
  });
});
