# Documentación API - Barbería Félix

Las rutas protegidas requieren enviar el token en el header:
```
Authorization: Bearer {token}
```

---

## Índice

- [Auth](#auth)
- [Usuarios](#usuarios)
- [Servicios](#servicios)
- [Citas](#citas)
- [Pagos](#pagos)
- [Gastos](#gastos)
- [Reporte Financiero](#reporte-financiero)

---

## Auth

### 1. Crear usuario

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
> `lastname` y `phone` son opcionales.

**Respuesta exitosa — 201:**
```json
{
    "success": true,
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
| Código | Motivo                                          |
|--------|-------------------------------------------------|
| 401    | No enviaste el token                            |
| 403    | No eres admin                                   |
| 422    | Falta un campo obligatorio o el email ya existe |

---

### 2. Login

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
    "success": true,
    "message": "Inicio de sesión exitoso.",
    "access_token": "1|abc123...",
    "token_type": "Bearer",
    "must_change_password": true,
    "user": {
        "id": 1,
        "name": "Juan",
        "lastname": "Pérez",
        "email": "juan@email.com",
        "phone": "1234567890",
        "role": "barber"
    }
}
```

> [!IMPORTANT]
> Si `must_change_password` es `true`, redirige al usuario a la pantalla de cambio de contraseña antes de dejarlo usar la app. Guarda el `access_token` para usarlo en las demás peticiones.

**Errores posibles:**
| Código | Motivo                                             |
|--------|----------------------------------------------------|
| 401    | Email o contraseña incorrectos                     |
| 422    | Falta email o contraseña en el body                |
| 429    | Demasiados intentos fallidos, espera unos segundos |

---

### 3. Cambiar contraseña

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
    "success": true,
    "message": "Contraseña actualizada correctamente. Por favor, inicie sesión de nuevo.",
    "data": null
}
```

> [!IMPORTANT]
> Cuando recibas esta respuesta, elimina el token guardado y manda al usuario al login. El token anterior ya no sirve.

**Errores posibles:**
| Código | Motivo                                                                 |
|--------|------------------------------------------------------------------------|
| 401    | No enviaste el token o la contraseña actual es incorrecta              |
| 422    | La nueva contraseña no cumple los requisitos o la confirmación no coincide |

---

### 4. Logout

```
POST /logout
Authorization: Bearer {token}
```

**Respuesta exitosa — 200:**
```json
{
    "success": true,
    "message": "Cierre de sesión exitoso.",
    "data": null
}
```

> [!IMPORTANT]
> Cuando recibas esta respuesta, elimina el token guardado y redirige al login.

**Errores posibles:**
| Código | Motivo                                    |
|--------|-------------------------------------------|
| 401    | No enviaste el token o ya estaba expirado |

---

### Flujo general

```
1. Login → guardar access_token
2. Si must_change_password === true → ir a cambiar contraseña
3. Cambiar contraseña → eliminar token → ir al login
4. Login de nuevo con la nueva contraseña → guardar nuevo token
5. Usar la app con normalidad
6. Logout → eliminar token → ir al login
```

---

## Usuarios

### 1. Listar usuarios

**Solo admin y recepcionistas pueden usar este endpoint.**

```
GET /users
Authorization: Bearer {token}
```

**Query params opcionales:**
| Parámetro | Tipo   | Descripción                                          |
|-----------|--------|------------------------------------------------------|
| `role`    | string | Filtra por rol: `admin`, `barber`, `receptionist`, `client` |

**Respuesta exitosa — 200:**
```json
{
    "success": true,
    "message": "Usuarios obtenidos exitosamente",
    "data": {
        "data": [
            {
                "id": 1,
                "name": "Juan",
                "lastname": "Pérez",
                "email": "juan@email.com",
                "phone": "1234567890",
                "role": "barber"
            }
        ],
        "current_page": 1,
        "last_page": 1,
        "per_page": 10,
        "total": 1
    }
}
```

**Errores posibles:**
| Código | Motivo               |
|--------|----------------------|
| 401    | No enviaste el token |

---

### 2. Actualizar usuario

**Cada usuario puede editarse a sí mismo. Los admins pueden editar a cualquiera.**

```
PUT /users/{id}
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

> `lastname` y `phone` son opcionales.
> `role` acepta: `admin`, `barber`, `receptionist`, `client`.

**Respuesta exitosa — 200:**
```json
{
    "success": true,
    "message": "Usuario actualizado exitosamente",
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
| Código | Motivo                                          |
|--------|-------------------------------------------------|
| 401    | No enviaste el token                            |
| 403    | No tienes permiso para editar ese usuario       |
| 422    | Falta un campo obligatorio o el email ya existe |
| 404    | Usuario no encontrado                           |

---

### 3. Eliminar usuario

**Solo el admin puede usar este endpoint. No puede eliminarse a sí mismo.**

```
DELETE /users/{id}
Authorization: Bearer {token}
```

**Respuesta exitosa — 200:**
```json
{
    "success": true,
    "message": "Usuario eliminado exitosamente",
    "data": null
}
```

**Errores posibles:**
| Código | Motivo                                    |
|--------|-------------------------------------------|
| 401    | No enviaste el token                      |
| 403    | No eres admin                             |
| 404    | Usuario no encontrado                     |

---

## Servicios

### 1. Listar servicios

**Cualquier usuario autenticado puede ver los servicios activos.**

```
GET /services
Authorization: Bearer {token}
```

**Respuesta exitosa — 200:**
```json
{
    "success": true,
    "message": "Servicios obtenidos exitosamente",
    "data": [
        {
            "id": 1,
            "name": "Corte clásico",
            "description": "Corte de cabello estilo clásico.",
            "price": 120.00,
            "duration_min": 30,
            "active": true
        }
    ]
}
```

---

### 2. Crear servicio

**Solo el admin puede usar este endpoint.**

```
POST /services
Authorization: Bearer {token}
```

**Body:**
```json
{
    "name": "Corte clásico",
    "description": "Corte de cabello estilo clásico.",
    "price": 120.00,
    "duration_min": 30,
    "active": true
}
```

> `description` y `active` son opcionales. `active` es `true` por defecto.
> `duration_min` debe ser entre 1 y 480 minutos.
> `price` debe ser mayor a 0.

**Respuesta exitosa — 201:**
```json
{
    "success": true,
    "message": "Servicio creado exitosamente",
    "data": {
        "id": 1,
        "name": "Corte clásico",
        "description": "Corte de cabello estilo clásico.",
        "price": 120.00,
        "duration_min": 30,
        "active": true
    }
}
```

**Errores posibles:**
| Código | Motivo                         |
|--------|--------------------------------|
| 401    | No enviaste el token           |
| 403    | No eres admin                  |
| 422    | Falta un campo obligatorio     |

---

### 3. Actualizar servicio

**Solo el admin puede usar este endpoint.**

```
PUT /services/{id}
Authorization: Bearer {token}
```

**Body:** Mismos campos que en crear servicio.

**Respuesta exitosa — 200:**
```json
{
    "success": true,
    "message": "Servicio actualizado exitosamente",
    "data": {
        "id": 1,
        "name": "Corte clásico",
        "description": "Corte de cabello estilo clásico.",
        "price": 120.00,
        "duration_min": 30,
        "active": true
    }
}
```

**Errores posibles:**
| Código | Motivo                         |
|--------|--------------------------------|
| 401    | No enviaste el token           |
| 403    | No eres admin                  |
| 404    | Servicio no encontrado         |
| 422    | Falta un campo obligatorio     |

---

### 4. Eliminar servicio

**Solo el admin puede usar este endpoint. Realiza un soft delete.**

```
DELETE /services/{id}
Authorization: Bearer {token}
```

**Respuesta exitosa — 200:**
```json
{
    "success": true,
    "message": "Servicio eliminado exitosamente",
    "data": null
}
```

**Errores posibles:**
| Código | Motivo                 |
|--------|------------------------|
| 401    | No enviaste el token   |
| 403    | No eres admin          |
| 404    | Servicio no encontrado |

---

## Citas

### 1. Listar citas

**Todos los roles pueden listar citas. El sistema filtra según el rol del usuario.**

```
GET /appointments
Authorization: Bearer {token}
```

**Query params opcionales:**
| Parámetro   | Tipo   | Descripción                                                                 |
|-------------|--------|-----------------------------------------------------------------------------|
| `date`      | date   | Filtra por fecha exacta (formato `YYYY-MM-DD`)                              |
| `barber_id` | int    | Filtra por barbero                                                           |
| `status`    | string | Filtra por estado: `pending`, `confirmed`, `in_progress`, `completed`, `cancelled`, `no_show` |

**Respuesta exitosa — 200:**
```json
{
    "success": true,
    "message": "Citas obtenidas exitosamente",
    "data": {
        "data": [
            {
                "id": 1,
                "status": "pending",
                "appointment_date": "2026-06-01",
                "start_time": "10:00",
                "end_time": "10:30",
                "total_price": 120.00,
                "notes": "Sin notas",
                "client": {
                    "id": 3,
                    "name": "Carlos",
                    "lastname": "López",
                    "email": "carlos@email.com",
                    "phone": null,
                    "role": "client"
                },
                "barber": {
                    "id": 2,
                    "name": "Juan",
                    "lastname": "Pérez",
                    "email": "juan@email.com",
                    "phone": null,
                    "role": "barber"
                },
                "services": [
                    {
                        "id": 1,
                        "name": "Corte clásico",
                        "description": "Corte de cabello estilo clásico.",
                        "price": 120.00,
                        "duration_min": 30,
                        "active": true,
                        "price_at_time": 120.00
                    }
                ],
                "payment": null
            }
        ],
        "current_page": 1,
        "last_page": 1,
        "per_page": 10,
        "total": 1
    }
}
```

---

### 2. Crear cita

**Admin, recepcionistas y clientes pueden crear citas.**

```
POST /appointments
Authorization: Bearer {token}
```

**Body:**
```json
{
    "client_id": 3,
    "barber_id": 2,
    "service_ids": [1, 3],
    "appointment_date": "2026-06-01",
    "start_time": "10:00",
    "notes": "Sin notas"
}
```

> `notes` es opcional.
> `appointment_date` no puede ser una fecha pasada.
> `start_time` debe tener formato `HH:MM`.
> `service_ids` debe contener al menos un servicio existente.
> `client_id` y `barber_id` deben ser diferentes.
> El `end_time` se calcula automáticamente sumando la duración de todos los servicios.
> El sistema verifica que el barbero no tenga otra cita activa en ese horario.

**Respuesta exitosa — 201:**
```json
{
    "success": true,
    "message": "Cita creada exitosamente",
    "data": {
        "id": 1,
        "status": "pending",
        "appointment_date": "2026-06-01",
        "start_time": "10:00",
        "end_time": "10:50",
        "total_price": 200.00,
        "notes": "Sin notas",
        "client": { "..." },
        "barber": { "..." },
        "services": [ { "..." } ],
        "payment": null
    }
}
```

**Errores posibles:**
| Código | Motivo                                              |
|--------|-----------------------------------------------------|
| 401    | No enviaste el token                                |
| 409    | El barbero no está disponible en ese horario        |
| 422    | Falta un campo obligatorio o algún ID no existe     |

---

### 3. Actualizar estado de cita

**Admins y recepcionistas pueden cambiar cualquier estado. Los barberos solo pueden actualizar sus propias citas.**

```
PATCH /appointments/{id}/status
Authorization: Bearer {token}
```

**Body:**
```json
{
    "status": "confirmed"
}
```

> `status` acepta: `pending`, `confirmed`, `in_progress`, `completed`, `cancelled`, `no_show`.

**Respuesta exitosa — 200:**
```json
{
    "success": true,
    "message": "Estado de cita actualizado",
    "data": {
        "id": 1,
        "status": "confirmed",
        "..."
    }
}
```

**Errores posibles:**
| Código | Motivo                             |
|--------|------------------------------------|
| 401    | No enviaste el token               |
| 422    | El status enviado no es válido     |
| 404    | Cita no encontrada                 |

---

### 4. Cancelar cita

**Solo admins y recepcionistas pueden cancelar citas. Realiza un soft delete.**

```
DELETE /appointments/{id}
Authorization: Bearer {token}
```

**Respuesta exitosa — 200:**
```json
{
    "success": true,
    "message": "Cita cancelada exitosamente",
    "data": null
}
```

**Errores posibles:**
| Código | Motivo                 |
|--------|------------------------|
| 401    | No enviaste el token   |
| 403    | No tienes permiso      |
| 404    | Cita no encontrada     |

---

## Pagos

### 1. Listar pagos

**Cualquier usuario autenticado puede ver pagos.**

```
GET /payments
Authorization: Bearer {token}
```

**Query params opcionales:**
| Parámetro        | Tipo   | Descripción                                      |
|------------------|--------|--------------------------------------------------|
| `appointment_id` | int    | Filtra por cita                                   |
| `status`         | string | Filtra por estado: `pending`, `completed`, `refunded` |
| `method`         | string | Filtra por método: `cash`, `card`, `transfer`, `other` |

**Respuesta exitosa — 200:**
```json
{
    "success": true,
    "message": "Pagos obtenidos exitosamente",
    "data": {
        "data": [
            {
                "id": 1,
                "amount": 200.00,
                "method": "cash",
                "status": "completed",
                "reference": null,
                "paid_at": "2026-06-01 10:55:00",
                "received_by": {
                    "id": 4,
                    "name": "Ana",
                    "lastname": "García",
                    "email": "ana@email.com",
                    "phone": null,
                    "role": "receptionist"
                }
            }
        ],
        "current_page": 1,
        "last_page": 1,
        "per_page": 20,
        "total": 1
    }
}
```

---

### 2. Registrar pago

**Cualquier usuario autenticado puede registrar pagos.**

```
POST /payments
Authorization: Bearer {token}
```

**Body:**
```json
{
    "appointment_id": 1,
    "amount": 200.00,
    "method": "cash",
    "reference": null,
    "paid_at": "2026-06-01 10:55:00"
}
```

> `reference` y `paid_at` son opcionales. Si no se envía `paid_at`, se usa la fecha y hora actual.
> `method` acepta: `cash`, `card`, `transfer`, `other`.
> Si el monto cubre el total de la cita, esta se marca automáticamente como `completed`.

**Respuesta exitosa — 201:**
```json
{
    "success": true,
    "message": "Pago registrado exitosamente",
    "data": {
        "id": 1,
        "amount": 200.00,
        "method": "cash",
        "status": "completed",
        "reference": null,
        "paid_at": "2026-06-01 10:55:00",
        "received_by": { "..." }
    }
}
```

**Errores posibles:**
| Código | Motivo                                      |
|--------|---------------------------------------------|
| 401    | No enviaste el token                        |
| 422    | Falta un campo obligatorio o datos inválidos|

---

## Gastos

### 1. Listar gastos

**Solo admins y recepcionistas pueden ver gastos.**

```
GET /expenses
Authorization: Bearer {token}
```

**Query params opcionales:**
| Parámetro     | Tipo   | Descripción                              |
|---------------|--------|------------------------------------------|
| `category_id` | int    | Filtra por categoría de gasto            |
| `from`        | date   | Fecha de inicio del rango (`YYYY-MM-DD`) |
| `to`          | date   | Fecha de fin del rango (`YYYY-MM-DD`)    |

**Respuesta exitosa — 200:**
```json
{
    "success": true,
    "message": "Gastos obtenidos exitosamente",
    "data": {
        "data": [
            {
                "id": 1,
                "description": "Compra de productos",
                "amount": 500.00,
                "expense_date": "2026-06-01",
                "category": {
                    "id": 2,
                    "name": "Productos",
                    "color": "#F59E0B"
                },
                "registered_by": {
                    "id": 4,
                    "name": "Ana",
                    "lastname": "García",
                    "email": "ana@email.com",
                    "phone": null,
                    "role": "receptionist"
                },
                "created_at": "2026-06-01 09:00:00"
            }
        ],
        "current_page": 1,
        "last_page": 1,
        "per_page": 20,
        "total": 1
    }
}
```

---

### 2. Registrar gasto

**Solo admins y recepcionistas pueden registrar gastos.**

```
POST /expenses
Authorization: Bearer {token}
```

**Body:**
```json
{
    "category_id": 2,
    "description": "Compra de productos",
    "amount": 500.00,
    "expense_date": "2026-06-01"
}
```

> `amount` debe ser mayor a 0.
> `category_id` debe existir en la tabla de categorías.

**Categorías disponibles por defecto:**
| ID | Nombre       | Color     |
|----|--------------|-----------|
| 1  | Renta        | `#EF4444` |
| 2  | Productos    | `#F59E0B` |
| 3  | Servicios    | `#3B82F6` |
| 4  | Nómina       | `#8B5CF6` |
| 5  | Equipamiento | `#10B981` |
| 6  | Otros        | `#6B7280` |

**Respuesta exitosa — 201:**
```json
{
    "success": true,
    "message": "Gasto registrado exitosamente",
    "data": {
        "id": 1,
        "description": "Compra de productos",
        "amount": 500.00,
        "expense_date": "2026-06-01",
        "category": {
            "id": 2,
            "name": "Productos",
            "color": "#F59E0B"
        },
        "registered_by": { "..." },
        "created_at": "2026-06-01 09:00:00"
    }
}
```

**Errores posibles:**
| Código | Motivo                                       |
|--------|----------------------------------------------|
| 401    | No enviaste el token                         |
| 403    | No eres admin ni recepcionista               |
| 422    | Falta un campo obligatorio o datos inválidos |

---

### 3. Eliminar gasto

**Solo el admin puede eliminar gastos. Realiza un soft delete.**

```
DELETE /expenses/{id}
Authorization: Bearer {token}
```

**Respuesta exitosa — 200:**
```json
{
    "success": true,
    "message": "Gasto eliminado exitosamente",
    "data": null
}
```

**Errores posibles:**
| Código | Motivo                 |
|--------|------------------------|
| 401    | No enviaste el token   |
| 403    | No eres admin          |
| 404    | Gasto no encontrado    |

---

## Reporte Financiero

### 1. Resumen financiero por período

**Solo el admin puede ver reportes financieros.**

```
GET /financial/summary
Authorization: Bearer {token}
```

**Query params requeridos:**
| Parámetro | Tipo | Descripción                              |
|-----------|------|------------------------------------------|
| `from`    | date | Fecha de inicio del período (`YYYY-MM-DD`) |
| `to`      | date | Fecha de fin del período (`YYYY-MM-DD`)    |

> `to` debe ser igual o posterior a `from`.

**Respuesta exitosa — 200:**
```json
{
    "success": true,
    "message": "Reporte financiero generado exitosamente",
    "data": {
        "period": {
            "from": "2026-06-01",
            "to": "2026-06-30"
        },
        "income": 5400.00,
        "expenses": 1200.00,
        "net_profit": 4200.00,
        "by_barber": {
            "Juan Pérez": 3200.00,
            "Miguel Torres": 2200.00
        },
        "by_category": {
            "Productos": 700.00,
            "Renta": 500.00
        }
    }
}
```

**Descripción de campos:**
| Campo          | Descripción                                              |
|----------------|----------------------------------------------------------|
| `income`       | Total de ingresos por pagos completados en el período    |
| `expenses`     | Total de gastos registrados en el período                |
| `net_profit`   | Diferencia entre ingresos y gastos                       |
| `by_barber`    | Ingresos desglosados por barbero                         |
| `by_category`  | Gastos desglosados por categoría                         |

**Errores posibles:**
| Código | Motivo                                           |
|--------|--------------------------------------------------|
| 401    | No enviaste el token                             |
| 403    | No eres admin                                    |
| 422    | Faltan los parámetros `from` / `to` o son inválidos |