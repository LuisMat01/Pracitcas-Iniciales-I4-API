# Social USAC — API Backend

Backend desarrollado con **NestJS + Prisma + MySQL**. Documentación para el equipo de frontend.

---

## Requisitos previos

- Docker Desktop instalado y corriendo
- Node.js 18+

## Levantar el proyecto

```bash
# 1. Levantar la base de datos
docker-compose up -d

# 2. Instalar dependencias
npm install

# 3. Correr migraciones
npx prisma migrate dev

# 4. Iniciar el servidor
npm run start:dev
```

El servidor corre en: `http://localhost:3000`

---

## Configuración del Frontend

### Regla de oro

Cada request que haga el frontend **debe incluir `credentials: 'include'`** (fetch) o **`withCredentials: true`** (axios). Sin esto las cookies no se mandan y el usuario aparecerá como no autenticado.

### Configuración con Axios (recomendado)

Crea un archivo `api.ts` o `api.js` y configúralo una sola vez:

```ts
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:3000',
  withCredentials: true, // SIEMPRE incluir esto
});

export default api;
```

Luego úsalo en toda la app:

```ts
// Login
await api.post('/auth/login', { email, password });

// Obtener publicaciones
await api.get('/posts');
```

### Configuración con Fetch

Si usas fetch nativo, agrega `credentials: 'include'` en cada request:

```ts
fetch('http://localhost:3000/auth/login', {
  method: 'POST',
  credentials: 'include',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email, password }),
});
```

---

## Cómo funciona la autenticación

El backend usa **HTTP-only cookies**. Esto significa que:

- El frontend **NO** necesita guardar tokens en localStorage ni en memoria
- El frontend **NO** necesita leer ni manejar tokens
- El frontend **NO** necesita agregar headers de autorización
- Las cookies se mandan y reciben **automáticamente** por el navegador

El único requisito es `credentials: 'include'` / `withCredentials: true`.

### Flujo de autenticación

```
1. Usuario hace login
   → Backend crea las cookies automáticamente en el navegador
   → accessToken  (dura 15 minutos)
   → refreshToken (dura 7 días)

2. Usuario hace requests
   → El navegador manda las cookies solo en cada request
   → El backend valida el accessToken

3. accessToken expira (15 min)
   → El frontend llama a POST /auth/refresh
   → El backend genera nuevas cookies automáticamente

4. Usuario hace logout
   → El frontend llama a POST /auth/logout
   → El backend borra las cookies
```

### Manejo del token expirado (401)

Cuando el accessToken expira el backend devuelve un error `401`. El frontend debe interceptarlo y llamar al endpoint de refresh:

```ts
// Interceptor de Axios para manejar tokens expirados
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      try {
        // Intenta renovar el token
        await api.post('/auth/refresh');
        // Reintenta el request original
        return api(error.config);
      } catch {
        // Si el refresh también falla, manda al login
        window.location.href = '/login';
      }
    }
    return Promise.reject(error);
  }
);
```

---

## Endpoints

### Autenticación

#### POST `/auth/register`
Registrar un nuevo usuario.

**Body:**
```json
{
  "registroAcademico": "202012345",
  "nombres": "Juan",
  "apellidos": "Pérez",
  "email": "juan@usac.edu",
  "password": "mipassword123"
}
```

**Respuesta exitosa (200):**
```json
{ "message": "Autenticación exitosa" }
```

---

#### POST `/auth/login`
Iniciar sesión.

**Body:**
```json
{
  "email": "juan@usac.edu",
  "password": "mipassword123"
}
```

**Respuesta exitosa (200):**
```json
{ "message": "Autenticación exitosa" }
```

---

#### POST `/auth/logout`
Cerrar sesión. Requiere estar autenticado.

**Body:** ninguno

**Respuesta exitosa (200):**
```json
{ "message": "Sesión cerrada correctamente" }
```

---

#### POST `/auth/refresh`
Renovar el accessToken cuando expira.

**Body:** ninguno

**Respuesta exitosa (200):**
```json
{ "message": "Autenticación exitosa" }
```

---

#### POST `/auth/reset-password`
Restablecer contraseña olvidada.

**Body:**
```json
{
  "registroAcademico": "202012345",
  "email": "juan@usac.edu",
  "newPassword": "nuevapassword123"
}
```

**Respuesta exitosa (200):**
```json
{ "message": "Contraseña actualizada correctamente" }
```

---

### Usuarios

