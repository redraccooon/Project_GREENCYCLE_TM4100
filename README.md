# GreenCycle 🌱

Vivero digital gamificado desarrollado como proyecto final del curso **TM-4100 Desarrollo de Aplicaciones Interactivas I** (II Semestre, 2026), Universidad de Costa Rica.

En GreenCycle, la persona usuaria planta árboles virtuales que crecen, se deterioran o mueren en función del tiempo real. Toda la lógica de negocio (crecimiento, deterioro, economía) es controlada exclusivamente por el servidor; el cliente nunca define estado interno del juego.

---

## Alcance actual (Sprint 1)

Al finalizar esta entrega, una persona usuaria puede:

- Registrarse e iniciar sesión mediante autenticación por token (Laravel Sanctum).
- Consultar su sesión activa (`/me`) y cerrar sesión.
- Plantar un árbol propio, indicando únicamente los datos que el dominio permite al cliente definir (por ejemplo, la especie/`tree_type`).
- Consultar el listado de sus árboles y el detalle de un árbol propio.
- Ver la interfaz reaccionar sin recargas de página, con estados de carga, vacío, éxito y error.

**Queda deliberadamente fuera de este sprint** (planificado para Sprint 2 en adelante):

- El Job asíncrono (`Scheduler`) que evalúa `base_growth_time_seconds` y aplica crecimiento/deterioro con base en el tiempo transcurrido.
- La lógica de intervalos de cuidado (`care_interval_seconds` u opción de configuración global — decisión pendiente, ver sección 3.5 del documento de entrega).
- La economía del juego (`green_coins`), el inventario de ítems y sus efectos.

Esta separación es intencional: el modelo de datos ya contempla estas extensiones (migraciones aditivas, tablas `items` e `inventories`, columna `green_coins` en `users`) sin necesidad de rediseñar el esquema en sprints futuros.

---

## Stack / requisitos

| Componente | Detalle |
|---|---|
| Backend | PHP 8.3, Laravel 13.8 (MVC) |
| ORM | Eloquent |
| Autenticación | Laravel Sanctum (token-based) |
| Frontend | HTML5 semántico, CSS3 puro, JavaScript ES6+ (Fetch API) — sin frameworks frontend |
| Base de datos | MySQL (vía Laravel Herd) |
| Entorno local | Laravel Herd (dominios `.test`, MySQL integrado) |
| Gestor de dependencias PHP | Composer |
| Gestor de dependencias JS | npm (si aplica para build de assets) |

> **Nota de arquitectura:** este proyecto usa Laravel 13, por lo que **no existe** `app/Http/Kernel.php`; el registro de middleware y rutas se realiza en `bootstrap/app.php`. Tampoco existe `routes/api.php` por defecto: se creó manualmente y se registró explícitamente.

---

## Instalación

1. **Clonar el repositorio**

   ```bash
   git clone https://github.com/redraccooon/Project_GREENCYCLE_TM4100.git
   cd Project_GREENCYCLE_TM4100
   ```

2. **Instalar dependencias PHP**

   ```bash
   composer install
   ```

3. **Configurar el entorno**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Edite `.env` con los datos de su base de datos local (ver sección de Configuración más abajo). Si usa Laravel Herd, el servicio de MySQL integrado ya provee host, usuario y contraseña por defecto — revise la documentación de Herd o TablePlus para confirmar los valores en su máquina.

4. **Instalar Laravel Sanctum (si el `vendor:publish` no se ejecutó aún)**

   ```bash
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   ```

5. **Ejecutar migraciones y seeders**

   ```bash
   php artisan migrate:fresh --seed
   ```

   Esto crea las tablas del dominio (`tree_types`, `trees`, `items`, `inventories`, y la columna `green_coins` en `users`) y ejecuta `TreeTypeSeeder` e `ItemSeeder` para poblar datos base.

6. **Instalar dependencias de frontend (si corresponde)**

   ```bash
   npm install
   npm run build
   ```

---

## Configuración

Variables de entorno necesarias (sin exponer secretos reales en el repositorio):

```env
APP_NAME=GreenCycle
APP_ENV=local
APP_KEY=            # generado con php artisan key:generate
APP_DEBUG=true
APP_URL=http://greencycle.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=greencycle
DB_USERNAME=root
DB_PASSWORD=
```

