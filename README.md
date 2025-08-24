# Pest v4 Browser Testing Deprecation Issue

This repository demonstrates a deprecation warning that occurs when using **Pest v4 browser testing** with the `visit()` function.

## What I Want to Fix

**Pest browser testing** - specifically eliminating deprecation warnings when using `visit()` and other browser testing functions in Pest v4.

## What Seems Broken

**The `amphp/websocket-client` package** - it uses a deprecated League URI method that triggers warnings during browser tests.

## Problem Description

When running Pest browser tests that use `visit()`, you get this deprecation warning:

```
Method League\Uri\Http::createFromString() is deprecated since league/uri:7.0.0, use League\Uri\Http::new() instead
```

**Example test that triggers the warning:**
```php
test('browser', function () {
    visit('https://www.google.com')
        ->assertSee('Google');
});
```

## Environment

### PHP Version
- **PHP**: 8.4.8 (cli) (built: Jun 3 2025 16:29:26) (NTS)
- **Zend Engine**: v4.4.8
- **Zend OPcache**: v8.4.8

### Key Package Versions
- **Pest**: v4.0.2
- **Pest Browser Plugin**: v4.0.2
- **League URI**: v7.5.1
- **AMPHP WebSocket Client**: v2.0.1
- **PHPUnit**: v12.3.5

### Complete Package List
```
amphp/amp                           3.1.0
amphp/byte-stream                   2.1.2
amphp/cache                         2.0.1
amphp/dns                           2.4.0
amphp/hpack                         3.2.1
amphp/http                          2.1.2
amphp/http-client                   5.3.4
amphp/http-server                   3.4.3
amphp/parser                        1.1.1
amphp/pipeline                      1.2.3
amphp/process                       2.0.3
amphp/serialization                 1.0.0
amphp/socket                        2.3.1
amphp/sync                          2.3.0
amphp/websocket                     2.0.4
amphp/websocket-client              2.0.1
brianium/paratest                   7.11.2
daverandom/libdns                   2.1.0
doctrine/deprecations               1.1.5
fidry/cpu-core-counter              1.3.0
filp/whoops                         2.18.4
jean85/pretty-package-versions      2.1.1
kelunik/certificate                 1.1.3
league/uri                          7.5.1
league/uri-components               7.5.1
league/uri-interfaces               7.5.0
myclabs/deep-copy                   1.13.4
nikic/php-parser                    5.6.1
nunomaduro/collision                8.8.2
nunomaduro/termwind                 2.3.1
pestphp/pest                        4.0.2
pestphp/pest-plugin                 4.0.0
pestphp/pest-plugin-arch            4.0.0
pestphp/pest-plugin-browser         4.0.2
pestphp/pest-plugin-mutate          4.0.1
pestphp/pest-plugin-profanity       4.0.1
phar-io/manifest                    2.0.4
phar-io/version                     3.2.1
phpdocumentor/reflection-common     2.2.0
phpdocumentor/reflection-docblock   5.6.3
phpdocumentor/type-resolver         1.10.0
phpstan/phpdoc-parser               2.2.0
phpunit/php-code-coverage           12.3.2
phpunit/php-file-iterator           6.0.0
phpunit/php-invoker                 6.0.0
phpunit/php-text-template           5.0.0
phpunit/php-timer                   8.0.0
phpunit/phpunit                     12.3.5
psr/container                       2.0.2
psr/http-factory                    1.1.0
psr/http-message                    2.0
psr/log                             3.0.2
psr/simple-cache                    3.0.0
revolt/event-loop                   1.0.7
sebastian/cli-parser                4.0.0
sebastian/comparator                7.1.3
sebastian/complexity                5.0.0
sebastian/diff                      7.0.0
sebastian/environment               8.0.3
sebastian/exporter                  7.0.0
sebastian/global-state              8.0.0
sebastian/lines-of-code             4.0.0
sebastian/object-enumerator         7.0.0
sebastian/object-reflector          5.0.0
sebastian/recursion-context         7.0.1
sebastian/type                      6.0.3
sebastian/version                   6.0.0
staabm/side-effects-detector        1.0.5
symfony/console                     7.3.2
symfony/deprecation-contracts       3.6.0
symfony/finder                      7.3.2
symfony/polyfill-ctype              1.33.0
symfony/polyfill-intl-grapheme      1.33.0
symfony/polyfill-intl-normalizer    1.33.0
symfony/polyfill-mbstring           1.33.0
symfony/process                     7.3.0
symfony/service-contracts           3.6.0
symfony/string                      7.3.2
ta-tikoma/phpunit-architecture-test 0.8.5
theseer/tokenizer                   1.2.3
webmozart/assert                    1.11.0
```

## Issue Details

The deprecation warning originates from the AMPHP WebSocket Client library (`vendor/amphp/websocket-client/src/WebsocketHandshake.php:240`), which is used internally by the Pest browser plugin. The library is still using the deprecated `League\Uri\Http::createFromString()` method instead of the newer `League\Uri\Http::new()` method.

