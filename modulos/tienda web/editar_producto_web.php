<?php 
    require_once '../../includes/auth.php';

    // Obtenemos el ID del producto desde la URL. Si no existe, redirigimos.
    $producto_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($producto_id === 0) {
        header('Location: productos_web.php');
        exit;
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0"/> 
    <title>Solver | Editar Producto</title>
    <link rel="icon" href="../../assets/icono_solver_nobg.png" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background-color: #f4f7f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }

        .main-container {
            max-width: 800px;
            margin: 0px auto;
            padding: 10px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 600;
            color: #2c3e50;
        }

        .btn-volver {
            display: inline-block;
            padding: 10px 20px;
            background-color: #7f8c8d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            transition: background-color 0.2s ease;
        }

        .btn-volver:hover {
            background-color: #6c7a7b;
        }

        .form-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .btn-guardar {
            width: 100%;
            padding: 15px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .btn-guardar:hover {
            background-color: #218838;
        }

        /* Estilos para la vista previa de la imagen */
        .image-preview-container {
            margin-bottom: 15px;
            text-align: center;
        }

        #image-preview {
            width: 150px;
            height: 150px;
            border-radius: 5px;
            border: 1px solid #ddd;
            display: none; /* Oculta por defecto */
            object-fit: contain; /* Asegura que toda la imagen se vea sin recortar */
            background-color: #f8f9fa; /* Fondo sutil para el espacio sobrante */
        }

        #btn-remove-image {
            display: none; /* Oculto por defecto */
            margin-top: 10px;
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <div class="main-container">
        <div class="page-header">
            <h1>Editar Producto</h1>
            <a href="productos_web.php" class="btn-volver">Volver al Catálogo</a>
        </div>

        <div id="form-container" class="form-container">
            <form id="formEditarProducto" enctype="multipart/form-data">
                <input type="hidden" id="producto_id" name="id" value="<?php echo htmlspecialchars($producto_id); ?>">
                
                <div class="form-group">
                    <label for="nombre">Nombre del Producto</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>

                <div class="form-group">
                    <label for="descripcion_web">Descripción para la Tienda</label>
                    <textarea id="descripcion_web" name="descripcion_web"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="precio_venta">Precio de Venta (Gs.)</label>
                    <input type="number" id="precio_venta" name="precio_venta" required min="0">
                </div>

                <div class="form-group">
                    <label>Imagen del Producto</label>
                    <div class="image-preview-container">
                        <img id="image-preview" src="" alt="Vista previa">
                    </div>
                    <input type="file" id="imagen" name="imagen" accept="image/png, image/jpeg, image/webp">
                    <button type="button" id="btn-remove-image">Eliminar Imagen Actual</button>
                    <!-- Campo oculto para notificar a la API si se debe eliminar la imagen -->
                    <input type="hidden" id="remove_image_flag" name="remove_image_flag" value="0">
                </div>

                <button type="submit" class="btn-guardar">Guardar Cambios</button>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', async () => {
        const productoId = document.getElementById('producto_id').value;
        const form = document.getElementById('formEditarProducto');
        const container = document.getElementById('form-container');
        const imagePreview = document.getElementById('image-preview');
        const imageInput = document.getElementById('imagen');
        const btnRemoveImage = document.getElementById('btn-remove-image');
        const removeImageFlag = document.getElementById('remove_image_flag');

        // Cargar datos actuales del producto
        try {
            const response = await fetch(`../../api/tienda web/obtener_producto.php?id=${productoId}`);
            const res = await response.json();

            if (res.status === 'ok') {
                const producto = res.datos;
                document.getElementById('nombre').value = producto.nombre || '';
                document.getElementById('descripcion_web').value = producto.descripcion_web || '';
                document.getElementById('precio_venta').value = producto.precio_venta || 0;
                
                if (producto.imagen_url) {
                    // La URL guardada es una ruta absoluta desde la raíz del dominio, se puede usar directamente.
                    imagePreview.src = producto.imagen_url;
                    imagePreview.style.display = 'block';
                    btnRemoveImage.style.display = 'inline-block';
                }
            } else {
                container.innerHTML = `<p style="color: red;">Error: ${res.mensaje}</p>`;
            }
        } catch (error) {
            container.innerHTML = `<p style="color: red;">Error de conexión al cargar el producto.</p>`;
        }

        // Vista previa de la nueva imagen seleccionada
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                    removeImageFlag.value = '0'; // Si sube nueva, no queremos eliminarla
                    btnRemoveImage.style.display = 'inline-block';
                }
                reader.readAsDataURL(file);
            }
        });

        // Lógica para el botón de eliminar imagen
        btnRemoveImage.addEventListener('click', function() {
            imagePreview.style.display = 'none';
            imagePreview.src = '';
            btnRemoveImage.style.display = 'none';
            imageInput.value = ''; // Limpiamos el selector de archivo
            removeImageFlag.value = '1'; // Marcamos para que la API la elimine
        });

        // Lógica para guardar
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(form);
            try {
                const response = await fetch('../../api/tienda web/actualizar_producto_web.php', {
                    method: 'POST',
                    body: formData // Enviamos el formulario completo, incluyendo el archivo
                });
                const res = await response.json();

                if (res.status === 'ok') {
                    alert('Producto actualizado con éxito.');
                    window.location.href = 'productos_web.php';
                } else {
                    alert('Error al guardar: ' + res.mensaje);
                }
            } catch (error) {
                console.error('Error al guardar:', error);
                alert('Error de conexión al guardar los cambios.');
            }
        });
    });
    </script>

</body>
</html>