> Todos los endpoints de usuarios requieren estar autenticado.

#### GET `/users/me`
Ver mi perfil.

**Respuesta exitosa (200):**
```json
{
  "id": 1,
  "registroAcademico": "202012345",
  "nombres": "Juan",
  "apellidos": "Pérez",
  "email": "juan@usac.edu",
  "createdAt": "2024-01-01T00:00:00.000Z",
  "approvedCourses": [
    {
      "course": {
        "id": 1,
        "nombre": "Matemática 1",
        "creditos": 5
      }
    }
  ]
}
```

---

#### PUT `/users/me`
Editar mi perfil. Todos los campos son opcionales. No se puede cambiar el `registroAcademico`.

**Body:**
```json
{
  "nombres": "Juan Carlos",
  "apellidos": "Pérez López",
  "email": "juancarlos@usac.edu",
  "password": "nuevapassword123"
}
```

---

#### GET `/users/me/courses`
Ver mis cursos aprobados con total de créditos.

**Respuesta exitosa (200):**
```json
{
  "usuario": "Juan Pérez",
  "totalCreditos": 15,
  "cursos": [
    { "id": 1, "nombre": "Matemática 1", "creditos": 5 },
    { "id": 2, "nombre": "Física 1", "creditos": 5 },
    { "id": 3, "nombre": "Programación 1", "creditos": 5 }
  ]
}
```

---

#### POST `/users/me/courses/:courseId`
Agregar un curso aprobado a mi perfil.

**Ejemplo:** `POST /users/me/courses/1`

---

#### DELETE `/users/me/courses/:courseId`
Eliminar un curso aprobado de mi perfil.

**Ejemplo:** `DELETE /users/me/courses/1`

---

#### GET `/users/:registroAcademico`
Ver el perfil de otro usuario.

**Ejemplo:** `GET /users/202012345`

---

#### GET `/users/:registroAcademico/courses`
Ver los cursos aprobados de otro usuario.

**Ejemplo:** `GET /users/202012345/courses`

---

### Publicaciones

> Todos los endpoints de publicaciones requieren estar autenticado.

#### GET `/posts`
Obtener todas las publicaciones ordenadas de más reciente a más antigua. Acepta filtros opcionales por query params.

| Query param | Descripción | Ejemplo |
|-------------|-------------|---------|
| `courseId` | Filtrar por ID de curso | `?courseId=1` |
| `professorId` | Filtrar por ID de catedrático | `?professorId=2` |
| `courseName` | Buscar por nombre de curso | `?courseName=matematica` |
| `professorName` | Buscar por nombre de catedrático | `?professorName=juan` |

**Ejemplos:**
```
GET /posts
GET /posts?courseId=1
GET /posts?professorName=garcia
GET /posts?courseName=fisica
```

**Respuesta exitosa (200):**
```json
[
  {
    "id": 1,
    "mensaje": "Este curso está muy bueno",
    "createdAt": "2024-01-15T10:30:00.000Z",
    "user": {
      "id": 1,
      "nombres": "Juan",
      "apellidos": "Pérez",
      "registroAcademico": "202012345"
    },
    "course": {
      "id": 1,
      "nombre": "Matemática 1"
    },
    "professor": null,
    "comments": [
      {
        "id": 1,
        "mensaje": "Totalmente de acuerdo",
        "createdAt": "2024-01-15T11:00:00.000Z",
        "user": {
          "id": 2,
          "nombres": "María",
          "apellidos": "García",
          "registroAcademico": "202054321"
        }
      }
    ]
  }
]
```

---

#### GET `/posts/:id`
Ver una publicación específica con sus comentarios.

**Ejemplo:** `GET /posts/1`

---

#### POST `/posts`
Crear una publicación. Debe incluir **solo uno** de los dos: `courseId` o `professorId`.

**Body (publicación sobre un curso):**
```json
{
  "mensaje": "Este curso está muy bien explicado",
  "courseId": 1
}
```

**Body (publicación sobre un catedrático):**
```json
{
  "mensaje": "El catedrático explica muy bien",
  "professorId": 2
}
```

---

### Comentarios

> Requieren estar autenticado.

#### POST `/posts/:postId/comments`
Agregar un comentario a una publicación.

**Ejemplo:** `POST /posts/1/comments`

**Body:**
```json
{
  "mensaje": "Estoy de acuerdo con esta publicación"
}
```

---

### Cursos

> Requieren estar autenticado.

