# Test info

- Name: Clubs >> should display clubs page
- Location: /home/tobias/usr/nrfc/nrfc-fixtures/tests/playwright/clubs.spec.ts:8:7

# Error details

```
Error: Timed out 5000ms waiting for expect(locator).toHaveTitle(expected)

Locator: locator(':root')
Expected pattern: /Clubs/
Received string:  "No route found for \"GET http://localhost:8000/clubs\": Method Not Allowed (Allow: POST) (405 Method Not Allowed)"
Call log:
  - expect.toHaveTitle with timeout 5000ms
  - waiting for locator(':root')
    9 × locator resolved to <html lang="en" class="sf-js-enabled">…</html>
      - unexpected value "No route found for "GET http://localhost:8000/clubs": Method Not Allowed (Allow: POST) (405 Method Not Allowed)"

    at /home/tobias/usr/nrfc/nrfc-fixtures/tests/playwright/clubs.spec.ts:9:24
```

# Page snapshot

```yaml
- banner:
  - heading "Symfony Exception" [level=1]:
    - img
    - text: Symfony Exception
  - link "Symfony Docs":
    - /url: https://symfony.com/doc/7.2.5/index.html
    - img
    - text: Symfony Docs
- heading "MethodNotAllowedException MethodNotAllowedHttpException" [level=2]:
  - link "MethodNotAllowedException":
    - /url: "#trace-box-2"
  - img
  - link "MethodNotAllowedHttpException":
    - /url: "#trace-box-1"
- heading "HTTP 405 Method Not Allowed" [level=2]
- 'heading "No route found for \"GET http://localhost:8000/clubs\": Method Not Allowed (Allow: POST)" [level=1]'
- img
- tablist:
  - tab "Exceptions 2" [selected]
  - tab "Logs 1"
  - tab "Stack Traces 2"
- tabpanel:
  - img
  - heading "Symfony\\Component\\HttpKernel\\Exception\\ MethodNotAllowedHttpException" [level=3]
  - group: Show exception properties
  - img
  - text: in
  - link "vendor/symfony/http-kernel/EventListener/RouterListener.php":
    - /url: file:///home/tobias/usr/nrfc/nrfc-fixtures/vendor/symfony/http-kernel/EventListener/RouterListener.php#L153
    - text: vendor/symfony/http-kernel/EventListener/
    - strong: RouterListener.php
  - text: (line 153)
  - img
  - text: in
  - link "vendor/symfony/event-dispatcher/Debug/WrappedListener.php":
    - /url: file:///home/tobias/usr/nrfc/nrfc-fixtures/vendor/symfony/event-dispatcher/Debug/WrappedListener.php#L115
    - text: vendor/symfony/event-dispatcher/Debug/
    - strong: WrappedListener.php
  - text: "-> onKernelRequest (line 115)"
  - img
  - text: in
  - link "vendor/symfony/event-dispatcher/EventDispatcher.php":
    - /url: file:///home/tobias/usr/nrfc/nrfc-fixtures/vendor/symfony/event-dispatcher/EventDispatcher.php#L206
    - text: vendor/symfony/event-dispatcher/
    - strong: EventDispatcher.php
  - text: "-> __invoke (line 206)"
  - img
  - text: in
  - link "vendor/symfony/event-dispatcher/EventDispatcher.php":
    - /url: file:///home/tobias/usr/nrfc/nrfc-fixtures/vendor/symfony/event-dispatcher/EventDispatcher.php#L56
    - text: vendor/symfony/event-dispatcher/
    - strong: EventDispatcher.php
  - text: "-> callListeners (line 56)"
  - img
  - text: in
  - link "vendor/symfony/event-dispatcher/Debug/TraceableEventDispatcher.php":
    - /url: file:///home/tobias/usr/nrfc/nrfc-fixtures/vendor/symfony/event-dispatcher/Debug/TraceableEventDispatcher.php#L122
    - text: vendor/symfony/event-dispatcher/Debug/
    - strong: TraceableEventDispatcher.php
  - text: "-> dispatch (line 122)"
  - img
  - text: in
  - link "vendor/symfony/http-kernel/HttpKernel.php":
    - /url: file:///home/tobias/usr/nrfc/nrfc-fixtures/vendor/symfony/http-kernel/HttpKernel.php#L159
    - text: vendor/symfony/http-kernel/
    - strong: HttpKernel.php
  - text: "-> dispatch (line 159)"
  - img
  - text: in
  - link "vendor/symfony/http-kernel/HttpKernel.php":
    - /url: file:///home/tobias/usr/nrfc/nrfc-fixtures/vendor/symfony/http-kernel/HttpKernel.php#L76
    - text: vendor/symfony/http-kernel/
    - strong: HttpKernel.php
  - text: "-> handleRaw (line 76)"
  - img
  - text: in
  - link "vendor/symfony/http-kernel/Kernel.php":
    - /url: file:///home/tobias/usr/nrfc/nrfc-fixtures/vendor/symfony/http-kernel/Kernel.php#L182
    - text: vendor/symfony/http-kernel/
    - strong: Kernel.php
  - text: "-> handle (line 182)"
  - img
  - text: in
  - link "vendor/symfony/runtime/Runner/Symfony/HttpKernelRunner.php":
    - /url: file:///home/tobias/usr/nrfc/nrfc-fixtures/vendor/symfony/runtime/Runner/Symfony/HttpKernelRunner.php#L35
    - text: vendor/symfony/runtime/Runner/Symfony/
    - strong: HttpKernelRunner.php
  - text: "-> handle (line 35)"
  - img
  - text: in
  - link "vendor/autoload_runtime.php":
    - /url: file:///home/tobias/usr/nrfc/nrfc-fixtures/vendor/autoload_runtime.php#L29
    - text: vendor/
    - strong: autoload_runtime.php
  - text: "-> run (line 29)"
  - img
  - text: require_once('/home/tobias/usr/nrfc/nrfc-fixtures/vendor/autoload_runtime.php') in
  - link "public/index.php":
    - /url: file:///home/tobias/usr/nrfc/nrfc-fixtures/public/index.php#L5
    - text: public/
    - strong: index.php
  - text: (line 5)
  - list:
    - listitem:
      - code: <?php
    - listitem:
      - code
    - listitem:
      - code: use App\Kernel;
    - listitem:
      - code
    - listitem:
      - code: require_once dirname(__DIR__).'/vendor/autoload_runtime.php';
    - listitem:
      - code
    - listitem:
      - code: "return function (array $context) {"
    - listitem:
      - code: return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
    - listitem:
      - code: "};"
    - listitem:
      - code
  - img
  - heading "Symfony\\Component\\Routing\\Exception\\ MethodNotAllowedException" [level=3]
- region "Symfony Web Debug Toolbar":
  - link "405":
    - /url: http://localhost:8000/_profiler/aa0faf?panel=request
  - link "131 ms":
    - /url: http://localhost:8000/_profiler/aa0faf?panel=time
  - link "10.0 MiB":
    - /url: http://localhost:8000/_profiler/aa0faf?panel=time
  - link "Logger 1":
    - /url: http://localhost:8000/_profiler/aa0faf?panel=logger
    - img "Logger"
    - text: "1"
  - link "Security n/a":
    - /url: http://localhost:8000/_profiler/aa0faf?panel=security
    - img "Security"
    - text: n/a
  - link "Twig 1 ms":
    - /url: http://localhost:8000/_profiler/aa0faf?panel=twig
    - img "Twig"
    - text: 1 ms
  - link "Symfony 7.2.5":
    - /url: http://localhost:8000/_profiler/aa0faf?panel=config
    - img "Symfony"
    - text: 7.2.5
  - button "Close Toolbar" [expanded]
```

