# Plataforma inmobiliaria segura (PHP)

Aplicación web escrita en PHP puro que permite gestionar inmuebles, amenities, material multimedia y portales de publicación compatibles con feeds XML/JSON. El objetivo es ofrecer un panel ligero con medidas adicionales de seguridad (CSRF tokens, consultas preparadas con PDO, sanitización de datos y cabeceras estrictas) para integrarse con Roomless, Spacest, Idealista, Fotocasa, Badi, HousingAnywhere, Spotahome y cualquier portal que acepte feeds estructurados.

## Requisitos

- PHP 8.1 o superior con extensiones `pdo_sqlite`, `dom` y `SimpleXML` habilitadas.
- Permisos de escritura sobre `config.php` y la carpeta `storage/` (donde, por defecto, se creará `storage/realestate.sqlite`).

## Instalación rápida

1. Clona el repositorio y sitúate en la carpeta del proyecto.
2. Ejecuta el asistente de instalación desde la terminal:
   ```bash
   php bin/install.php
   ```
   El script validará los requisitos, sugerirá como ubicación por defecto `storage/realestate.sqlite` y te guiará por la configuración inicial sin necesidad de abrir un puerto o un navegador.
3. Tras finalizar la instalación podrás desplegar los archivos de la raíz del proyecto en tu servidor web (Apache, Nginx, etc.) y acceder al panel principal (`/`) o al panel de administración (`/admin`).
4. Los parámetros de conexión quedan guardados en `config.php` y la configuración global de la aplicación reside en `app/support/Config.php`.

> El asistente CLI genera/actualiza `config.php`, crea la base de datos y deja cargados los ajustes básicos en la tabla `settings`.

### Importar XML desde CLI

Puedes seguir utilizando el importador por línea de comandos para cargar un feed existente:

```bash
php bin/import.php "XML FINAL 2022 - XML.xml"
```

El script valida la existencia del archivo, parsea cada nodo con `SimpleXMLElement` y realiza altas/actualizaciones mediante consultas preparadas.

## Funcionalidades principales

- **Panel de control** con métricas globales y resumen de la información de contacto configurada.
- **Gestor de propiedades** con CRUD completo, protección CSRF y sanitización de los formularios.
- **Asignación de portales** precargados (Roomless, Spacest, Idealista, Fotocasa, Badi, HousingAnywhere, Spotahome) con posibilidad de actualizar endpoint, token y estado.
- **Feeds seguros** en `/feeds/json` y `/feeds/xml` que incluyen metadatos del sitio (nombre, contacto, moneda, nota personalizada y marca temporal).
- **Importador XML web** dentro del panel de administración que acepta archivo o pegado directo del contenido y realiza _upsert_ por identificador externo.
- **Panel de administración** para actualizar ajustes del sitio, gestionar portales y lanzar importaciones manuales.

## Seguridad destacada

- CSRF tokens regenerados en cada petición mutadora.
- Sanitización de todas las entradas antes de persistirlas.
- PDO con `ATTR_ERRMODE` y `ATTR_DEFAULT_FETCH_MODE` para prevenir inyecciones SQL.
- Cabeceras `Content-Type` definidas explícitamente en las salidas JSON/XML.
- Sesiones configuradas con cookies `httponly` y soporte opcional `secure` si la app corre bajo HTTPS.

## Estructura

```
app/
  Http/              # Router y controladores
  Models/            # Entidades y repositorios PDO
  Services/          # Feeds y importador XML
  support/           # Configuración, DB, helpers, CSRF y vistas
  Views/             # Plantillas PHP con layout compartido
bin/import.php       # Script CLI para importar feeds XML existentes
index.php            # Front controller seguro en la raíz
assets/              # Estilos base disponibles públicamente
config.php           # Configuración global (ruta de SQLite y bandera de instalación)
```

## Verificación rápida

El proyecto no depende de frameworks, por lo que basta con ejecutar:

```bash
php -l index.php
find app bin -name '*.php' -print0 | xargs -0 -n1 php -l
```

Esto validará la sintaxis de todos los archivos PHP.