#### GET `/courses`
Listar todos los cursos ordenados alfabéticamente.

**Respuesta exitosa (200):**
```json
[
  { "id": 1, "nombre": "Física 1", "creditos": 5 },
  { "id": 2, "nombre": "Matemática 1", "creditos": 5 }
]
```

---

#### GET `/courses/:id`
Ver un curso con todas sus publicaciones.

---

#### POST `/courses`
Crear un curso.

**Body:**
```json
{
  "nombre": "Matemática 1",
  "creditos": 5
}
```

---

#### PUT `/courses/:id`
Editar un curso.

**Body:**
```json
{
  "nombre": "Matemática Básica",
  "creditos": 6
}
```

---

#### DELETE `/courses/:id`
Eliminar un curso.

---

### Catedráticos

> Requieren estar autenticado.

#### GET `/professors`
Listar todos los catedráticos ordenados por apellido.

**Respuesta exitosa (200):**
```json
[
  { "id": 1, "nombres": "Carlos", "apellidos": "García" },
  { "id": 2, "nombres": "María", "apellidos": "López" }
]
```

---

#### GET `/professors/:id`
Ver un catedrático con todas sus publicaciones.

---

#### POST `/professors`
Crear un catedrático.

**Body:**
```json
{
  "nombres": "Carlos",
  "apellidos": "García"
}
```

---

#### PUT `/professors/:id`
Editar un catedrático.

---

#### DELETE `/professors/:id`
Eliminar un catedrático.

---

## Manejo de errores

El backend devuelve errores en este formato:

```json
{
  "statusCode": 400,
  "message": "El correo o registro académico ya está en uso",
  "error": "Bad Request"
}
```

| Código | Significado | Qué hacer en el frontend |
|--------|-------------|--------------------------|
| `400` | Datos inválidos o faltantes | Mostrar el mensaje de error al usuario |
| `401` | No autenticado o token expirado | Llamar a `/auth/refresh` o redirigir al login |
| `403` | Sin permisos | Mostrar mensaje de acceso denegado |
| `404` | Recurso no encontrado | Mostrar mensaje de no encontrado |
| `500` | Error del servidor | Mostrar mensaje genérico de error |

### Errores de validación (400)

Cuando se mandan datos incorrectos, el mensaje puede ser un array:

```json
{
  "statusCode": 400,
  "message": [
    "El correo no es válido",
    "La contraseña debe tener mínimo 8 caracteres"
  ],
  "error": "Bad Request"
}
```

---

## Estructura del proyecto

```
src/
├── auth/
│   ├── dto/
│   │   ├── register.dto.ts
│   │   ├── login.dto.ts
│   │   └── reset-password.dto.ts
│   ├── auth.module.ts
│   ├── auth.service.ts
│   ├── auth.controller.ts
│   ├── jwt.strategy.ts
│   ├── jwt-refresh.strategy.ts
│   ├── jwt-auth.guard.ts
│   └── jwt-refresh.guard.ts
├── users/
│   ├── dto/
│   │   └── update-user.dto.ts
│   ├── users.module.ts
│   ├── users.service.ts
│   └── users.controller.ts
├── posts/
│   ├── dto/
│   │   └── create-post.dto.ts
│   ├── posts.module.ts
│   ├── posts.service.ts
│   └── posts.controller.ts
├── comments/
│   ├── dto/
│   │   └── create-comment.dto.ts
│   ├── comments.module.ts
│   ├── comments.service.ts
│   └── comments.controller.ts
├── courses/
│   ├── dto/
│   │   ├── create-course.dto.ts
│   │   └── update-course.dto.ts
│   ├── courses.module.ts
│   ├── courses.service.ts
│   └── courses.controller.ts
├── professors/
│   ├── dto/
│   │   ├── create-professor.dto.ts
│   │   └── update-professor.dto.ts
│   ├── professors.module.ts
│   ├── professors.service.ts
│   └── professors.controller.ts
└── prisma/
    ├── prisma.module.ts
    └── prisma.service.ts
```

---

## Variables de entorno

Crea un archivo `.env` en la raíz del proyecto:

```env
# Base de datos
DATABASE_URL="mysql://admin:admin1234@localhost:3306/usac_db"

# JWT
JWT_SECRET=un_secreto_muy_seguro_aqui
JWT_REFRESH_SECRET=otro_secreto_muy_seguro_aqui

# App
FRONTEND_URL=http://localhost:6000
NODE_ENV=development
```