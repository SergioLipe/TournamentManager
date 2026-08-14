# Tests

Three suites. They need a local server and database running:

```sh
# terminal 1
php -S 127.0.0.1:8765

# terminal 2
bash tests/todos.sh
```

The HTTP suites assume the local database is seeded with an `admin` account
whose password is `localtest123` (see `database/criar-admin.php`). They create
and delete their own themes and competitors as they go, and clean up after
themselves.

| Suite | What it covers |
|---|---|
| `estatico.pl` | Bracket/brace balance, referenced paths resolve, no leftover references to deleted code. No server needed. |
| `seguranca.sh` | SQL injection on login, CSRF on every write, `apagar.php` refusing GET, theme visibility, the `api/resultado.php` contract, registration validation, output escaping. |
| `uploads.sh` | Valid upload path, PHP disguised as `.jpg`, forged magic bytes, oversized files, directory traversal in theme names, uploading to someone else's theme, delete-removes-file, theme delete cascade. |

Override the defaults with environment variables if your setup differs:

```sh
BASE=http://localhost:8000 MYSQL=/usr/bin/mysql bash tests/todos.sh
```

## Why `uploads.sh` keeps its fixtures inside the project

`curl` on Windows is a native binary and cannot open Git Bash paths like
`/tmp/xxx`. Given such a path it aborts *before sending the request* — which
silently turns every negative test into a false pass, because nothing was
uploaded for the server to reject. The fixtures therefore live in
`.testfixtures/` inside the project and are referenced relatively.

## The GD dependency is load-bearing

`uploads.sh` case 3 uploads a file starting with the bytes `GIF89a` followed by
PHP source. `finfo` reports `image/gif`, and `getimagesize` reads the following
bytes as width and height — so both of those checks pass it. Only
`imagecreatefromstring()` actually rejects it.

If the `gd` extension is missing, `includes/uploads.php` skips that check and
forged files like this get stored. They still cannot execute — the extension is
`.gif` and `Imagens/.htaccess` strips PHP handlers — but the validation is
meaningfully weaker. **Confirm GD is enabled on any host you deploy to.**
