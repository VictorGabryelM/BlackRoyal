const csvURL = "planilha.csv";

Papa.parse(csvURL, {
  download: true,
  header: true,
  skipEmptyLines: true,
  complete: function (results) {
    const produtos = results.data;
    const container = document.getElementById("camisas");
    container.innerHTML = "";

    produtos.forEach((p, index) => {
      let precoHTML = `R$ ${p['Preço']}`;

      if (p['Promoção'] === "Sim" && p['PreçoPromo']) {
        precoHTML = `
          <span style="color:orange;">R$ ${p['PreçoPromo']}</span>
          <span style="text-decoration: line-through; opacity:0.6; margin-left:6px;">
            R$ ${p['Preço']}
          </span>
        `;
      }

      const card = document.createElement("div");
      card.classList.add("card");

      // CARD CLICÁVEL
      card.addEventListener("click", () => {
        window.location.href = `produto.html?id=${index}`;
      });

      card.innerHTML = `
        <img src="${p['Img1']}" alt="${p['Nome']}">
        <h3>${p['Nome']}</h3>
        <p>${p['Time']}</p>
        <strong>${precoHTML}</strong>
      `;

      container.appendChild(card);
    });
  },
  error: function (err) {
    console.error("Erro ao ler CSV:", err);
  }
});