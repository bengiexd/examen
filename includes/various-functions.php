<?php
/**
 * Funciones varias
 */

function get_pregunta($codPregunta) {
	global $bcdb;

	$q = sprintf("SELECT * FROM $bcdb->pregunta WHERE codPregunta = '%s'", $codPregunta);
	$pregunta = $bcdb -> get_row($q);

	$q2 = sprintf("SELECT * FROM $bcdb->alternativa WHERE codPregunta = '%s'", $codPregunta);
	$alternativas = $bcdb -> get_results($q2);

	foreach ($alternativas as $alternativa) {
		$pregunta['tema'] = $bcdb -> get_row(sprintf("SELECT * FROM %s WHERE codTema = '%s'", $bcdb -> tema, $pregunta['codTema']));
		$pregunta['curso'] = $bcdb -> get_row(sprintf("SELECT * FROM %s WHERE codCurso = '%s'", $bcdb -> curso, $pregunta['tema']['codCurso']));
		$pregunta['alternativas'][] = $alternativa;
	}
	return $pregunta;
}

/**
 * Devuelve los cursos asignados a un docente
 *
 * @param char $codDocente El código del docente
 * @return array
 */
function get_cursos_docente($codDocente) {
	global $bcdb;

	$q = sprintf("SELECT * 
          FROM %s CA
          INNER JOIN %s DC
          ON CA.codDocente = DC.codDocente
          AND CA.codCurso = DC.codCurso
          INNER JOIN %s C
          ON DC.codCurso = C.codCurso
          WHERE CA.codDocente = '%s'
          AND CA.codSemestre = '%s'", $bcdb -> cargaacademica, $bcdb -> docentecurso, $bcdb -> curso, $codDocente, get_option('semestre_actual'));
	$cursos = $bcdb -> get_results($q);
	return $cursos;
}

/**
 * Trae exámenes de un curso.
 *
 * @param char $codCurso El curso
 * @return array
 */
function get_examenes_curso($codCurso) {
	global $bcdb, $bcrs, $pager;

	$sql = sprintf("SELECT DISTINCT e.codExamen, e.nombre 
    FROM %s e
    INNER JOIN %s ep
    ON e.codExamen = ep.codExamen
    INNER JOIN %s p
    ON ep.codPregunta = p.codPregunta
    INNER JOIN %s t
    ON p.codTema = t.codTema
    WHERE t.codCurso = '%s'
    ORDER BY e.codExamen", $bcdb -> examen, $bcdb -> examenpregunta, $bcdb -> pregunta, $bcdb -> tema, $codCurso);
	$examenes = ($pager) ? $bcrs -> get_results($sql) : $bcdb -> get_results($sql);
	return $examenes;
}

/**
 * Trae temas de un curso
 *
 * @param char $codCurso El curso
 * @param char $codDocente El docente
 * @return array
 */
function get_temas_curso($codCurso, $codDocente) {
	global $bcdb;

	$q = sprintf("SELECT *
    FROM %s T
    WHERE T.codCurso = '%s'
    AND T.codDocente = '%s'", $bcdb -> tema, $codCurso, $codDocente);
	$temas = $bcdb -> get_results($q);
	return $temas;
}

/**
 * Guarda una asignación de un docente
 *
 * @param array $datos Los datos de la asignacion
 * @return boolean
 */
function save_asignacion($datos) {
	global $bcdb;
	$q = sprintf("INSERT INTO %s (codDocente, codCurso)
          VALUES ('%s', '%s')", $bcdb -> docentecurso, $datos['codDocente'], $datos['codCurso']);

	$resultado1 = $bcdb -> query($q);

	$q = sprintf("INSERT INTO %s (codDocente, codCurso, codSemestre)
          VALUES ('%s', '%s', '%s')", $bcdb -> cargaacademica, $datos['codDocente'], $datos['codCurso'], $datos['codSemestre']);

	$resultado2 = $bcdb -> query($q);

	return ($resultado1 && $resultado2);
}

function mostrarAsignaciones() {
	global $bcdb, $bcrs, $pager;
	$sql = sprintf("SELECT D.codDocente, D.nombres, D.apellidoP, D.apellidoM, C.codCurso, C.nombre
        FROM %s CA
        INNER JOIN %s D ON CA.codDocente = D.CodDocente
        INNER JOIN %s C ON CA.codCurso = C.CodCurso", $bcdb -> cargaacademica, $bcdb -> docente, $bcdb -> curso);

	$asignaciones = ($pager) ? $bcrs -> get_results($sql) : $bcdb -> get_results($sql);
	return $asignaciones;
}

/**
 * Guarda un usuario
 *
 * @param int $idusuario El id del usuario
 * @return boolean
 */
function save_user($idusuario, $user_values, $tabla) {
	global $bcdb, $msg;
	if ($idusuario && get_item($idusuario, $tabla)) {
		unset($user_values[$bcdb -> current_field]);
		// We don't want someone 'accidentally' update usuario
	}
	if (($query = insert_update_query($tabla, $user_values)) && $bcdb -> query($query)) {
		if (empty($idusuario))
			$idusuario = $bcdb -> insert_id;
		return $idusuario;
	}
	return false;
}

/**
<<<<<<< HEAD
 * Guarda preguntas relacionadas a un examen.
 * @param array $examen_pregunta los datos de la pregunta y el examen.
 * @return boolean
 */
function save_examen_pregunta($examen_pregunta) {
  global $bcdb;
  
  $sql = sprintf("INSERT INTO %s VALUES ('%s', '%s', '%s')",
          $bcdb->examenpregunta,
          $examen_pregunta['codExamen'],
          $examen_pregunta['codPregunta'],
          $examen_pregunta['puntaje']);
  echo $sql;
  return ($bcdb->query($sql));
}

/**
* Es Administrador
*
* @param int $idusuario El id del usuario
* @return boolean
*/
function is_admin ($idusuario) {
=======
 * Es Administrador
 *
 * @param int $idusuario El id del usuario
 * @return boolean
 */
function is_admin($idusuario) {
>>>>>>> a4de47e453c5e299b78650dcec4b1a90ea72dfe9
	return true;
}

/* FUNCIONES PARA REPORTES */
function get_curso_de_examen($codExamen) {
	global $bcdb;
	$sql = "SELECT DISTINCT c.codCurso, c.nombre FROM tCurso c
INNER JOIN tTema t ON t.codCurso = c.codCurso
INNER JOIN tPregunta p ON p.codTema = t.codTema
INNER JOIN tExamenPregunta ep ON ep.codPregunta = p.codPregunta
WHERE ep.codExamen = $codExamen;";

	return $bcdb -> get_results($sql);
}

function get_examen($codExamen) {
	global $bcdb;
	$sql = "SELECT * FROM tExamen WHERE codExamen = $codExamen;";

	return $bcdb -> get_results($sql);
}

function get_preguntas_de_examen($codExamen) {
	global $bcdb;
	$sql = "SELECT * FROM tPregunta p
INNER JOIN tExamenPregunta ep ON p.codPregunta = ep.codPregunta
WHERE ep.codExamen = $codExamen;";

	return $bcdb -> get_results($sql);
}

function get_alternativas_de_pregunta($codPregunta) {
	global $bcdb;
	$sql = "SELECT * FROM tAlternativa WHERE codPregunta = $codPregunta";

	return $bcdb -> get_results($sql);
}
?>