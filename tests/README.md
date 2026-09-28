# Tests

Two suites. The security one needs a local server and database running:

```sh
# terminal 1
php -S 127.0.0.1:8765

# terminal 2
bash tests/todos.sh
```

The HTTP suite assumes the local database was loaded from `database/schema.sql`
and `database/seed-temas-publicos.sql`. It creates a hidden theme of its own as it
goes, and removes it at the end.

| Suite | What it covers |
|---|---|
| `estatico.pl` | Bracket/brace balance, referenced paths resolve, no leftover references to deleted code (including the removed account and upload pages). No server needed. |
| `seguranca.sh` | `api/competidores.php` limits, hidden themes staying hidden, the `api/resultado.php` contract (CSRF, one final, one theme), output escaping. |

Override the defaults with environment variables if your setup differs:

```sh
BASE=http://localhost:8000 MYSQL=/usr/bin/mysql bash tests/todos.sh
```
