# GreenCycle

API REST de un juego de cuidado de árboles virtuales. Cada usuario planta árboles, los cuida para hacerlos crecer de nivel y, cuando maduran, los cosecha para ganar **green coins** que puede gastar en una tienda de ítems.

Proyecto del curso **TM-4100**, Universidad de Costa Rica.

---

## Tabla de contenidos

- [Tecnologías](#tecnologías)
- [Requisitos](#requisitos)
- [Instalación](#instalación)
- [Variables de entorno](#variables-de-entorno)
- [Base de datos](#base-de-datos)
- [Cuentas de prueba](#cuentas-de-prueba)
- [Endpoints](#endpoints)
- [Reglas de negocio](#reglas-de-negocio)
- [Pruebas automatizadas](#pruebas-automatizadas)
- [Estructura del proyecto](#estructura-del-proyecto)
- [Flujo de trabajo con Git](#flujo-de-trabajo-con-git)
- [Estado por sprint](#estado-por-sprint)
- [Equipo](#equipo)

---

## Tecnologías

| Capa | Tecnología |
| --- | --- |
| Lenguaje | PHP 8.3+ |
| Framework | Laravel 13 |
| Autenticación | Laravel Sanctum (tokens Bearer) |
| Base de datos | PostgreSQL en [Neon](https://neon.tech) |
| Base de datos de pruebas | SQLite en memoria |
| Pruebas | PHPUnit 12 (Feature Tests) |
| Frontend (assets) | Vite + Tailwind CSS 4 |

---

## Requisitos

- PHP 8.3 o superior, con las extensiones `pdo_pgsql` y `pdo_sqlite` habilitadas
- Composer 2
- Node.js 20 o superior y npm
- Una base de datos PostgreSQL (el proyecto usa Neon)

> En Windows con XAMPP, revisen que `extension=pdo_pgsql` y `extension=pdo_sqlite` no estén comentadas en `php.ini`.

---

## Instalación

```bash
# 1. Clonar el repositorio
git clone https://github.com/redraccooon/Project_GREENCYCLE_TM4100.git
cd Project_GREENCYCLE_TM4100
git checkout develop

# 2. Instalar dependencias
composer install
npm install

# 3. Crear el archivo de entorno y la llave de la aplicación
cp .env.example .env
php artisan key:generate

# 4. Configurar la conexión a Neon en .env (ver la sección siguiente)

# 5. Crear las tablas y cargar los datos iniciales
php artisan migrate:fresh --seed

# 6. Levantar el servidor
php artisan serve
```

La API queda disponible en `http://localhost:8000/api`.

---

## Variables de entorno

Las variables más importantes del `.env`:

| Variable | Ejemplo | Descripción |
| --- | --- | --- |
| `DB_CONNECTION` | `pgsql` | Motor de base de datos |
| `DB_URL` | `postgresql://usuario:clave@host.neon.tech/neondb` | Cadena de conexión que entrega Neon |
| `DB_SSLMODE` | `require` | Neon exige conexiones con SSL |
| `SESSION_DRIVER` | `database` | Usa la tabla `sessions` |
| `GREENCYCLE_CARE_COOLDOWN_MINUTES` | `60` | Minutos de espera entre cuidados de un mismo árbol |

`GREENCYCLE_CARE_COOLDOWN_MINUTES` permite acortar el cooldown para una demostración (por ejemplo, `1`) sin tocar el código. Después de cambiarla, corran:

```bash
php artisan config:clear
```

> El archivo `.env` nunca se sube al repositorio; contiene las credenciales de Neon.

---

## Base de datos

### Diagrama entidad-relación

```mermaid
erDiagram
    users ||--o{ trees : "planta"
    users ||--o{ inventories : "posee"
    users ||--o{ purchases : "realiza"
    tree_types ||--o{ trees : "clasifica"
    trees ||--o{ active_effects : "recibe"
    items ||--o{ inventories : "se guarda en"
    items ||--o{ purchases : "se compra en"
    items ||--o{ active_effects : "produce"

    users {
        bigint id PK
        varchar name
        varchar email UK
        varchar password
        integer green_coins "default 100"
        timestamp email_verified_at "nullable"
        varchar remember_token "nullable"
    }
    tree_types {
        bigint id PK
        varchar slug UK
        varchar name
        text description "nullable"
        smallint cares_per_level "default 5"
        smallint harvest_reward "default 10"
        smallint cost "default 0"
    }
    trees {
        bigint id PK
        bigint user_id FK
        bigint tree_type_id FK
        smallint level "default 0"
        smallint health "default 100"
        smallint progress "default 0"
        varchar status "ACTIVE, MATURE, HARVESTED, DEAD"
        timestamp last_care_at "nullable"
        timestamp next_care_at "nullable"
        timestamp last_deterioration_check_at "nullable"
        timestamp matured_at "nullable"
        timestamp harvested_at "nullable"
        timestamp died_at "nullable"
    }
    items {
        bigint id PK
        varchar slug UK
        varchar name
        text description "nullable"
        integer cost
        integer effect_duration_minutes "nullable"
    }
    inventories {
        bigint id PK
        bigint user_id FK
        bigint item_id FK
        integer quantity "default 0"
    }
    purchases {
        bigint id PK
        bigint user_id FK
        bigint item_id FK
        integer quantity "default 1"
        integer unit_price
        integer total
    }
    active_effects {
        bigint id PK
        bigint tree_id FK
        bigint item_id FK
        timestamp starts_at
        timestamp expires_at
        varchar status "ACTIVE, EXPIRED, CONSUMED"
    }
```

Todas las tablas tienen además `created_at` y `updated_at`.

### Decisiones de diseño

- **Estados como `varchar` y no como `enum` de PostgreSQL.** Los valores válidos se controlan en PHP con el enum `App\Enums\TreeStatus`, porque los `enum` nativos de PostgreSQL son difíciles de modificar después.
- **`next_care_at` guarda el fin del cooldown.** El servidor compara contra su propio reloj y nunca confía en la hora del cliente.
- **`inventories` tiene una restricción única `(user_id, item_id)`.** Cada usuario tiene una sola fila por ítem; comprar de nuevo aumenta `quantity`.
- **`purchases.unit_price` copia el precio del momento de la compra**, para que un cambio de precio posterior no altere el historial.
- **Claves foráneas:**
  - `cascadeOnDelete` hacia `users` y `trees`: al borrar un usuario o un árbol, se borran sus datos.
  - `restrictOnDelete` hacia los catálogos (`tree_types`, `items`): no se puede borrar un tipo o ítem que todavía esté en uso.
- **Índices** en las columnas que se filtran seguido: `trees(user_id, status)`, `trees(status)`, `purchases(user_id, created_at)`, `active_effects(tree_id, status)` y `active_effects(status, expires_at)`.

### Datos iniciales (seeders)

| Seeder | Qué carga |
| --- | --- |
| `UserSeeder` | Usuarios de prueba y 10 usuarios aleatorios |
| `TreeTypeSeeder` | Catálogo de semillas: básica y especial |
| `ItemSeeder` | Catálogo de la tienda: Acelerador y Riego automático |
| `DatabaseSeeder` | Ejecuta los anteriores y crea la cuenta demo con 2 árboles |

**Catálogo de semillas**

| Slug | Cuidados por nivel | Recompensa al cosechar | Costo |
| --- | --- | --- | --- |
| `semilla-basica` | 5 | 10 monedas | 0 |
| `semilla-especial` | 3 | 7 monedas | 15 |

**Catálogo de ítems**

| Slug | Costo | Duración del efecto | Efecto |
| --- | --- | --- | --- |
| `acelerador` | 5 | 120 min | Reduce a 30 minutos el cooldown de los cuidados manuales |
| `riego-automatico` | 8 | 360 min | Aplica cuidados automáticos |

---

## Cuentas de prueba

Disponibles después de `php artisan migrate:fresh --seed`:

| Correo | Contraseña | Uso |
| --- | --- | --- |
| `demo@greencycle.test` | `password` | Cuenta de la demostración; tiene 2 árboles |
| `admin@greencycles.com` | `password123` | Usuario de prueba |
| `pedro@greencycles.com` | `password123` | Usuario de prueba |

La cuenta demo tiene:
- **Árbol #1**: recién plantado (nivel 0, progreso 0).
- **Árbol #2**: nivel 9 y progreso 4. Un solo cuidado lo lleva a `MATURE`, ideal para mostrar la transición.

> Los IDs #1 y #2 corresponden a una base recién creada con `migrate:fresh --seed`.

---

## Endpoints

Todas las rutas llevan el prefijo `/api`. Las rutas protegidas requieren estos headers:

```http
Authorization: Bearer <token>
Accept: application/json
```

| Método | Ruta | Auth | Descripción |
| --- | --- | --- | --- |
| `POST` | `/api/register` | No | Registra un usuario y devuelve su token |
| `POST` | `/api/login` | No | Inicia sesión y devuelve un token |
| `POST` | `/api/logout` | Sí | Revoca el token actual |
| `GET` | `/api/me` | Sí | Datos del usuario autenticado |
| `POST` | `/api/trees/{tree}/care` | Sí | Aplica un cuidado a un árbol propio |

`register` y `login` tienen un límite de **5 intentos por minuto por IP** (`throttle:5,1`).

### `POST /api/register`

```json
{
  "name": "Ana Rojas",
  "email": "ana@correo.com",
  "password": "secreto123",
  "password_confirmation": "secreto123"
}
```

**201 Created**

```json
{
  "user": { "id": 13, "name": "Ana Rojas", "email": "ana@correo.com", "green_coins": 100 },
  "token": "1|xxxxxxxxxxxxxxxx"
}
```

### `POST /api/login`

```json
{
  "email": "demo@greencycle.test",
  "password": "password"
}
```

**200 OK**: misma estructura que `register`. Con credenciales incorrectas responde **422** con el error en `email`.

### `POST /api/trees/{tree}/care`

No lleva cuerpo. El servidor decide todo el resultado.

**200 OK**

```json
{
  "message": "Cuidado aplicado con éxito",
  "tree": {
    "id": 1,
    "level": 0,
    "health": 100,
    "progress": 1,
    "status": "ACTIVE",
    "can_care": false,
    "last_care_at": "2026-11-01T18:00:00Z",
    "next_care_at": "2026-11-01T19:00:00Z",
    "matured_at": null,
    "tree_type": { "id": 1, "name": "Semilla básica", "slug": "semilla-basica", "cares_per_level": 5 },
    "created_at": "2026-11-01T17:00:00Z",
    "updated_at": "2026-11-01T18:00:00Z"
  }
}
```

`can_care` lo calcula el servidor, así el frontend sabe si habilitar el botón "Cuidar" sin repetir las reglas.

**Respuestas de error**

| Código | Cuándo | Cuerpo |
| --- | --- | --- |
| `401 Unauthorized` | No se envió un token válido | `{"message": "Unauthenticated."}` |
| `403 Forbidden` | El árbol pertenece a otro usuario | `{"message": "This action is unauthorized."}` |
| `404 Not Found` | El árbol no existe | `{"message": "..."}` |
| `409 Conflict` | El árbol está `DEAD`, `MATURE` o `HARVESTED` | ver abajo |
| `409 Conflict` | El cooldown todavía no termina | ver abajo |

Estado que no admite cuidados:

```json
{
  "error": "El árbol no puede recibir cuidados en su estado actual.",
  "code": "TREE_NOT_CAREABLE",
  "status": "DEAD"
}
```

Cooldown activo:

```json
{
  "error": "El árbol se encuentra en período de enfriamiento.",
  "code": "TREE_ON_COOLDOWN",
  "next_care_at": "2026-11-01T19:00:00Z"
}
```

El campo `code` es estable: el frontend debe usarlo para decidir qué mostrar, no el texto de `error`.

---

## Reglas de negocio

### Cuidado de árboles

Las validaciones se aplican en este orden. Si una falla, el árbol **no se modifica**.

1. **Propiedad**: solo el dueño puede cuidar su árbol (`TreePolicy`) → si no, **403**.
2. **Estado**: solo un árbol `ACTIVE` admite cuidados → si no, **409** `TREE_NOT_CAREABLE`.
3. **Cooldown**: debe haber pasado `next_care_at` según el reloj del servidor → si no, **409** `TREE_ON_COOLDOWN`.
4. **Aplicación del cuidado**:
   - `progress` + 1
   - `health` + 20, con un máximo de 100
   - Al alcanzar `cares_per_level` del tipo de árbol: `level` + 1 y `progress` vuelve a 0
   - Al llegar al nivel 10: el estado cambia a `MATURE` y se registra `matured_at`
   - `next_care_at` = ahora + `GREENCYCLE_CARE_COOLDOWN_MINUTES`

### Máquina de estados

```mermaid
stateDiagram-v2
    [*] --> ACTIVE : plantar
    ACTIVE --> ACTIVE : cuidar (nivel < 10)
    ACTIVE --> MATURE : cuidar al llegar al nivel 10
    ACTIVE --> DEAD : salud llega a 0
    MATURE --> HARVESTED : cosechar (Sprint 3)
    DEAD --> [*]
    HARVESTED --> [*]
```

### Seguridad

- El cliente **nunca** define nivel, salud, progreso, estado, fechas ni precios. Esos campos no están en `$fillable` y solo los escribe la lógica del servidor con `forceFill()`.
- El cuidado corre dentro de una transacción con `lockForUpdate()`. Si llegan dos peticiones al mismo tiempo (doble clic), la segunda espera y encuentra el cooldown activo.
- Las respuestas de `/api/*` siempre se devuelven en JSON, incluidos los errores 403 y 404 (`bootstrap/app.php`).

---

## Pruebas automatizadas

Las pruebas usan SQLite en memoria (`phpunit.xml`), así que **no tocan la base de datos de Neon**.

```bash
# Toda la suite
php artisan test

# Solo las pruebas de cuidado
php artisan test --filter=TreeCareTest
```

Casos cubiertos en `tests/Feature/TreeCareTest.php`:

| Prueba | Verifica |
| --- | --- |
| Cuidado exitoso | +1 progreso, +20 salud y `next_care_at` correcto |
| Cooldown | Un segundo cuidado inmediato da 409 y no cambia el progreso |
| Subida de nivel | 5 cuidados válidos suben 1 nivel y reinician el progreso |
| Madurez | Nivel 9 con progreso 4 + un cuidado = nivel 10 y `MATURE` |
| Acceso cruzado | Cuidar el árbol de otro usuario da 403 y no lo modifica |
| Árbol muerto | Un árbol `DEAD` da 409 |
| Árbol maduro | Un árbol `MATURE` da 409 |
| Tope de salud | La salud nunca pasa de 100 |
| Sin autenticación | Sin token da 401 |

Las pruebas congelan el reloj (`freezeSecond`) y lo adelantan (`travel`) para probar el cooldown sin esperar tiempo real.

---

## Estructura del proyecto

```
app/
├── Enums/TreeStatus.php                 # Estados del árbol y cuáles admiten cuidados
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php           # register, login, logout, me
│   │   └── TreeCareController.php       # POST /api/trees/{tree}/care
│   ├── Requests/                        # LoginRequest, RegisterRequest
│   └── Resources/TreeResource.php       # Contrato JSON de un árbol
├── Models/                              # User, Tree, TreeType, Item, Inventory, Purchase, ActiveEffect
└── Policies/TreePolicy.php              # Solo el dueño puede ver o cuidar su árbol
config/greencycle.php                    # Reglas configurables del juego
database/
├── factories/                           # Datos falsos para pruebas
├── migrations/
└── seeders/
routes/api.php
tests/Feature/TreeCareTest.php
```

---

## Flujo de trabajo con Git

| Rama | Propósito |
| --- | --- |
| `main` | Versión estable de cada entrega |
| `develop` | Integración del trabajo del sprint |
| `sprintN/<tema>` | Una funcionalidad concreta, por ejemplo `sprint2/tree-care` |

1. Crear la rama desde `develop`: `git checkout -b sprint2/<tema> develop`
2. Hacer commits pequeños con prefijos: `feat:`, `fix:`, `test:`, `refactor:`, `chore:`
3. Abrir un Pull Request hacia `develop`
4. Al cerrar el sprint, PR de `develop` → `main` y una etiqueta de la entrega:

```bash
git tag -a v0.2.0-sprint2 -m "Entrega Sprint 2"
git push origin v0.2.0-sprint2
```

---

## Estado por sprint

| Sprint | Alcance | Estado |
| --- | --- | --- |
| Sprint 1 | Autenticación con Sanctum, modelo de datos |  Autenticación lista · Endpoints de árboles (`index`, `show`, `store`) pendientes |
| Sprint 2 | Capa de datos, endpoint de cuidado, pruebas |  Completo · Proceso programado de deterioro pendiente |
| Sprint 3 | Cosecha, monedas, tienda, inventario y efectos |  Migraciones listas, endpoints pendientes |

---

## Equipo

| Integrante | Carné | Responsabilidades |
| --- | --- | --- |
| `Hugo Carranza` | `C31676` | Capa de datos, endpoint de cuidado, pruebas |
| `Eduardo Arias` | `C20762` | Autenticación con Sanctum, usuarios |

Profesor: `Jorge Miranda` · Curso TM-4100 · `II - 2026`