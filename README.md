# Hexágono Research

Sitio web de **Hexágono Research**, firma mexicana de investigación de mercados
especializada en estudios **pre-test** y **post-test** de campañas de
comunicación social, con foco en clientes de gobierno federal e instituciones
públicas.

Es una aplicación Laravel estándar: una landing page en Blade (sin SPA) con un
formulario de contacto que persiste en base de datos y, opcionalmente, notifica
por correo.

## Stack

- **Backend:** Laravel 13, PHP 8.5
- **Frontend:** Tailwind CSS 4 (vía Vite) + JavaScript vanilla para
  microinteracciones (`resources/js/landing.js`)
- **Base de datos:** la que definas en `.env` (el proyecto usa PostgreSQL por
  defecto en desarrollo)
- **Testing:** Pest
- **Auth / cuenta:** Livewire + Flux + Fortify (starter kit base del proyecto;
  no se usa en la landing pública, solo en `/dashboard`)

## Estructura relevante

```
app/Enums/StudyType.php                        Tipos de estudio del formulario
app/Models/ContactMessage.php                   Modelo de mensajes de contacto
app/Http/Requests/StoreContactMessageRequest.php  Validación del formulario
app/Http/Controllers/ContactController.php      Guarda el mensaje y responde
app/Mail/ContactMessageReceived.php             Notificación por correo (opcional)
database/migrations/..._create_contact_messages_table.php
resources/views/welcome.blade.php               Landing page completa
resources/views/components/                     app-hexagon-mark, service-icon, contact-field
resources/js/landing.js                         Canvas de red, scroll reveal, envío del formulario
resources/css/app.css                           Tokens de color y microinteracciones (@theme)
tests/Feature/ContactControllerTest.php         Tests del formulario de contacto
```

## Requisitos

- PHP 8.5 (ver `.php-version`)
- Node 22 (ver `.node-version`)
- Composer
- Una base de datos accesible (PostgreSQL, MySQL o SQLite)

## Puesta en marcha

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configura la conexión a base de datos en `.env` (`DB_CONNECTION`, `DB_HOST`,
`DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) y, si vas a activar el
correo de notificación del formulario, `MAIL_*` y `MAIL_TO_ADDRESS` (dirección
que recibe los mensajes de contacto).

```bash
php artisan migrate
npm install
npm run build   # o `npm run dev` para desarrollo con recarga en caliente
php artisan serve
```

Alternativamente, `composer setup` encadena instalación de dependencias,
`.env`, `key:generate`, migración y build del frontend en un solo paso.

Para desarrollo día a día, `composer dev` levanta en paralelo el servidor,
el worker de colas, los logs (`pail`) y Vite.

## Formulario de contacto

- Ruta: `POST /contacto` (`routes/web.php`, `contact.store`)
- Campos: nombre, institución/dependencia (opcional), correo, teléfono
  (opcional), tipo de estudio (`pretest`, `posttest`, `opinion`, `otro`) y
  mensaje.
- El envío funciona con y sin JavaScript:
  - Con JS: `resources/js/landing.js` envía el formulario por `fetch` y
    muestra el resultado sin recargar la página.
  - Sin JS: el formulario hace un POST normal y la página se re-renderiza con
    el mensaje de éxito (`session('success')`) o los errores de validación.
- El envío del correo de notificación (`ContactMessageReceived`) está
  implementado pero **comentado** en `ContactController::store`. Descoméntalo
  cuando quieras activarlo; usa `config('mail.to_address')`
  (env `MAIL_TO_ADDRESS`).

## Comandos útiles

```bash
php artisan test --compact       # correr los tests (Pest)
vendor/bin/pint --format agent   # formatear código PHP
vendor/bin/phpstan analyse       # análisis estático
composer test                    # lint + análisis estático + tests, todo junto
```

## Notas

- No se registran nombres de clientes reales del sector público; la sección
  "Sectores" describe el tipo de instituciones a las que se atiende de forma
  genérica.
- Los datos de contacto en el footer (correo, teléfono, dirección) son
  placeholders — reemplázalos por los datos reales antes de publicar el sitio.
- El aviso de privacidad del footer es un placeholder y debe sustituirse por
  el texto legal real antes de producción.
