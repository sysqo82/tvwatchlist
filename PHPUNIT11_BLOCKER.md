# PHPUnit 11 Upgrade Blocker - PHP 8.4 Compatibility

## Issue

PHPUnit 11.5.46 has a PHP 8.4 compatibility issue with readonly class inheritance.

### Error

```
PHP Fatal error: Non-readonly class PHPUnit\Event\TestSuite\TestSuiteForTestMethodWithDataProvider 
cannot extend readonly class PHPUnit\Event\TestSuite\TestSuite 
in /var/www/html/vendor/phpunit/phpunit/src/Event/Value/TestSuite/TestSuiteForTestMethodWithDataProvider.php on line 19
```

### Root Cause

PHP 8.4 introduced stricter rules for readonly classes:
- A non-readonly class cannot extend a readonly class
- This is a breaking change from PHP 8.3
- PHPUnit 11.5.46 has classes that violate this new rule

### Current Status

- **PHP Version**: 8.4.16
- **PHPUnit Version**: 10.5.60 (downgraded from 11.5.46)
- **Blocker Status**: ACTIVE - cannot upgrade to PHPUnit 11 until fixed

### Resolution Plan

1. **Monitor PHPUnit releases** for PHP 8.4 compatibility fixes
   - Track: https://github.com/sebastianbergmann/phpunit/issues
   - Expected fix in: PHPUnit 11.6.x or later

2. **Options**:
   - **Option A**: Wait for PHPUnit fix (RECOMMENDED)
   - **Option B**: Temporarily downgrade PHP to 8.3 (not recommended)
   - **Option C**: Skip PHPUnit 11, wait for PHPUnit 12 (future)

3. **When Fixed**:
   - Update `composer.json`: `"phpunit/phpunit": "^11"`
   - Update `phpunit.xml.dist` schema to `11.4`
   - Update `SYMFONY_PHPUNIT_VERSION` to `11.4`
   - Run full test suite to verify

### Phase 2 Status

**BLOCKED** - Cannot proceed with PHPUnit 11 upgrade until upstream compatibility issue is resolved.

Phase 2 will be skipped for now and revisited when PHPUnit releases a PHP 8.4-compatible version.

### Next Steps

- Proceed with **Phase 3: Deprecation Cleanup** instead
- Revisit Phase 2 when PHPUnit 11.6+ is available with PHP 8.4 support
- Subscribe to PHPUnit release notifications

### Testing Performed

```bash
# Upgraded to PHPUnit 11
composer require --dev phpunit/phpunit:^11

# Test run failed with readonly class error
vendor/bin/phpunit

# Reverted to PHPUnit 10
composer require --dev phpunit/phpunit:^10

# Tests pass successfully
vendor/bin/phpunit # ✓ 108 tests, 869 assertions
```

### Date

January 10, 2026

### Related Issues

- PHPUnit GitHub: https://github.com/sebastianbergmann/phpunit
- PHP 8.4 Readonly Classes: https://www.php.net/releases/8.4/en.php
