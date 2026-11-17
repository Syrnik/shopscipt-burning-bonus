# AGENTS.md

## Commit Messages

All commits must follow the [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) specification.

Format: `<type>[optional scope]: <description>`

Common types: `feat`, `fix`, `chore`, `refactor`, `docs`, `test`, `ci`.

## Changelog

The `CHANGELOG.md` file must follow the [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) format.
Entries are written in Russian.

- Add entries under `[Unreleased]` during development.
- On release, rename `[Unreleased]` to the version number with the release date.
- Sections within a version: `Added`, `Changed`, `Deprecated`, `Removed`, `Fixed`, `Security`.

## Release tags

Git release tags use a `v` prefix: `vX.Y.Z` (e.g. `v1.0.4`), matching the
convention used across this author's other plugin repos (sdekint, axilogpro,
syrattach, ordit). `plugin.php` keeps storing the bare version number without
the prefix — only the git tag itself gets `v`. `CHANGELOG.md` also uses the bare
version number.

## Checks

- Tests: `phpunit` from the plugin directory. `tests/init.php` boots `waSystem`
  and `wa('shop')` directly, so a globally installed PHPUnit works as-is.
  Tests must not touch the stand's data or plugin settings in the database.
- Static analysis: `psalm -c psalm74.xml` and `psalm -c psalm85.xml`. Both
  share `psalm-baseline.xml`. Fix new issues instead of adding them to the baseline.
- The plugin supports PHP 7.4: no syntax newer than 7.4 in `lib/`.
