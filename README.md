# Pest v4 Deprecation Warning Issue

This repository demonstrates a deprecation warning that occurs when using Pest v4 with the browser plugin.

## Problem Description

When running browser tests with Pest v4, you'll encounter the following deprecation warning:

```
Method League\Uri\Http::createFromString() is deprecated since league/uri:7.0.0, use League\Uri\Http::new() instead
```

This warning appears every time you use the `visit()` function in your browser tests.

## Environment

- **Pest**: v4.0.2
- **Pest Browser Plugin**: v4.0.2
- **League URI**: v7.5.1
- **AMPHP WebSocket Client**: v2.0.1

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

## Potential Solutions

1. **Wait for upstream fix**: AMPHP WebSocket Client needs to update their code
3. **Version pinning**: Pin league/uri to 6.x (not recommended for new projects)
