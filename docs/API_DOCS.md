# Documentación API - Auth

Base URL: `http://localhost:8000/api`

Las rutas protegidas requieren enviar el token en el header:
```
Authorization: Bearer {token}
```

---

## 1. Crear usuario
**Solo el admin puede usar este endpoint.**

```
POST /register
Authorization: Bearer {token}
```

**Body:**
```json
{
    "name": "Juan",
    "lastname": "Pérez",
    "email": "juan@email.com",
    "phone": "1234567890",
    "role": "barber"
}
```
> `role` acepta: `admin`, `barber`, `receptionist`, `client`.
> `lastname` y `phone` son opcionales

**Respuesta exitosa — 201:**
```json
{
    "success": true
    "message": "usuario creado con contraseña por defecto 12345678.",
    "data": {
        "id": 1,
        "name": "Juan",
        "lastname": "Pérez",
        "email": "juan@email.com",
        "phone": "1234567890",
        "role": "barber"
    }
}
```

**Errores posibles:**
| Código | Motivo                                          |Respuesta  |
|--------|-------------------------------------------------|-----------|
| 401    | No enviaste el token                            | Fig. 1    |
| 403    | No eres admin                                   | Fig. 2    |
| 422    | Falta un campo obligatorio o el email ya existe | Fig. 3, 4 |

Fig. 1

```json
{
  "message": "Unauthenticated."
}
```

Fig. 2

```json
{
  "success": false,
  "message": "No tienes permiso para crear usuarios."
}
```

Fig. 3

```json
{
  "errors": {
    "name": [
      "The name field is required."
    ],
    "email": [
      "The email field is required."
    ],
    "role": [
      "The role field is required."
    ]
  }
}
```

Fig. 4

```json
{
  "errors": {
    "email": [
      "The email has already been taken."
    ]
  }
}
```

---

## 2. Login

```
POST /login
```

**Body:**
```json
{
    "email": "juan@email.com",
    "password": "12345678"
}
```

**Respuesta exitosa — 200:**
```json
{
    "success": true
    "message": "Inicio de sesión exitoso.",
    "access_token": "1|abc123...",
    "token_type": "Bearer",
    "must_change_password": true,
    "user": {
        "id": 1,
        "name": "Juan",
        "lastname": "Pérez",
        "email": "juan@email.com",
        "role": "barber"
    }
}
```
> [!IMPORTANT]
>Si `must_change_password` es `true`, redirige al usuario a la pantalla de cambio de contraseña antes de dejarlo usar la app. Guarda el `access_token` para usarlo en las demás peticiones.

**Errores posibles:**
| Código | Motivo                                          | Respuesta |
|--------|-------------------------------------------------|-----------|
| 401 | Email o contraseña incorrectos                     | Fig. 1    |
| 422 | Falta email o contraseña en el body                | Fig. 2    |
| 429 | Demasiados intentos fallidos, espera unos segundos | Fig. 3    |

Fig. 1

```json
{
  "success": false,
  "message": "Credenciales inválidas."
}
```

Fig. 2

```json
{
  "errors": {
    "email": [
      "The email field is required."
    ],
    "password": [
      "The password field is required."
    ]
  }
}
```

Fig. 3

```json
{
  "success": false,
  "message": "Demasiados intentos de inicio de sesión. Inténtalo de nuevo en 60 segundos."
}
```

---

## 3. Cambiar contraseña

```
POST /change-password
Authorization: Bearer {token}
```

**Body:**
```json
{
    "current_password": "12345678",
    "new_password": "NuevoPass1!",
    "new_password_confirmation": "NuevoPass1!"
}
```

> `new_password` debe tener mínimo 8 caracteres, una mayúscula, un número y un carácter especial (`@$!%*#?&`).
> `new_password_confirmation` debe ser idéntico a `new_password`.

**Respuesta exitosa — 200:**
```json
{
    "message": "Contraseña actualizada correctamente. Por favor, inicie sesión de nuevo."
}
```
> [!IMPORTANT]
>Cuando recibas esta respuesta, elimina el token guardado y manda al usuario al login. El token anterior ya no sirve.

**Errores posibles:**
| Código | Motivo                                                                 | Respuesta |
|--------|------------------------------------------------------------------------|-----------|
| 401    | No enviaste el token                                                   | Fig. 1    |
| 422    | La contraseña actual es incorrecta o la nueva no cumple los requisitos | Fig. 2    |

Fig. 1

```json
{
  "message": "Unauthenticated."
}
```

Fig. 2

```json
{
  "errors": {
    "new_password": [
      "The new password field confirmation does not match.",
      "The new password field format is invalid."
    ]
  }
}
```
---

## 4. Logout

```
POST /logout
Authorization: Bearer {token}
```

**Respuesta exitosa — 200:**
```json
{
    "message": "Cierre de sesión exitoso."
}
```
> [!IMPORTANT]
>Cuando recibas esta respuesta, elimina el token guardado y redirige al login.

**Errores posibles:**
| Código | Motivo                                    | Respuesta |
|--------|-------------------------------------------|-----------|
| 401    | No enviaste el token o ya estaba expirado | Fig. 1    |

Fig. 1

```json
{
  "message": "Unauthenticated."
}
```

---

## Flujo general

```
1. Login → guardar access_token
2. Si must_change_password === true → ir a cambiar contraseña
3. Cambiar contraseña → eliminar token → ir al login
4. Login de nuevo con la nueva contraseña → guardar nuevo token
5. Usar la app con normalidad
6. Logout → eliminar token → ir al login
```