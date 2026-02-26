# Release Notes

## Symfony 8.0 Upgrade

### Summary
Upgraded the application from Symfony 7.4 to Symfony 8.0.6.

---

### Dependency Changes (`composer.json`)

| Package | Before | After |
|---------|--------|-------|
| All `symfony/*` packages | `^7.4` | `^8.0` |
| `symfony/monolog-bundle` | `^3.10` | `^4.0` |
| `phpmd/phpmd` | `^2.15.0` | `^3.0@dev` |
| `extra.symfony.require` | `7.4.*` | `8.0.*` |

Added `"minimum-stability": "dev"` and `"prefer-stable": true` to allow `phpmd` 3.x (no stable release yet).

---

### Breaking Changes Fixed

#### Routing
- **14 controllers**: `Symfony\Component\Routing\Annotation\Route` → `Symfony\Component\Routing\Attribute\Route` (Annotation namespace removed in Symfony 8)
- `config/routes/framework.yaml`: `errors.xml` → `errors.php` (XML routing resources removed in Symfony 8)
- `config/routes/web_profiler.yaml`: `wdt.xml` → `wdt.php`, `profiler.xml` → `profiler.php`
- `config/packages/framework.yaml`: removed deprecated `router.cache_dir` option

#### Doctrine MongoDB ODM
- **6 Document files** (`ArchivedMovie`, `ArchivedSeries`, `Episode`, `History`, `Movie`, `Show`): `Doctrine\ODM\MongoDB\Mapping\Annotations` → `Doctrine\ODM\MongoDB\Mapping\Attribute`

#### Symfony Serializer
- `ArchivedMovie`, `ArchivedSeries`: `Symfony\Component\Serializer\Annotation\Groups` → `Symfony\Component\Serializer\Attribute\Groups`

#### HTTP Foundation
- `SeriesTitleFactory`, `MovieTitleFactory`: `$request->get()` → `$request->query->get()` (`Request::get()` removed in Symfony 8)

---

### Test Fixes

- `SeriesTitleFactoryTest`: replaced mocked `Request` with `Request::create()` (mock bypassed constructor leaving `$query` uninitialised); added `MockInterface` intersection type to `$requestStack`
- `TvdbAuthClientTest`: added `MockInterface` intersection type to `$client`
- `TvdbApiTokenCacheTest`: added `MockInterface` intersection types to `$cache` and `$tvdbClient`
- `NextUpHelperTest`: added `MockInterface` intersection type to `$series`; renamed data provider key `recentlyWatched` → `watched` to match method parameter name (PHPUnit deprecation)

---

### Test Results
- **101 tests, 806 assertions — all passing, zero deprecations**
