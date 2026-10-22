# Welcome to SwiftDashPHP

SwiftDashPHP is a modern, open-source PHP framework for quickly building powerful applications.

No Magic - Pure PHP behind and pure JS for front parts

Makes it easy to start with built-in features such as:

- Authentication for local, Google, MS (live and Azure)
- Support for MySQL/MariaDB/SQlite/Postgres
- TailwindCSS
- DataGrid with powerful features
- Charts (via Chart.js and QuickChart.io)
- Markdown rendering
- Forms
- API
- Admin Panel
- HTML Components
- User Settings
- Easy containerization with Docker
- Dark Mode
- Localisation
- SendGrid mailsender

---

## Authentication for local, Google, MS (live and Azure)

SwiftDashPHP comes with a built-in authentication system that is easy to use and customize. Check it out on our [Docs section](/docs/authentication). We also have security features such as a Firewall that stops IPs from accessing parts we want to protect, as well as parts that we want to keep public.

### Running behind a reverse proxy / load balancer

Client IPs (used by the Firewall, logs and UTM capture) come from `REMOTE_ADDR`. Forwarding headers such as `X-Forwarded-For`, `X-Client-IP` and `CF-Connecting-IP` are only honoured when the direct peer is listed in `TRUSTED_PROXIES` in `.env`, otherwise anyone could spoof their IP:

```bash
# comma separated IPs and IPv4 CIDRs, or * to trust every upstream (e.g. Azure App Service, Cloudflare)
TRUSTED_PROXIES="10.0.0.0/8,172.16.0.0/12"
```

### Content Security Policy

The CSP header is sent by PHP (`App::bootstrap`) on HTTPS requests with a fresh nonce per request (`CSP_NONCE`). Edit the policy, e.g. to allow another CDN, in `CSP_POLICY` in `config/site-settings.php`. Inline scripts in your own views must carry `nonce="<?= CSP_NONCE ?>"`.

### Database TLS

With `DB_SSL=true` the MySQL server certificate is verified against the bundled DigiCert CA. Set `DB_SSL_VERIFY=false` in `.env` only for self-signed test servers.

### DataGrid CRUD API

The `/api/datagrid/*` endpoints (used by the DataGrid/AGGrid edit and delete buttons) are admin-only. To let regular users edit specific tables, list them in `DATAGRID_USER_TABLES` in `config/site-settings.php`.

---

## Tailwind setup on clone

```bash
npm install
```

```bash
npm run build-css-prod
```

```bash
npx update-browserslist-db@latest
```

## PHP CodeSniffer checks

Checks

```bash
./vendor/bin/phpcs --ignore=vendor/* --extensions=php .
```

Fixes

```bash
./vendor/bin/phpcbf --ignore=vendor/* --extensions=php .
```

```bash
php -d memory_limit=512M ./vendor/bin/phpcbf --ignore=vendor/* --extensions=php .
```

## Support for MySQL/MariaDB/SQlite/Postgres

SwiftDashPHP supports MySQL, MariaDB, SQLite, and Postgres. You can easily switch between them by changing the `.env` file. The database connection is handled by PDO, with very little magic in between, taking advantage of the agnostic nature of PDO. There is no ORM, so you write your own queries.

---

## TailwindCSS

SwiftDashPHP uses TailwindCSS for styling. The framework uses a global `$theme` variable which can be switched easily and utilizes Tailwind's native colors such as sky, cyan, emerald, teal, blue, indigo, violet, purple, fuchsia, pink, red, rose, orange, yellow, amber, lime, gray, slate, stone. Each user has their own styling based on these colors. There is also global theming for light and dark modes, based on constants in the config file. The dark/light switch is based on the dark class mode in TailwindCSS. Everything comes ready with a switcher, default system theming, chart theming, and requires very little effort on your part.

---

## DataGrid with powerful features

SwiftDashPHP comes with a powerful DataGrid component (based on Datatables) that allows you to display data in a table with features such as sorting, filtering, pagination, and more. It can display PHP Arrays, DB queries, whole DB tables, and provides CRUD for those. More on [DataGrid](/datagrid).

---

## Charts (via Chart.js and QuickChart.io)

Chart.js and Quickchart.io are ready-to-use chart functions for the most popular chart types. You can also easily autoload JS charts only with PHP code using the autoloading mechanism. See more in the example [Charts](/charts).

---

## Markdown rendering

With the power of Parsedown (which, as of now, is not up to date for PHP 8.4) and some custom classes we have here, you can render locally stored or remotely stored Markdown files automatically styled with Tailwind. Check out the [Docs](/docs) section. Here let's render some MD below.

## Forms

This is a big one. All (or almost all) of the buttons that do something on the framework are actually Form Components. The Forms component takes out the big headache of creating the form and the submission hurdles of it. You can easily create modals too. Built-in CSRF protection is also included. Check out the [Forms](/forms) section.

---

## API

Since it's PHP, we know doing API endpoints is not hard. The framework helps a bit with some Response classes and some API checks, along with a few other tools like JWT capabilities and API keys.

---

## Admin Panel

We have an Admin panel which is basic but cool and expandable.

---

## HTML Components

We have an HTML component which has static HTML methods providing HTML elements. DataGrid and Forms components are using it, as well as normal HTML output, for standardized output everywhere.

---

## User Settings

Comes with a user settings page as well, built-in with some features.

---

## Easy containerization with Docker

Tested to run in a container with a ready-to-use Dockerfile that can get the app running on major cloud platforms in a few clicks.

---

## Dark Mode

Built-in Dark/Light mode.

---

## Localisation

The foundation for localisation is there; you just need to expand it. A working language switcher is also included.

---

## SendGrid mailsender

API endpoint for sending mails and a TinyMCE endpoint for sending manually.

## Docker

```bash
DOCKER_BUILDKIT=1 docker build -t swiftdashphp:v1.6.2 .
```

### Azure ACR

Login to Azure ACR, build and push

```bash
ACR="XXXX"
```

```bash
az acr login --name $ACR
```

```bash
docker tag swiftdashphp:v1.6.2 $ACR.azurecr.io/swiftdashphp:v1.6.2
```

```bash
docker push $ACR.azurecr.io/swiftdashphp:v1.6.2
```

```bash
docker run -dp 8080:80 --env-file .env swiftdashphp:v1.6.2
```

### Github Container Registry

```bash
USER='XXX'
```

```bash
USER_LOWER=${USER,,}
```

```bash
docker login ghcr.io -u $USER
```

```bash
docker tag swiftdashphp:v1.6.2 ghcr.io/$USER_LOWER/swiftdashphp:v1.6.2
```

```bash
docker push ghcr.io/$USER_LOWER/swiftdashphp:v1.6.2
```
