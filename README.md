# Sistema Gestor de Proyectos - API

## Descripción
API REST para la gestión de proyectos. Permite crear, consultar, actualizar y eliminar proyectos, así como gestionar usuarios y roles.

## Base URL
```
http://localhost:8000/api
```

## Endpoints

### Usuarios
* **POST /register**  
  Registro de un nuevo usuario  
  **Ejemplo de Body JSON:**
  ```json
  {
    "nombre": "Kathy Flores",
    "email": "kathy@example.com",
    "password": "12345678",
    "password_confirmation": "12345678",
    "role_id": 2
  }
  ```

* **POST /login**
  
  Login de usuario

  **Ejemplo de Body JSON:**

    ```json
    {
      "email": "kathy@example.com",
      "password": "12345678"
    }
    ```

### Proyectos

* **GET /projects**

  Listar todos los proyectos

* **POST /projects**

  Crear un proyecto

  **Ejemplo de Body JSON:**

  ```json
  {
    "nombre": "Portal de Clientes",
    "descripcion": "Plataforma web para que los clientes consulten sus pedidos y proyectos.",
    "tipo": "software",
    "categoria": "Atención al Cliente",
    "estado": "Planificado",
    "fecha_inicio": "2025-09-05",
    "fecha_fin_estimada": "2025-11-30",
    "fecha_fin_real": null,
    "porcentaje_avance": 0
  }
  ```

* **PUT /projects/{id}**

  Editar un proyecto

* **DELETE /projects/{id}**
  
  Eliminar un proyecto

---

## Autenticación

Se utiliza **Laravel Passport** para tokens de acceso.
Enviar el token en el header:

```
Authorization: Bearer <access_token>
```
