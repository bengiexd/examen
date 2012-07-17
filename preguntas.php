<?php

require_once('home.php');
require_once('redirect.php');

$id = ! empty($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

// Clave principal
$bcdb->current_field = 'codPregunta';

$postback = isset($_POST['submit']);
$error = false;

// Si es que el formulario se ha enviado
if($postback) :
  $pregunta = array(
    'codTema' => $_POST['codTema'],
    'enunciado' => $_POST['enunciado'],
    'nivel' => $_POST['nivel'],
  );

  // Verificación
  if (empty($pregunta['codTema']) || empty($pregunta['enunciado'])) :
    $error = true;
    $msg = "Ingrese la información obligatoria.";
  else :
    $pregunta = array_map('strip_tags', $pregunta);
  
    // Guarda la pregunta
    $id = save_item(0, $pregunta, $bcdb->pregunta);

    if ($id) :
      $bcdb->current_field = 'codAlternativa';
      $alternativas = $_POST['detalle'];
      foreach($alternativas as $k => $detalle) {
        $alternativa = array();
        $alternativa['detalle'] = $detalle;
        $alternativa['codPregunta'] = $id;
        if ((int)$_POST['correcta'] == $k) $alternativa['correcta'] = 'S';
        $alternativa = array_map('strip_tags', $alternativa);
        if (!empty($alternativa['detalle'])) :
          save_item(0, $alternativa, $bcdb->alternativa);
        endif;
      }
    endif;

    if($id) :
      $msg = "La información se guardó correctamente.";
    else:
      $error = true;
      $msg = "Hubo un error al guardar la información, intente nuevamente.";
    endif;
  endif;
endif;

$pregunta = array();
if($id) {
  $pregunta = get_pregunta($id);
}

$temas = get_items($bcdb->tema, 'codTema');
//$cursos = get_items($bcdb->curso, 'codCurso');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" media="screen" href="/css/reset.css" />
<link rel="stylesheet" type="text/css" media="screen" href="/css/text.css" />
<link rel="stylesheet" type="text/css" media="screen" href="/css/960.css" />
<link rel="stylesheet" type="text/css" media="screen" href="/css/layout.css" />
<link href="/favicon.ico" type="image/ico" rel="shortcut icon" />
<script type="text/javascript" src="/scripts/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="/scripts/jquery.collapsible.js"></script>
<script type="text/javascript" src="/scripts/jquery.jeditable.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
    $("#clone").click(function(){
      alternativas = $('#alternativas');
      tr = alternativas.find('tr:last').clone(true);
      n = alternativas.find('tr').length;
      tr.find('input').each(function() {
      if($(this).attr('type') == 'text') {
        $(this).val('');
        $(this).attr('id', 'detalle' + n);
      } else {
        $(this).val(n);
        $(this).attr('id', 'correcta' + n);
      }
      });
      console.log(tr);
      alternativas.append(tr);
      return false;
    });
    
		$('codTema').focus();
	});
</script>
<title>Preguntas | Sistema de exámenes</title>
</head>

