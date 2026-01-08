# Explicación de Archivos - Módulos G6 y W6 (Social y Gamificación)

Este documento detalla los archivos creados y modificados para la implementación de los módulos **G6 (Social: Chat y Mesas Privadas)** y **W6 (Gamificación y Sistema de Afiliados)**.

## 1. Controladores (Controllers)

Los controladores manejan la lógica de negocio y conectan los modelos con las vistas.

### `codigo/controllers/AfiliadoController.php`
*   **Descripción**: Gestiona el **Sistema de Afiliados**.
*   **Funcionalidad Principal**:
    *   Genera y guarda el código de referido único para el usuario si no lo tiene.
    *   Muestra el panel (`dashboard`) con el enlace para compartir.
    *   Calcula las comisiones basadas en los usuarios captados ("ahijados") que se han verificado.
*   **Actions**: `actionIndex`.

### `codigo/controllers/GamificacionController.php`
*   **Descripción**: Controlador para la parte visual de la **Gamificación** (Frontend del usuario).
*   **Funcionalidad Principal**:
    *   Muestra el "Muro de Logros" del usuario.
    *   Recupera los logros desbloqueados mediante la relación en el modelo `Usuario`.
*   **Actions**: `actionIndex`.

### `codigo/controllers/MesaPrivadaController.php`
*   **Descripción**: Gestiona las **Mesas Privadas** y el **Chat** asociado.
*   **Funcionalidad Principal**:
    *   **CRUD de Mesas**: Permite crear (`create`), listar (`index`) y borrar mesas.
    *   **Seguridad**: Gestiona el acceso mediante contraseña (`actionJoin`) y sesiones.
    *   **Sala de Juego (`actionRoom`)**: Integra la vista del juego (vía Iframe G3/G4) junto con el chat en tiempo real.
    *   **Chat**: Procesa el envío y lectura de mensajes (`MensajeChat`).

### `codigo/controllers/LogroController.php`
*   **Descripción**: Gestión administrativa (Back-office) de los **Logros**.
*   **Funcionalidad Principal**:
    *   Permite a los Administradores crear, editar y eliminar los logros disponibles en el sistema.
    *   Restringido solo a roles `admin` y `superadmin`.

---

## 2. Modelos (Models)

Representan la estructura de datos y la lógica de negocio.

### `codigo/models/Usuario.php` (Modificado)
*   **Descripción**: Modelo central del usuario.
*   **Cambios G6/W6**:
    *   **Relación `getAfiliados()`**: Define la relación "uno a muchos" para obtener los usuarios captados (`id_padrino`).
    *   **Relación `getLogros()`**: Define la relación "muchos a muchos" con `Logro` para obtener las medallas ganadas.
    *   **Atributos**: `codigo_referido_propio`, `id_padrino`.

### `codigo/models/MesaPrivada.php`
*   **Descripción**: Representa una mesa de juego privada creada por un usuario.
*   **Atributos**: `nombre`, `tipo_juego`, `password_hash` (contraseña de la sala), `id_anfitrion`, `estado_mesa`.
*   **Métodos**: `validarContrasena($password)` para el acceso seguro.

### `codigo/models/MensajeChat.php`
*   **Descripción**: Representa un mensaje individual enviado en una mesa.
*   **Atributos**: `mensaje`, `fecha_envio`, `id_usuario`, `id_mesa`.
*   **Lógica**: Validación de longitud y limpieza de texto.

### `codigo/models/Logro.php`
*   **Descripción**: Define qué es un logro (Medalla).
*   **Atributos**: `titulo`, `descripcion`, `icono_url`, `puntos_otorgados`.

### `codigo/models/LogroUsuario.php`
*   **Descripción**: Tabla intermedia (Pivote) que registra qué usuario tiene qué logro y cuándo lo obtuvo.
*   **Atributos**: `id_usuario`, `id_logro`, `fecha_obtencion`.

---

## 3. Vistas (Views)

Archivos que renderizan la interfaz de usuario.

### `codigo/views/afiliado/panel.php`
*   **Uso**: Interfaz del panel de afiliados.
*   **Contenido**: Muestra el código de referido, botón para copiar enlace, tabla con lista de afiliados y el total de comisiones generadas.

### `codigo/views/gamificacion/index.php`
*   **Uso**: Muro de Logros.
*   **Contenido**: Diseño visual (Grid/Tarjetas) que muestra las medallas desbloqueadas por el usuario, con sus iconos y descripciones.

### `codigo/views/mesa-privada/`
*   **`index.php`**: Listado ("Lobby") de mesas privadas activas donde los usuarios pueden elegir entrar.
*   **`create.php`**: Formulario para configurar una nueva mesa (Nombre, Juego, Contraseña).
*   **`join.php`**: Pantalla intermedia que solicita la contraseña para entrar a una mesa protegida.
*   **`room.php`**: **Vista Principal Integradora**. Divide la pantalla en:
    *   **Zona de Juego**: Iframe que carga el juego (Ruleta, Blackjack, etc.).
    *   **Zona de Chat**: Historial de mensajes y caja de texto para chatear con otros jugadores.

### `codigo/views/logro/`
*   **`index.php`, `view.php`, `create.php`, `update.php`**: Vistas estándar para que el administrador gestione el catálogo de logros.
