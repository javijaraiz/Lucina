# Lucina - Comparador de Tarifas Eléctricas
### Proyecto Final de Ciclo — DAW DUAL 25/26

Lucina es una aplicación web para comparar tarifas de luz. El usuario sube su factura en PDF, la IA extrae los datos automáticamente y el sistema calcula cuánto se ahorraría con cada tarifa del catálogo.

La idea surgió de una necesidad que vi en una empresa familiar. Necesitaban digitalizar el proceso de captura de facturas, que hasta ahora se hacía a mano.

## Cómo funciona

1. El cliente sube su factura (PDF o imagen) sin necesidad de registrarse
2. Google Gemini analiza el documento y extrae los datos: CUPS, consumos P1/P2/P3, potencias e importe
3. El cliente revisa y corrige los datos si es necesario
4. Se muestra un ranking de tarifas ordenado por ahorro estimado
5. Si le interesa alguna, puede solicitarla y el administrador recibe una notificación

El panel de administración permite gestionar compañías, tarifas y ver los clientes que han pedido información.

## Acceso

**Zona pública (cliente):** http://maskfundas.es/lucina/

**Panel de administración:** http://maskfundas.es/lucina/login.php
- Usuario: `admin@lucina.es` o `admin`
- Contraseña: `123456`

## Tecnologías

- PHP 8 + PDO (MySQL)
- Bootstrap 5.3
- Google Gemini API (análisis de facturas con IA)
- JavaScript vanilla con Fetch API

## Instalación local (XAMPP)

1. Copiar la carpeta en `C:\xampp\htdocs\Luzina`
2. Crear la base de datos `lucina_db` e importar `lucina_db.sql`
3. Renombrar `config.php.example` a `config.php` y rellenar los datos de conexión y la API key de Gemini
4. Configurar el SMTP en `funciones/notificaciones.php` (servidor, usuario y contraseña)
5. Acceder a `http://localhost/Luzina`

## Roles

| Rol | Acceso |
|---|---|
| Cliente / Invitado | Zona pública — sin registro |
| Administrador | Panel de gestión — requiere login |

---
Autor: Javier González Paniagua — DAW DUAL 25/26