<body>
<div class="container_16">
  <div id="header">
    <h1 id="logo"> <a href="/"><span>Sistema de exámenes</span></a> </h1>
    <?php include "menutop.php"; ?>
    <?php if(isset($_SESSION['loginuser'])) : ?>
    <div id="logout">Sesión: <?php print $_SESSION['loginuser']['nombres']; ?> <a href="logout.php">Salir</a></div>
    <?php endif; ?>
  </div>
  <div class="clear"></div>
  <div id="icon" class="grid_3">
    <div id="sidebar">
      <h3>Preguntas</h3>
      <ul>
        <li><a href="/preguntas.php">Crear preguntas</a></li>
        <li><a href="/lista-preguntas.php">Lista de preguntas</a></li>
      </ul>
    </div>
    <p class="align-center"><img src="images/opciones.png" alt="Opciones" /></p>
  </div>
  <div id="content" class="grid_13">
    <h1>Preguntas</h1>
    <?php if (isset($msg)): ?>
    <p class="<?php echo ($error) ? "error" : "msg" ?>"><?php print $msg; ?></p>
    <?php endif; ?>
    <form name="frmpregunta" id="frmpregunta" method="post" action="preguntas.php">
      <fieldset class="collapsible">
        <legend>Información de la pregunta</legend>
        <p>
          <label for="codTema">Tema <span class="required">*</span>:</label>
          <select name="codTema" id="codTema">
            <option value="" selected="selected">Seleccione un tema</option>
            <?php foreach ($temas as $k => $tema) : ?>
            <option value="<?php print $tema['codTema']; ?>"
              <?php
              if (count($pregunta) > 0) {
                if($tema['codTema'] == $pregunta['codTema']) 
                  print 'selected="selected"';
              }
              ?>>
                  <?php print $tema['nombre']; ?>
            </option>
            <?php endforeach; ?>
          </select>
        </p>
        <p>
          <label for="enunciado">Enunciado <span class="required">*</span>:</label>
          <input type="text" name="enunciado" id="enunciado" maxlength="200" size="60" value="<?php print ($pregunta) ? $pregunta['enunciado'] : ""; ?>" />
        </p>
        <p>
          <label for="nivel">Nivel <span class="required">*</span>:</label>
          <?php foreach($pniveles as $k => $nivel) : ?>
          <input type="radio" name="nivel" id="nivel<?php print $k; ?>" value="<?php print $k; ?>" <?php if ($k == 'N') print 'checked="checked"'?> />
          <label for="nivel<?php print $k; ?>"><?php print $nivel; ?></label>
          <?php endforeach; ?>
        </p>
      </fieldset>
      <fieldset class="collapsible">
        <legend>Alternativas</legend>
        <table>
          <caption>
          Alternativas
          </caption>
          <thead>
            <tr>
              <th>Opción</th>
              <th>¿Correcta?</th>
            </tr>
          </thead>
          <tbody id="alternativas">
            <?php $alt = "even"; ?>
            <?php if($id) : ?>
              <?php foreach($pregunta['alternativas'] as $k => $alternativa) : ?>
              <tr class="<?php print $alt ?>">
                <td><input type="text" name="detalle[]" id="detalle<?php print $alternativa['codAlternativa']; ?>" maxlength="50" size="60" tabindex="<?php print $i+5; ?>" value="<?php print $alternativa['detalle']; ?>" /></td>
                <th><input type="radio" name="correcta" id="correcta<?php print $alternativa['codAlternativa']; ?>" value="<?php print $alternativa['codAlternativa']; ?>" <?php if ($alternativa['correcta'] == 'S') print 'checked="checked"'?> /></th>
                <?php $alt = ($alt == "even") ? "odd" : "even"; ?>
              </tr>
              <?php endforeach; ?>
            <?php else : ?>
              <?php for($i = 0; $i < 5; $i++) : ?>
              <tr class="<?php print $alt ?>">
                <td><input type="text" name="detalle[]" id="detalle<?php print $i; ?>" maxlength="50" size="60" tabindex="<?php print $i+5; ?>" /></td>
                <th><input type="radio" name="correcta" id="correcta<?php print $i; ?>" value="<?php print $i ?>" <?php if ($i == '0') print 'checked="checked"'?> /></th>
                <?php $alt = ($alt == "even") ? "odd" : "even"; ?>
              </tr>
              <?php endfor; ?>
            <?php endif; ?>
          </tbody>
        </table>
        <p class="align-center"><a href="#" id="clone">Añadir otra alternativa</a></p>
        <p class="war">Si no va a utilizar un campo, déjelo vacío.</p>
      </fieldset>
      <p class="align-center">
        <button type="submit" name="submit" id="submit">Guardar</button>
      </p>
    </form>
  </div>
  <div class="clear"></div>
  <?php include "footer.php"; ?>
</div>
</body>
</html>