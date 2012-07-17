<?php
/**
 * Gestión de usuarios que usan el sistema
 */
 
 
	require_once('home.php');
	require_once('redirect.php');
	
	
	$id = ! empty($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
  
  $bcdb->field_id = 'codUsuario';
	
	if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
		if ( validate_required(array(
		'Código' => $_POST['codUsuario'],
		'Nombres' => $_POST['nombres'], 
		'Apellido Paterno' => $_POST['apellidoP'],
		'Apellido Materno' => $_POST['apellidoM'],
		'Email' => $_POST['email'],
		))) {
			
			$error = false;
			if($_POST['pwd']!=$_POST['pwd2']) {
				$error = true;
				$msg = "Las contraseñas no coinciden.";
			}else{
				$pwd = trim($_POST['pwd']);
				if(empty($pwd)&&(!$id)) {
					$error = true;
					$msg = "La contrase&ntilde;a es un campo requerido.";
				}
			}
			
			if(!$error) :
				$user_values = array(
					'codUsuario' => $_POST['codUsuario'],
					'nombres' => $_POST['nombres'],
					'apellidoP' => $_POST['apellidoP'],
					'apellidoM' => $_POST['apellidoM'],
					'email' => $_POST['email'],
					'rol' => $_POST['rol']
				);
				
				if($id&&(!empty($_POST['pwd']))) {
					$user_values['password'] = md5(trim($_POST['pwd']));
				}
				
				if($id===0) {
					$user_values['password'] = md5(trim($_POST['pwd']));
				}
				
				$user_values = array_map('strip_tags', $user_values);
				$id = save_user($id, $user_values);
				if($id) $id = 0;
				$msg = "Los datos se guardaron satisfactoriamente.";
			endif;
		} else 
			$msg = "Ya existe el usuario '$user_values[usuario]'.";
	}
	
	$users = get_items($bcdb->usuario, 'codUsuario');
	if($users) {
		foreach($users as $k=>$user) {
			$users[$k]['rol'] = $autipos[$users[$k]['rol']];
		}
	}
	$user = array();
	if($id) {
		$user = get_item_by_field('codUsuario', $id, $bcdb->usuario);
	}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" media="screen" href="/css/reset.css" />
<link rel="stylesheet" type="text/css" media="screen" href="/css/text.css" />
<link rel="stylesheet" type="text/css" media="screen" href="/css/960.css" />
<link rel="stylesheet" type="text/css" media="screen" href="/css/layout.css" />
<link rel="stylesheet" type="text/css" media="screen" href="/css/jquery.autocomplete.css" />
<link rel="stylesheet" type="text/css" media="screen" href="/css/thickbox.css" />
<link href="/favicon.ico" type="image/ico" rel="shortcut icon" />
<script type="text/javascript" src="/scripts/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="/scripts/jquery.validate.js"></script>
<script type="text/javascript" src="/scripts/jquery.collapsible.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$("#frmusers").validate();
		
		/**
		 * Varios
		 */	
		$('#codUsuario').focus();
	});
</script>
<title>Usuarios | Sistema de Caja</title>
</head>

<body>
<div class="container_16">
  <div id="header">
    <h1 id="logo"> <a href="/"><span>Sistema de Caja</span></a> </h1>
    <?php include "menutop.php"; ?>
    <?php if(isset($_SESSION['loginuser'])) : ?>
    <div id="logout">Sesión: <?php print $_SESSION['loginuser']['nombres']; ?> <a href="logout.php">Salir</a></div>
    <?php endif; ?>
  </div>
  <div class="clear"></div>
  <div id="icon" class="grid_3">
    <p class="align-center"><img src="images/usuarios.png" alt="Usuarios" /></p>
  </div>
  <div id="content" class="grid_13">
    <h1>Usuarios</h1>
    <?php if (isset($msg)): ?>
    <p class="<?php echo ($error) ? "error" : "msg" ?>"><?php print $msg; ?></p>
    <?php endif; ?>
    <form name="frmusers" id="frmusers" method="post" action="usuarios.php">
      <fieldset class="collapsible">
        <legend>Datos del usuario</legend>
        <p>
          <label for="codUsuario">Código:</label>
          <input type="text" name="codUsuario" id="codUsuario" maxlength="6" size="10" value="<?php print ($user) ? $user['codUsuario'] : ""; ?>" />
        </p>
        <p>
          <label for="nombres">Nombres:</label>
          <input type="text" name="nombres" id="nombres" maxlength="255" size="60" value="<?php print ($user) ? $user['nombres'] : ""; ?>" />
        </p>
        <p>
          <label for="apellidoP">Apellido Paterno:</label>
          <input type="text" name="apellidoP" id="apellidoP" maxlength="255" size="60" value="<?php print ($user) ? $user['apellidoM'] : ""; ?>" />
        </p>
        <p>
          <label for="apellidoM">Apellido Materno:</label>
          <input type="text" name="apellidoM" id="apellidoM" maxlength="255" size="60" value="<?php print ($user) ? $user['apellidoM'] : ""; ?>" />
        </p>
        <p>
          <label for="email">E-mail:</label>
          <input type="text" name="email" id="email" maxlength="255" size="60" value="<?php print ($user) ? $user['email'] : ""; ?>" />
        </p>
        <p>
          <label for="rol">Tipo:</label>
          <select name="rol" id="rol">
            <?php foreach($autipos as $k => $v) : ?>
            <option value="<?php print $k; ?>" <?php print (isset($user['rol'])&&$user['rol'] == $k) ? 'selected="selected"' : ""; ?>><?php print $v; ?></option>
            <?php endforeach; ?>
          </select>
        </p>
        <p>
          <label for="pwd">Contraseña:</label>
          <input type="password" name="pwd" id="pwd" maxlength="100" title="Ingresa la contraseña" />
        </p>
        <p>
          <label for="pwd2">Otra vez:</label>
          <input type="password" name="pwd2" id="pwd2" maxlength="100" title="Ingresa la contraseña" />
          <br />
          <span class="nota">Si no va a cambiar la contraseña, deje los campos en blanco.</span> </p>
        <p class="align-center">
          <button type="submit" name="submit" id="submit">Guardar</button>
          <input type="hidden" name="id" id="id" value="<?php print $id; ?>" />
        </p>
      </fieldset>
    </form>
    <fieldset class="collapsibleClosed">
      <legend>Datos del usuario</legend>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombres</th>
            <th>Apellidos</th>
            <th>Rango</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($users): ?>
          <?php $alt = "even"; ?>
          <?php foreach($users as $k=> $usuario): ?>
          <tr class="<?php print $alt ?>">
            <th><?php print $usuario['codUsuario']; ?>
              </td>
            <th><?php print $usuario['nombres']; ?>
              </td>
            <td><?php print $usuario['apellidoP']; ?> <?php print $usuario['apellidoM']; ?></td>
            <td><?php print $usuario['rol']; ?></td>
            <td><a href="usuarios.php?id=<?php print $usuario['codUsuario']; ?>">Editar</a></td>
            <?php $alt = ($alt == "even") ? "odd" : "even"; ?>
          </tr>
          <?php endforeach; ?>
          <?php else: ?>
          <tr class="<?php print $alt; ?>">
            <td colspan="5">No existen datos
              </th>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
      <?php include "pager.php"; ?>
    </fieldset>
  </div>
  <div class="clear"></div>
  <?php include "footer.php"; ?>
</div>
</body>
</html>