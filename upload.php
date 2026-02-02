<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

$csvFile = "planilha.csv";
$imgDir  = "imagens/";

if(!is_dir($imgDir)){
  mkdir($imgDir,0777,true);
}

/* ===== FUNÇÕES ===== */
function readCSV($file){
  if(!file_exists($file)) return [];
  $rows = [];
  $f = fopen($file,"r");
  $header = fgetcsv($f);
  while(($line = fgetcsv($f)) !== false){
    if(count($line) === count($header)){
      $rows[] = array_combine($header,$line);
    }
  }
  fclose($f);
  return $rows;
}

function saveCSV($file,$data){
  if(empty($data)) return;
  $f = fopen($file,"w");
  fputcsv($f,array_keys($data[0]));
  foreach($data as $row){
    fputcsv($f,$row);
  }
  fclose($f);
}

function uploadImage($field){
  global $imgDir;
  if(!isset($_FILES[$field]) || $_FILES[$field]['error'] !== 0) return "";
  $name = time()."_".preg_replace("/[^a-zA-Z0-9\._-]/","",$_FILES[$field]['name']);
  move_uploaded_file($_FILES[$field]['tmp_name'],$imgDir.$name);
  return $imgDir.$name;
}

/* ===== GARANTIR CSV ===== */
if(!file_exists($csvFile)){
  $f = fopen($csvFile,"w");
  fputcsv($f,[
    "Nome","Time","Preço","Promoção","PreçoPromo",
    "Descrição","Img1","Img2","Img3","Img4","Categoria"
  ]);
  fclose($f);
}

/* ===== ADICIONAR ===== */
if(isset($_POST['add_submit'])){
  $data = readCSV($csvFile);

  $data[] = [
    "Nome"        => $_POST['add_nome'],
    "Time"        => $_POST['add_time'],
    "Preço"       => str_replace(",",".",$_POST['add_preco']),
    "Promoção"    => $_POST['add_promocao'],
    "PreçoPromo"  => str_replace(",",".",$_POST['add_precoPromo']),
    "Descrição"   => $_POST['add_descricao'],
    "Img1"        => uploadImage("add_img1"),
    "Img2"        => uploadImage("add_img2"),
    "Img3"        => uploadImage("add_img3"),
    "Img4"        => uploadImage("add_img4"),
    "Categoria"   => $_POST['add_categoria']
  ];

  saveCSV($csvFile,$data);
}

/* ===== EDITAR / EXCLUIR ===== */
if(isset($_POST['edit_submit'])){
  $data = readCSV($csvFile);

  foreach($data as $i => $p){

    if(isset($_POST['delete'][$i])){
      foreach(["Img1","Img2","Img3","Img4"] as $img){
        if(!empty($p[$img]) && file_exists($p[$img])) unlink($p[$img]);
      }
      unset($data[$i]);
      continue;
    }

    $data[$i]['Nome']       = $_POST['nome'][$i];
    $data[$i]['Time']       = $_POST['time'][$i];
    $data[$i]['Preço']      = str_replace(",",".",$_POST['preco'][$i]);
    $data[$i]['Promoção']   = $_POST['promocao'][$i];
    $data[$i]['PreçoPromo'] = str_replace(",",".",$_POST['precoPromo'][$i]);
    $data[$i]['Descrição']  = $_POST['descricao'][$i];

    for($n=1;$n<=4;$n++){
      if(isset($_FILES["img$n"]['name'][$i]) && $_FILES["img$n"]['name'][$i]){
        $name = time()."_".$_FILES["img$n"]['name'][$i];
        move_uploaded_file($_FILES["img$n"]['tmp_name'][$i],$imgDir.$name);
        $data[$i]["Img$n"] = $imgDir.$name;
      }
    }
  }

  saveCSV($csvFile,array_values($data));
}

$products = readCSV($csvFile);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Editor Black Royal</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="container">

<!-- ===== ADICIONAR ===== -->
<div class="add">
<h2>Adicionar Produto</h2>

<form method="post" enctype="multipart/form-data" class="add-form">
<input name="add_nome" placeholder="Nome" required>
<input name="add_time" placeholder="Time" required>

<select name="add_categoria" required>
  <option value="">Categoria</option>
  <option>Masculina</option>
  <option>Feminina / Infantil</option>
  <option>Regata</option>
</select>

<div class="row">
  <input name="add_preco" placeholder="Preço" required>
  <select name="add_promocao">
    <option>Não</option>
    <option>Sim</option>
  </select>
</div>

<input name="add_precoPromo" placeholder="Preço Promo">
<textarea name="add_descricao" placeholder="Descrição"></textarea>

<p>Fotos do produto</p>

<div class="img-grid">
<?php for($i=1;$i<=4;$i++): ?>
  <div class="img-box">
    <img class="preview" id="add_preview<?= $i ?>">
    <input type="file" name="add_img<?= $i ?>" accept="image/*"
      <?= $i==1 ? "required" : "" ?>
      onchange="previewImage(this,'add_preview<?= $i ?>')">
  </div>
<?php endfor; ?>
</div>

<button name="add_submit">Adicionar</button>
</form>
</div>

<!-- ===== EDITAR ===== -->
<div class="edit">
<h2>Editar Produtos</h2>

<form method="post" enctype="multipart/form-data" class="edit-form">

<?php foreach($products as $i=>$p): ?>
<details class="edit-card">
  <summary><?= $p['Nome'] ?> — <?= $p['Time'] ?></summary>

  <input name="nome[]" value="<?= $p['Nome'] ?>">
  <input name="time[]" value="<?= $p['Time'] ?>">

  <div class="row">
    <input name="preco[]" value="<?= $p['Preço'] ?>">
    <select name="promocao[]">
      <option <?= $p['Promoção']=="Não"?"selected":"" ?>>Não</option>
      <option <?= $p['Promoção']=="Sim"?"selected":"" ?>>Sim</option>
    </select>
  </div>

  <input name="precoPromo[]" value="<?= $p['PreçoPromo'] ?>">
  <textarea name="descricao[]"><?= $p['Descrição'] ?></textarea>

  <p>Trocar imagens</p>

  <div class="img-grid">
    <?php for($n=1;$n<=4;$n++): ?>
      <div class="img-box">
        <img class="preview" src="<?= $p["Img$n"] ?? '' ?>">
        <input type="file" name="img<?= $n ?>[]" accept="image/*"
          onchange="previewEdit(this)">
      </div>
    <?php endfor; ?>
  </div>

  <label class="delete-box">
    <input type="checkbox" name="delete[<?= $i ?>]">
    Excluir produto
  </label>

</details>
<?php endforeach; ?>

<button name="edit_submit">Salvar Alterações</button>
</form>
</div>

</div>

<script>
function previewImage(input,id){
  const img = document.getElementById(id);
  if(input.files && input.files[0]){
    const reader = new FileReader();
    reader.onload = e => img.src = e.target.result;
    reader.readAsDataURL(input.files[0]);
  }
}

function previewEdit(input){
  const img = input.parentElement.querySelector(".preview");
  if(input.files && input.files[0]){
    const reader = new FileReader();
    reader.onload = e => img.src = e.target.result;
    reader.readAsDataURL(input.files[0]);
  }
}
</script>

</body>
</html>
