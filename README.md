# Plataforma inmobiliaria segura (PHP)

Aplicación web escrita en PHP puro que permite gestionar inmuebles, amenities, material multimedia y portales de publicación compatibles con feeds XML/JSON. El objetivo es ofrecer un panel ligero con medidas adicionales de seguridad (CSRF tokens, consultas preparadas con PDO, sanitización de datos y cabeceras estrictas) para integrarse con Roomless, Spacest, Idealista, Fotocasa, Badi, HousingAnywhere, Spotahome y cualquier portal que acepte feeds estructurados.

## Requisitos

- PHP 8.1 o superior con extensiones `pdo_sqlite`, `dom` y `SimpleXML` habilitadas.
- Permisos de escritura sobre `config.php` y la carpeta donde residirá la base de datos SQLite.

## Instalación rápida

1. Clona el repositorio y sitúate en la carpeta del proyecto.
2. Inicia el servidor embebido de PHP apuntando a `public/`:
   ```bash
   php -S 127.0.0.1:8000 -t public/
   ```
3. Abre `http://127.0.0.1:8000/install.php` y sigue el asistente de instalación paso a paso:
   - Verificación de requisitos.
   - Definición de la ruta del archivo SQLite.
   - Configuración inicial del sitio (nombre comercial, correo de contacto, moneda y teléfono de soporte).
4. Tras finalizar el asistente podrás acceder directamente al panel principal (`/`) o al panel de administración (`/admin`).

> El asistente genera/actualiza `config.php`, crea la base de datos y deja cargados los ajustes básicos en la tabla `settings`. Una vez en producción, elimina o restringe el acceso a `public/install.php`.

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
  Support/           # Configuración, DB, helpers, CSRF y vistas
  Views/             # Plantillas PHP con layout compartido
bin/import.php       # Script CLI para importar feeds XML existentes
public/index.php     # Front controller seguro
public/install.php   # Asistente de instalación paso a paso
public/assets/       # Estilos base
config.php           # Configuración global (ruta de SQLite y bandera de instalación)
```

## Verificación rápida

El proyecto no depende de frameworks, por lo que basta con ejecutar:

```bash
php -l public/index.php
find app bin -name '*.php' -print0 | xargs -0 -n1 php -l
```

Esto validará la sintaxis de todos los archivos PHP.
