# Local Dev Environment Rules
- This project runs inside a Docker container managed by Docker Compose and Traefik.
- Do NOT run `php artisan ...`, `composer ...`, or `npm ...` directly on the host machine.
- Always execute commands using:
  - `docker compose exec app php artisan <command>` (or `artisan <command>`)
  - `docker compose exec app composer <command>` (or `composer <command>`)
  - `docker compose exec app npm <command>` (or `npm <command>`)
- Database host is `mysql83` or `mysql84` on internal port `3306`.