### Stack Trace
```
at vendor/amphp/websocket-client/src/WebsocketHandshake.php:240
  236▕     {
  237▕         if (\is_string($uri)) {
  238▕             try {
  239▕                 /** @psalm-suppress DeprecatedMethod Using deprecated method to support 6.x and 7.x of league/uri */
➜ 240▕                 $uri = Uri\Http::createFromString($uri);
  241▕             } catch (\Exception $exception) {
  242▕                 throw new \ValueError('Invalid Websocket URI provided', 0, $exception);
  243▕             }
  244▕         }
```

## Reproduction

To reproduce this issue:

1. Install dependencies:
   ```bash
   composer install
   ```

2. Run the test suite with deprecation warnings displayed:
   ```bash
   ./vendor/bin/pest --display-deprecations
   ```

3. The browser test will pass, but you'll see the deprecation warning.

## Example Test

The issue occurs with any browser test that uses the `visit()` function:

```php
test('browser', function () {
    visit('https://www.google.com')
        ->assertSee('Google');
});
```

## Root Cause

The issue is in the dependency chain:
- Pest Browser Plugin depends on AMPHP WebSocket Client
- AMPHP WebSocket Client uses the deprecated `League\Uri\Http::createFromString()` method
- League URI v7.0.0+ deprecated this method in favor of `League\Uri\Http::new()`

## Current Status

As noted in the AMPHP WebSocket Client code, there's a `@psalm-suppress DeprecatedMethod` comment indicating they're aware of the deprecation but are maintaining compatibility with both league/uri 6.x and 7.x versions.

## Impact

- **Functional**: No functional impact - tests work correctly
- **Development**: Deprecation warnings clutter test output
- **Future**: The deprecated method may be removed in future versions of league/uri

## What's Actually Happening

The issue **is NOT in Pest itself** - Pest browser testing works fine. The problem is in a dependency:

**Dependency Chain:**
```
Pest v4 Browser Plugin
  └── amphp/websocket-client v2.0.1  ← THE ISSUE IS HERE
      └── Uses deprecated League\Uri\Http::createFromString()
```

When Pest browser tests run, they use websockets internally, which triggers the deprecated method.

## Current Status: Fix Available But Not Merged

### There's an Open Pull Request
- **PR #56**: [Fix League\Uri\Http deprecation warning](https://github.com/amphp/websocket-client/pull/56)
- **Status**: Open since April 10, 2025 ⏳
- **Author**: @foxycode  
- **The Fix**: Changes `Uri\Http::createFromString()` to `Uri\Http::new()`

### Why It's Not Merged Yet
**Missing piece**: The PR needs to update `composer.json` to drop league/uri 6.x support:

**Current (line 39):**
```json
"league/uri": "^6.8|^7.1"
```

**Needs to be:**
```json
"league/uri": "^7.1"  
```

This makes it a **breaking change** because `Uri\Http::new()` only exists in league/uri 7.x.

### Testing the Fix

To test if the fix works, you can check the PR branch directly in the fork:

**Repository with fix**: [foxycode/amphp-websocket-client](https://github.com/foxycode/amphp-websocket-client/tree/2.x)

The fix changes line 240 in `src/WebsocketHandshake.php` from:
```php
$uri = Uri\Http::createFromString($uri);
```
to:
```php  
$uri = Uri\Http::new($uri);
```

**Note**: The PR is waiting for upstream approval and may require dropping league/uri 6.x support (breaking change).

### Manual Verification

You can verify the fix by:
1. Checking the [commit in the PR](https://github.com/amphp/websocket-client/pull/56/commits/81d937a36d573d32314071bc95c96b0628a583d8)
2. Viewing the fixed code in [foxycode's fork](https://github.com/foxycode/amphp-websocket-client/blob/2.x/src/WebsocketHandshake.php#L240)

### Available Workarounds

1. **Wait for the official fix**: Monitor [PR #56](https://github.com/amphp/websocket-client/pull/56) for merge status
2. **Version pinning**: Pin league/uri to 6.x (not recommended for new projects)  
3. **Manual patch**: Apply the fix locally by editing `vendor/amphp/websocket-client/src/WebsocketHandshake.php:240`

## Bottom Line

✅ **Pest browser testing works perfectly** - no functional issues  
❌ **Deprecation warnings clutter test output** due to amphp/websocket-client  
🔧 **Fix exists but needs composer.json update** to be merged  
⏳ **Waiting for amphp maintainer decision** on breaking change strategy  

## What You Can Do

1. **For now**: Ignore the deprecation warnings - they don't break functionality
2. **Help fix it**: Comment on [PR #56](https://github.com/amphp/websocket-client/pull/56) about the missing composer.json change
3. **Track progress**: Watch the PR for updates on merge status

The fix is literally 2 lines of code, but requires careful coordination due to the breaking change implications.
