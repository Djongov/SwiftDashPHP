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

---

## PHP CodeSniffer checks

Checks

```bash
./vendor/bin/phpcs --standard=phpcs.xml --ignore=vendor/* --extensions=php .
```

Fixes

```bash
./vendor/bin/phpcbf --standard=phpcs.xml --ignore=vendor/* --extensions=php .
```

```bash
php -d memory_limit=512M ./vendor/bin/phpcbf --standard=phpcs.xml --ignore=vendor/* --extensions=php .
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

```cmd
DOCKER_BUILDKIT=1 docker build -t swiftdashphp:latest .
```

```cmd
az acr login --name swisscrmacrsn
```

```cmd
docker tag swiftdashphp:latest swisscrmacrsn.azurecr.io/swiftdashphp:latest
```

```cmd
docker push swisscrmacrsn.azurecr.io/swiftdashphp:latest
```
