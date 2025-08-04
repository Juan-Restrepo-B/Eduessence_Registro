<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../index.php");
    exit();
}

// Verificar el tiempo de inactividad (10 minutos en este ejemplo)
$inactive_timeout = 10 * 60; // 10 minutes
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $inactive_timeout) {
    // Si ha pasado más de 10 minutos, destruir la sesión y redirigir al inicio de sesión
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit();
}

// Verificar si el usuario ya tiene una sesión activa en otro navegador
if (isset($_SESSION['single_session_id']) && $_SESSION['single_session_id'] !== session_id()) {
    header("Location: ../Validaciones/usersin.php");
    exit();
}

// Si no hay single_session_id en la sesión, asignarlo
if (!isset($_SESSION['single_session_id'])) {
    $_SESSION['single_session_id'] = session_id();
}

// Actualizar el tiempo de la última actividad en cada carga de página
$_SESSION['last_activity'] = time();

$sponser = $_SESSION['idsponser'];

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="48x48" href="../Img/Logo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../Css/Styles_Register_Visita.css">
    <title>REGISTRO VISTA</title>
</head>
<body>
    <iframe style="
    position: absolute;
    width: 100%;
    height: 100%;" src="https://registro.juanprestrepob.com/index.php" frameborder="0"></iframe>
    <script>// Funci贸n para obtener el valor de una cookie por su nombre
function getCookieValue(cookieName) {
  const cookies = document.cookie.split('; ');

  for (const cookie of cookies) {
    const [name, value] = cookie.split('=');
    if (name === cookieName) {
      return decodeURIComponent(value);
    }
  }

  return null; // Retorna null si no se encuentra la cookie
}

// Uso de la funci贸n para obtener el valor de la cookie "idsponsor"
const idSponsor = getCookieValue('idsponsor');

if (idSponsor !== null) {
  // Redirigir al sitio del Dominio 2 con el valor de la cookie en la URL
  window.location.href = 'https://registro.juanprestrepob.com/index.php?idsponsor=${idSponsor}';
} else {
  console.log('La cookie "idsponsor" no se encontr贸 o no tiene valor.');
}
</script>
    <footer class="footer">
        <p>&copy;Todos los derechos reservados por &nbsp;
        <div class="class"> Juan Restrepo</div>
        </p>
    </footer>
</body>

</html>