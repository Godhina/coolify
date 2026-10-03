{{-- Does not extend layouts.base, which queries the database. --}}
<!DOCTYPE html>
<html data-theme="dark" class="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Database migration failed | Coolify</title>
    @vite(['resources/css/app.css'])
</head>

<body class="error-page-body text-black dark:text-inherit">
    <x-error-page code="503" title="The database migration failed."
        description="Coolify is paused until the migration succeeds. Read the full error with `docker logs coolify`, fix the cause, then run `docker restart coolify`."
        tone="danger" :show-go-back="false" :show-dashboard="false">
        <div class="error-message">{{ $error }}</div>
    </x-error-page>
</body>

</html>
