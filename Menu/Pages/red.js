// Función para obtener el valor de una cookie por su nombre
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

// Uso de la función para obtener el valor de la cookie "idsponsor"
const idSponsor = getCookieValue('idsponsor');

if (idSponsor !== null) {
  // Redirigir al sitio del Dominio 2 con el valor de la cookie en la URL
  window.location.href = `https://registro.juanprestrepob.com/index.php?idsponsor=${idSponsor}`;
} else {
  console.log('La cookie "idsponsor" no se encontró o no tiene valor.');
}