> Recuerde: `.env` **nunca** debe subirse al repositorio. Use `.env.example` como plantilla documentada de las variables requeridas, sin valores sensibles reales.

---

## Ejecución

Con **Laravel Herd**, el proyecto se sirve automáticamente en `http://greencycle.test` (o el dominio `.test` que Herd asigne según el nombre de la carpeta) — no es necesario ejecutar `php artisan serve`.

Si no se usa Herd, se puede levantar manualmente con:

```bash
php artisan serve
```

La aplicación quedará disponible en `http://localhost:8000`.

El frontend (`app.html`, `app.css`, `app.js`) consume la API mediante `fetch`, por lo que basta con acceder a la ruta correspondiente servida por Laravel para probar el flujo completo end-to-end.

---

## Pruebas / verificación

Comandos útiles para verificar el estado de la entrega:

```bash
# Verificar que las rutas API estén correctamente registradas
php artisan route:list --path=api

# Verificar el estado de las migraciones
php artisan migrate:status

# (Opcional) Inspeccionar visualmente la base de datos
# mediante TablePlus, conectado vía la integración con Laravel Herd
```

**Evidencia recomendada para la defensa del sprint:**
- Captura de `php artisan route:list --path=api` mostrando los endpoints protegidos por `auth:sanctum`.
- Captura en TablePlus del esquema generado (tablas, relaciones, columnas nuevas).
- Demostración de un usuario no autenticado intentando acceder a `/api/trees` y recibiendo un `401 Unauthorized`.

---

## API

La API sigue el estándar RESTful bajo el prefijo `/api`, protegida con Laravel Sanctum. Endpoints obligatorios de este sprint:

| Método | Ruta | Propósito | Auth |
|---|---|---|---|
| POST | `/api/register` | Registro de usuario | No |
| POST | `/api/login` | Inicio de sesión (emite token) | No |
| POST | `/api/logout` | Cierre de sesión (revoca token) | Sí |
| GET | `/api/me` | Consultar usuario autenticado | Sí |
| GET | `/api/trees` | Listar árboles del usuario autenticado | Sí |
| GET | `/api/trees/{tree}` | Consultar el detalle de un árbol propio | Sí |
| POST | `/api/trees` | Plantar un árbol | Sí |

**Reglas de validación y autorización:**
- Las solicitudes de creación de árbol se validan mediante `StoreTreeRequest` (Form Request), rechazando cualquier campo que intente definir estado interno (`status`, `level`, `health`, `progress`, timestamps de cuidado, etc.).
- El acceso a un árbol específico (`show`) está protegido por `TreePolicy`, que verifica que el árbol pertenezca al usuario autenticado antes de autorizar la operación.
- Los valores internos del dominio se asignan **siempre en el servidor**, nunca a partir del payload del cliente — principio aplicado de forma consistente en creación de árboles y registro de usuarios.

---

## Credenciales demo

<!-- COMPLETAR: solo incluir si la estrategia de entrega del equipo lo permite. Nunca usar credenciales personales reales. -->

```
Email: demo@greencycle.test
Password: demo1234*
```

---

## Equipo y atribuciones

| Integrante | Rol / responsabilidad |
|---|---|
| Eduardo Alberto Arias Morales | Backend/Frontend |
| Hugo Josué Carranza Arroyo | Backend/Frontend |

**Ramas utilizadas en este sprint:**
- `sprint1/data-model-trees` — migraciones, modelos Eloquent, seeders.
- `sprint1/api-trees` — Sanctum, `AuthController`, `TreeController`, `TreePolicy`, `routes/api.php`.
- `sprint1/frontend-integration` — utilidad `apiFetch`, interfaz `app.html/css/js`.

Todas las ramas se integraron mediante merges `--no-ff` para preservar el historial como bloques identificables de evidencia académica. La entrega queda marcada con el tag `sprint1-entrega`.

**Herramientas y recursos utilizados:**
- Laravel Herd (entorno de desarrollo local).
- TablePlus (inspección visual de base de datos).
- Visual Studio Code con extensiones PHP Intelephense, Laravel Blade Snippets y Laravel Extra Intellisense.
- Asistencia de IA (Claude, Gemini) para guía técnica y redacción de documentación — usada como apoyo, con todas las decisiones de arquitectura revisadas y defendidas por el equipo.