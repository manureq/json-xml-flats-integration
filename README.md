# Plataforma inmobiliaria segura (PHP)

Aplicación web escrita en PHP puro que permite gestionar inmuebles, amenities, material multimedia y portales de publicación compatibles con feeds XML/JSON. El objetivo es ofrecer un panel ligero con medidas adicionales de seguridad (CSRF tokens, consultas preparadas con PDO, sanitización de datos y cabeceras estrictas) para integrarse con Roomless, Spacest, Idealista, Fotocasa, Badi, HousingAnywhere, Spotahome y cualquier portal que acepte feeds estructurados.

## Requisitos

- PHP 8.1 o superior con extensiones `pdo_sqlite`, `dom`, `SimpleXML` y `zip` habilitadas.
- Permisos de escritura sobre `config.php` y la carpeta `storage/` (donde, por defecto, se creará `storage/realestate.sqlite`).

## Instalación rápida

1. Clona el repositorio y sitúate en la carpeta del proyecto.
2. Ejecuta el asistente de instalación desde la terminal:
   ```bash
   php bin/install.php
   ```
   El script valida los requisitos, sugiere como ubicación por defecto `storage/realestate.sqlite`, solicita los datos de contacto del sitio **y las credenciales del usuario administrador** sin necesidad de abrir puertos ni un navegador.
3. Tras finalizar la instalación podrás desplegar los archivos de la raíz del proyecto en tu servidor web (Apache, Nginx, etc.) y acceder al panel principal (`/`) autenticándote con el usuario y contraseña definidos en el asistente.
4. Los parámetros de conexión y credenciales quedan guardados en `config.php`, mientras que la configuración auxiliar permanece en `app/support/Config.php`. El panel recomienda mantener la base en `storage/realestate.sqlite` para simplificar copias de seguridad.

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
- **Panel de administración con autenticación obligatoria** (usuario/contraseña) para actualizar ajustes del sitio, gestionar portales y lanzar importaciones manuales.
- **Actualizador web mediante ZIP**: desde `/admin` puedes subir un paquete firmado para aplicar actualizaciones sin acceder al servidor.

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
  Services/          # Feeds, importador XML y actualizador vía ZIP
  support/           # Configuración, DB, helpers, CSRF, autenticación y vistas
  Views/             # Plantillas PHP con layout compartido
bin/import.php       # Script CLI para importar feeds XML existentes
index.php            # Front controller seguro en la raíz
assets/              # Estilos base disponibles públicamente
config.php           # Configuración global (ruta de SQLite, bandera de instalación y credenciales admin)
```

## Verificación rápida

El proyecto no depende de frameworks, por lo que basta con ejecutar:

```bash
php -l index.php
find app bin -name '*.php' -print0 | xargs -0 -n1 php -l
```

Esto validará la sintaxis de todos los archivos PHP.
