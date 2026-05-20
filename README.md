# 🚗 AutoRent — Sistema de Alquiler de Vehículos

![PHP](https://img.shields.io/badge/PHP-8.1-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-MariaDB_10.4-4479A1?logo=mysql&logoColor=white)
![Apache](https://img.shields.io/badge/Apache-2.4-D22128?logo=apache&logoColor=white)
![XAMPP](https://img.shields.io/badge/XAMPP-Local_Server-FB7A24?logo=xampp&logoColor=white)
![License](https://img.shields.io/badge/Licencia-Académica-green)

> Aplicación web para gestión de alquiler de vehículos desarrollada con PHP 8.1, MariaDB y Apache (XAMPP). Incluye autenticación de usuarios, catálogo de vehículos, sistema de reservas, registro de pagos y panel de administración.

---

## 📋 Tabla de contenidos

- [Características](#-características)
- [Tecnologías](#-tecnologías)
- [Estructura del proyecto](#-estructura-del-proyecto)
- [Requisitos previos](#-requisitos-previos)
- [Instalación paso a paso](#-instalación-paso-a-paso)
- [Credenciales de prueba](#-credenciales-de-prueba)
- [Capturas de pantalla](#-capturas-de-pantalla)
- [Base de datos](#-base-de-datos)
- [Seguridad implementada](#-seguridad-implementada)
- [Autores](#-autores)

---

## ✨ Características

- ✅ Login y registro de usuarios con hash SHA-256
- ✅ Roles diferenciados: **Admin** y **Cliente**
- ✅ Catálogo de vehículos con filtros por fecha, marca y precio
- ✅ Validación de disponibilidad en tiempo real
- ✅ Reservas con cálculo automático del costo
- ✅ Registro de pagos (tarjeta, efectivo, transferencia)
- ✅ Panel administrativo (CRUD de vehículos, usuarios y reservas)
- ✅ Diseño responsive con dark theme
- ✅ Protección contra SQL Injection y XSS

---

## 🛠 Tecnologías

| Capa | Tecnología | Versión |
|------|-----------|---------|
| Backend | PHP | 8.1.25 |
| Base de datos | MariaDB (MySQL) | 10.4.32 |
| Servidor web | Apache (XAMPP) | 2.4.x |
| Admin BD | phpMyAdmin | 5.2.1 |
| Frontend | HTML5 + CSS3 + JS vanilla | — |
| Tipografía | Google Fonts (Syne + DM Sans) | CDN |

---

## 📁 Estructura del proyecto

```
autorent/
├── index.php            ← Login y registro
├── home.php             ← Dashboard principal
├── vehiculos.php        ← Catálogo con filtros
├── reservar.php         ← Formulario de reserva
├── reservas.php         ← Mis reservas
├── admin.php            ← Panel de administración
├── logout.php           ← Cerrar sesión
├── includes/
│   ├── db.php           ← Conexión a la base de datos
│   ├── auth.php         ← Funciones de autenticación
│   └── layout.php       ← Estilos y navegación compartida
├── database/
│   └── autorent.sql     ← Script SQL completo (estructura + datos)
└── README.md
```

---

## ✅ Requisitos previos

Antes de instalar, asegúrate de tener:

- [XAMPP](https://www.apachefriends.org/es/index.html) instalado (incluye PHP, Apache y MariaDB)
- Navegador web moderno (Chrome, Firefox, Edge)
- Git (opcional, para clonar el repositorio)

---

## 🚀 Instalación paso a paso

### 1. Obtener el proyecto

**Opción A — Clonar con Git:**
```bash
git clone https://github.com/TU_USUARIO/autorent.git
```

**Opción B — Descargar ZIP:**  
Clic en el botón verde **`Code`** → **`Download ZIP`** → descomprimir.

---

### 2. Copiar a XAMPP

Mueve o copia la carpeta `autorent/` dentro del directorio raíz de XAMPP:

| Sistema operativo | Ruta destino |
|-------------------|-------------|
| Windows (XAMPP) | `C:\xampp\htdocs\autorent\` |
| Windows (WAMP) | `C:\wamp64\www\autorent\` |
| macOS (MAMP) | `/Applications/MAMP/htdocs/autorent/` |
| Linux (LAMP) | `/var/www/html/autorent/` |

---

### 3. Importar la base de datos

1. Abre **XAMPP Control Panel** → inicia **Apache** y **MySQL**
2. Abre el navegador y entra a: `http://localhost/phpmyadmin`
3. Haz clic en **Nueva** (panel izquierdo)
4. Escribe como nombre: `alquiler_carros`
5. Selecciona cotejamiento: `utf8mb4_general_ci`
6. Clic en **Crear**
7. Con la BD seleccionada, ve a la pestaña **Importar**
8. Selecciona el archivo: `database/autorent.sql`
9. Clic en **Continuar** ✓

---

### 4. Verificar configuración de conexión

Abre el archivo `includes/db.php` y verifica que los datos coincidan con tu instalación:

```php
define('DB_HOST', 'localhost');    // No cambiar en XAMPP estándar
define('DB_USER', 'root');         // Usuario por defecto de XAMPP
define('DB_PASS', '');             // Contraseña vacía por defecto en XAMPP
define('DB_NAME', 'alquiler_carros');
```

> ⚠️ **Nota:** Si cambiaste la contraseña de MySQL en XAMPP, actualiza `DB_PASS` con tu contraseña.

---

### 5. Abrir la aplicación

Con Apache y MySQL corriendo en XAMPP, abre tu navegador y entra a:

```
http://localhost/autorent/
```

¡Listo! 🎉

---

## 🔐 Credenciales de prueba

| Usuario | Correo | Contraseña | Rol |
|---------|--------|-----------|-----|
| Samuel | samuel@gmail.com | 123456 | **Admin** |
| Felipe | felipe@gmail.com | 123456 | Cliente |

> El usuario **Admin** tiene acceso al panel de administración en `http://localhost/autorent/admin.php`

---

## 📸 Capturas de pantalla

### Pantalla de Login
> Dark theme con tabs de Iniciar sesión / Registrarse

### Dashboard principal
> Estadísticas del usuario, búsqueda rápida y vehículos destacados

### Catálogo de vehículos
> Filtros por fecha, búsqueda por texto y ordenamiento por precio

### Panel de administración
> CRUD de vehículos, gestión de usuarios y reporte de reservas

---

## 🗄 Base de datos

El archivo `database/autorent.sql` contiene:

- ✅ Creación de la base de datos `alquiler_carros`
- ✅ Estructura de las 4 tablas: `usuarios`, `vehiculos`, `reservas`, `pagos`
- ✅ Claves foráneas con `ON DELETE CASCADE`
- ✅ Datos de prueba listos para usar

**Diagrama ER resumido:**
```
usuarios ──< reservas >── vehiculos
               │
               └──< pagos
```

---

## 🔒 Seguridad implementada

| Amenaza | Solución |
|---------|---------|
| SQL Injection | Prepared Statements + `bind_param()` en todas las consultas |
| XSS | `htmlspecialchars()` en todas las salidas al HTML |
| Acceso no autorizado | `redirigirSiNoAutenticado()` al inicio de cada página privada |
| Escalada de privilegios | Verificación de `$_SESSION['rol']` en `admin.php` |
| Contraseñas en texto plano | `hash('sha256', $contrasena)` antes de guardar o comparar |

---

## 👥 Autores

Desarrollado como proyecto académico.

| Nombre | Rol |
|--------|-----|
| [Tu nombre aquí] | Desarrollador principal |

---

## 📄 Licencia

Proyecto de uso académico. No destinado a producción sin las mejoras de seguridad indicadas en el manual técnico.
