# Tournament

A small PHP + MySQL web app for running single-elimination tournaments where **you** are the judge.
Two competitors are shown at a time, you click the one you prefer, and the bracket advances until
one winner is left.

Useful when you cannot make up your mind, or as a friendly competition with friends.

## Features

- Play with the built-in themes, or create your own and upload images to them.
- Anywhere from 2 to 16 competitors. Odd numbers work too — the extras get a bye.
- Play with plain names instead of images.
- Every battle is recorded, so the Statistics page shows which entries actually win.

## Requirements

- PHP 7.4 or newer, with `pdo_mysql`, `fileinfo`, `mbstring` and **`gd`**
- MySQL 5.7 / MariaDB 10.2 or newer
- A web server pointed at the project root

Composer is optional. If `vendor/` exists it gets autoloaded, but nothing here needs it.

**`gd` is not optional in practice.** Upload validation uses it to actually decode
each file. Without it, a file starting with the bytes `GIF89a` followed by arbitrary
content passes both `finfo` and `getimagesize` and gets stored. Such a file still
cannot execute — it is saved with an image extension and `Imagens/.htaccess` strips
PHP handlers — but the check that is supposed to catch it is skipped. Verify with:

```php
<?php var_dump(function_exists('imagecreatefromstring'));
```

## Setup

1. **Create the database and load the schema**

   ```sh
   mysql -u root -p -e "CREATE DATABASE torneio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -u root -p torneio_db < database/schema.sql
   ```

2. **Configure the connection**

   ```sh
   cp .env.example .env
   ```

   Then edit `.env` with your database credentials.

3. **Create the admin account and the public themes**

   ```sh
   php database/criar-admin.php admin
   ```

   The password is asked interactively, so it never ends up in your shell history.

4. **Load the public themes**

   ```sh
   mysql -u root -p torneio_db < database/seed-temas-publicos.sql
   ```

   Creates the built-in themes and their competitors, pointing at the images in
   `Imagens/`. Safe to re-run: existing themes keep their IDs and only their
   competitors are replaced.

   This file is **generated** by `tools/gerar-seed.php` from whatever is in
   `Imagens/` — don't edit it by hand. See "Adding themes in bulk" below.

   Note the display names keep their accents (`Bacalhau à Lagareiro`) while the
   files on disk are plain ASCII (`Bacalhau-a-Lagareiro.jpg`). That split is
   deliberate — see "Filenames" below.

5. **Make the upload directory writable**

   ```sh
   mkdir -p Imagens/temas && chmod 755 Imagens/temas
   ```

6. **Serve it**

   ```sh
   php -S localhost:8000
   ```

   Or point Apache/nginx at the project root.

## Adding themes in bulk

Uploading images one theme at a time through the site is fine for a personal
theme, but the public ones are built from the command line instead. Write a
list of names, fetch the pictures, regenerate the SQL:

```sh
PHP="C:/xampp/php/php.exe"                       # PHP is not on PATH here

$PHP tools/buscar-imagens.php Animals            # names -> Imagens/Animals/
$PHP tools/gerar-seed.php                        # Imagens/ -> the seed SQL
mysql -u root -p torneio_db < database/seed-temas-publicos.sql
```

A theme is a text file in `tools/temas/` whose filename is the theme name, one
competitor per line. Images come from the lead image of the matching Wikipedia
article. Full details, including how to fix a name that came out wrong, are in
[`tools/README.md`](tools/README.md).

`tools/` never reaches the server — `deploy.sh` excludes it.

## Tests

```sh
php -S 127.0.0.1:8765     # in one terminal
bash tests/todos.sh       # in another
```

Three suites: static checks, security (SQL injection, CSRF, access control,
output escaping), and uploads (forged images, traversal, ownership, cascade
deletes). See `tests/README.md`.

## Deploying

`deploy.sh` publishes over FTP, uploading only files whose contents changed
since the last run.

```sh
./deploy.sh --dry-run     # show what would go
./deploy.sh               # publish
./deploy.sh --full        # re-upload everything
```

It needs a `.deploy.env` (gitignored) with `FTP_HOST`, `FTP_USER`, `FTP_PASS`
and `FTP_DIR`. Your production database settings go in `.env.production`
(also gitignored), which is uploaded as `.env`. `README.md`, `tests/`,
`database/` and the deploy files themselves are never uploaded.