# Test source

```ts
   1 | import { test, expect } from '@playwright/test';
   2 |
   3 | test.describe('Clubs', () => {
   4 |   test.beforeEach(async ({ page }) => {
   5 |     await page.goto('/clubs');
   6 |   });
   7 |
   8 |   test('should display clubs page', async ({ page }) => {
>  9 |     await expect(page).toHaveTitle(/Clubs/);
     |                        ^ Error: Timed out 5000ms waiting for expect(locator).toHaveTitle(expected)
  10 |     await expect(page.locator('h1')).toContainText('Clubs');
  11 |   });
  12 |
  13 |   test('should create new club', async ({ page }) => {
  14 |     await page.click('text=New Club');
  15 |     
  16 |     // Fill in the form
  17 |     await page.fill('input[name="club[name]"]', 'Test Club');
  18 |     await page.fill('input[name="club[address]"]', '123 Test Street');
  19 |     await page.fill('input[name="club[postcode]"]', 'AB12 3CD');
  20 |     await page.fill('input[name="club[latitude]"]', '51.5074');
  21 |     await page.fill('input[name="club[longitude]"]', '-0.1278');
  22 |     
  23 |     // Submit the form
  24 |     await page.click('button[type="submit"]');
  25 |     
  26 |     // Verify redirection and success message
  27 |     await expect(page).toHaveURL(/\/clubs\/\d+/);
  28 |     await expect(page.locator('.alert-success')).toContainText('Club created successfully');
  29 |   });
  30 |
  31 |   test('should display club details with map', async ({ page }) => {
  32 |     // Navigate to a club with coordinates
  33 |     await page.goto('/clubs/1');
  34 |     
  35 |     await expect(page.locator('h1')).toContainText('Test Club');
  36 |     await expect(page.locator('#map')).toBeVisible();
  37 |   });
  38 |
  39 |   test('should display club details without map', async ({ page }) => {
  40 |     // Navigate to a club without coordinates
  41 |     await page.goto('/clubs/2');
  42 |     
  43 |     await expect(page.locator('h1')).toContainText('Test Club 2');
  44 |     await expect(page.locator('#map')).not.toBeVisible();
  45 |   });
  46 |
  47 |   test('should edit club', async ({ page }) => {
  48 |     await page.goto('/clubs/1/edit');
  49 |     
  50 |     // Update the form
  51 |     await page.fill('input[name="club[name]"]', 'Updated Club Name');
  52 |     await page.click('button[type="submit"]');
  53 |     
  54 |     // Verify redirection and success message
  55 |     await expect(page).toHaveURL(/\/clubs\/\d+/);
  56 |     await expect(page.locator('.alert-success')).toContainText('Club updated successfully');
  57 |     await expect(page.locator('h1')).toContainText('Updated Club Name');
  58 |   });
  59 | }); 
```