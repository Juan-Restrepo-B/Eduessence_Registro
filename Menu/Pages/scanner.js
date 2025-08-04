document.addEventListener('DOMContentLoaded', function () {
    const video = document.getElementById('qrScanner');
    const messageDiv = document.getElementById('message');
    
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ video: true }).then(function (stream) {
            video.srcObject = stream;
            video.play();
            
            const scanner = new Instascan.Scanner({ video: video });
            
            scanner.addListener('scan', function (content) {
                const params = obtenerParametrosURL(content);
                verificarRegistro(params);
            });
            
            Instascan.Camera.getCameras().then(function (cameras) {
                if (cameras.length > 0) {
                    scanner.start(cameras[0]);
                } else {
                    console.error('No se encontraron cámaras.');
                }
            });
        }).catch(function (error) {
            console.error('Acceso a la cámara denegado:', error);
        });
    }
});

function obtenerParametrosURL(url) {
    const searchParams = new URLSearchParams(url.split('?')[1]);
    const params = {};
    for (const [key, value] of searchParams.entries()) {
        params[key] = value;
    }
    return params;
}

function verificarRegistro(params) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'guardar_qr.php', true); // Cambiar a tu archivo de PHP
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                console.log(xhr.responseText); // Ver la respuesta del servidor en la consola
                const response = JSON.parse(xhr.responseText);
                mostrarMensaje(response.message);
            } else {
                console.error('Error en la verificación:', xhr.status, xhr.statusText);
            }
        }
    };
    const data = `userid=${encodeURIComponent(params.userid)}`;
    xhr.send(data);
}


function mostrarMensaje(message) {
    const messageDiv = document.getElementById('message');
    messageDiv.textContent = message;
    messageDiv.style.display = 'block'; // Mostrar el mensaje
    if (message.indexOf('exitoso') >= 0) {
    messageDiv.className = "color-green"
    console.log('Verde');
    } else if (message.indexOf('registrada') >= 0) {
        messageDiv.className = "color-red"
        console.log('Rojo');
    }

    setTimeout(function() {
        messageDiv.style.display = 'none'; // Ocultar el mensaje después de unos segundos
    }, 5000); // Cambiar a la cantidad de milisegundos que desees (5000 = 5 segundos)
}
