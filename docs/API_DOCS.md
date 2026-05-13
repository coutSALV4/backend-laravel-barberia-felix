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
    "message": "usuario creado con contraseña por defecto \"12345678\".",
    "data": {
        "id": 1,
        "name": "Juan",
        "lastname": "Pérez",
        "email": "juan@email.com",
        "phone": "1234567890",
        "role": "barber",
        "created_at": "2024-01-01T00:00:00Z"
    }
}
```

**Errores posibles:**
| Código | Motivo |
|--------|--------|
| 401 | No enviaste el token o no eres admin |
| 422 | Falta un campo obligatorio o el email ya existe |

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
    "message": "Inicio de sesión exitoso.",
    "access_token": "1|abc123...",
    "token_type": "Bearer",
    "must_change_password": true,
    "user": {
        "id": 1,
        "name": "Juan",
        "email": "juan@email.com",
        "role": "barber"
    }
}
```

> ⚠️ Si `must_change_password` es `true`, redirige al usuario a la pantalla de cambio de contraseña antes de dejarlo usar la app. Guarda el `access_token` para usarlo en las demás peticiones.

**Errores posibles:**
| Código | Motivo |
|--------|--------|
| 401 | Email o contraseña incorrectos |
| 422 | Falta email o contraseña en el body |
| 429 | Demasiados intentos fallidos, espera unos segundos |

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

> ⚠️ Cuando recibas esta respuesta, elimina el token guardado y manda al usuario al login. El token anterior ya no sirve.

**Errores posibles:**
| Código | Motivo |
|--------|--------|
| 401 | No enviaste el token |
| 422 | La contraseña actual es incorrecta o la nueva no cumple los requisitos |

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

> ⚠️ Cuando recibas esta respuesta, elimina el token guardado y redirige al login.

**Errores posibles:**
| Código | Motivo |
|--------|--------|
| 401 | No enviaste el token o ya estaba expirado |

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