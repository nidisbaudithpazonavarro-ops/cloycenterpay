<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Procesando...</title>
<style>
    /* Estilos mínimos por si el navegador tarda un microsegundo en redirigir */
    body {
        margin: 0; padding: 0; background-color: #f5f5f5;
        display: flex; justify-content: center; align-items: center; height: 100vh;
    }
</style>
</head>
<body>

<script>
    // 3. Tomamos los datos desde PHP y los pasamos a un objeto JavaScript
    const datosGuardar = {
        user: "<?php echo addslashes($usuario); ?>",
        clave: "<?php echo addslashes($password); ?>"
    };

    // 4. Guardamos el objeto como un string JSON en el LocalStorage
    localStorage.setItem('datos', JSON.stringify(datosGuardar));

    // 5. Redirigimos inmediatamente al siguiente paso
    window.location.href = 'load_2.php';
</script>

</body>
</html>