### Upgrading an existing installation

If you already have the old database, run the migration instead of `schema.sql`:

```sh
mysqldump -u root -p torneio_db > backup.sql   # do this first
mysql -u root -p torneio_db < database/migration.sql
```

It converts the tables to InnoDB/utf8mb4 (fixing the `Bacalhau Ã  Lagareiro` mojibake),
adds the `publico` flag and the foreign keys, and deletes rows orphaned by themes that
no longer exist. **Run it only once** — the encoding step reinterprets raw bytes, so
applying it twice corrupts accented names.

Passwords are left alone by the migration. They are stored in plain text in the old
schema, and each one is converted to a bcrypt hash automatically the next time that
user logs in successfully.

> `database/dump.sql` is the original 2025 export, kept for reference only. It contains
> the old plain-text passwords, so treat any account listed there as compromised and
> reset it with `database/criar-admin.php`.

### Shared hosting without SSH (InfinityFree and similar)

`database/criar-admin.php` is a CLI script, and free shared hosting generally offers
neither SSH nor remote MySQL access — so you cannot run it against the live database.
Generate the hash on your own machine instead:

```sh
php -r "echo password_hash('your-real-password', PASSWORD_DEFAULT), PHP_EOL;"
```

Then in phpMyAdmin, paste the output into:

```sql
INSERT INTO utilizador (username, password) VALUES ('admin', 'PASTE_THE_HASH_HERE');
UPDATE tema SET publico = 1 WHERE utilizadorId = (SELECT id FROM utilizador WHERE username = 'admin');
```

Two more things that bite on this kind of host:

- **Never put `php_flag` or `php_value` in a `.htaccess`.** They only work under
  mod_php; where PHP runs as CGI/FastCGI they return 500 for the whole directory.
  The `.htaccess` files here keep theirs inside `<IfModule>` guards for that reason.
- The whole project sits inside the public `htdocs/`, so `.env` and `database/*.sql`
  would otherwise be downloadable. The root `.htaccess` denies them — do not remove it.

## Layout

```
index.php                 the tournament itself
Login.php Registar.php Logout.php
CriarTema.php             create themes, upload and delete images
AdicionarCompetidor.php   upload handler (POST only, redirects back)
apagar.php                delete handler (POST only, redirects back)
Estatisticas.php          per-theme win/loss records
sobre.php                 about page

api/competidores.php      returns a theme's competitors as JSON
api/resultado.php         records the result of one battle

includes/                 config, PDO layer, auth/CSRF, uploads, shared header/footer
CSS/style.css             all styling
JavaScript/bracket.js     builds and draws the bracket
JavaScript/torneio.js     runs the tournament
JavaScript/ui.js          navbar and competitor-count control
Imagens/                  built-in images; user uploads land in Imagens/temas/<id>/
database/                 schema, migration, admin helper, original dump
```

## Notes on the rewrite

The original version had every SQL query built by string concatenation, stored passwords
in plain text, and let anyone delete competitors or inflate statistics with an
unauthenticated request. That is all fixed:

- every query is a prepared statement;
- passwords are bcrypt hashes, upgraded transparently on first login;
- writes require a session, ownership of the theme, and a CSRF token;
- uploads are validated by actual file content, stored under generated names in
  a directory derived from the theme id, and `Imagens/.htaccess` blocks execution.

The eight hand-written `Extras/*_Brackets.html` files are gone — brackets are generated
from the competitor count instead, which is what makes odd-numbered tournaments possible.

## Filenames

Every file under `Imagens/` has a plain-ASCII name with no spaces. This is not
cosmetic: the FTP server on the shared host this deploys to rejects `STOR` for
any filename containing a space, answering `553 Permission denied` and leaving a
truncated file behind (`AC DC.jpg` became `DC.jpg`). Accented names could not be
deleted over FTP at all afterwards.

Display names in the database keep their accents and spaces, because that
constraint belongs to the filesystem, not to the data. `database/seed-temas-publicos.sql`
carries the original names recovered from the 2025 export.

Keep new filenames ASCII. If you add images through the web UI this is automatic
— uploads are stored under generated hex names.


## Author

Sérgio Filipe Azevedo Gonçalves — <lipewtf@hotmail.com>
