// ===== PREVIEW IMAGEM (ADICIONAR) =====
const addInput = document.getElementById("add_imagem");
const previewImg = document.getElementById("previewImg");
const previewText = document.getElementById("previewText");

if (addInput) {
  addInput.addEventListener("change", () => {
    const file = addInput.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = () => {
      previewImg.src = reader.result;
      previewImg.style.display = "block";
      previewText.style.display = "none";
    };
    reader.readAsDataURL(file);
  });
}

// ===== PREVIEW IMAGEM (EDITAR) =====
function previewEdit(input, index) {
  const img = document.getElementById("editImg" + index);
  const file = input.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = () => {
    img.src = reader.result;
  };
  reader.readAsDataURL(file);
}

// ===== MOSTRAR / ESCONDER PREÇO PROMO (ADD) =====
const promoAdd = document.querySelector('select[name="add_promocao"]');
const precoPromoAdd = document.querySelector('input[name="add_precoPromo"]');

if (promoAdd && precoPromoAdd) {
  precoPromoAdd.style.display = promoAdd.value === "Sim" ? "block" : "none";

  promoAdd.addEventListener("change", () => {
    precoPromoAdd.style.display = promoAdd.value === "Sim" ? "block" : "none";
  });
}

// ===== MOSTRAR / ESCONDER PREÇO PROMO (EDIT) =====
document.querySelectorAll('select[name="promocao[]"]').forEach((select, i) => {
  const promoInput = document.querySelectorAll('input[name="precoPromo[]"]')[i];

  promoInput.style.display = select.value === "Sim" ? "block" : "none";

  select.addEventListener("change", () => {
    promoInput.style.display = select.value === "Sim" ? "block" : "none";
  });
});
