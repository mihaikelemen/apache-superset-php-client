<?php

declare(strict_types=1);

use Superset\Config\HttpClientConfig;
use Superset\Config\LoggerConfig;
use Superset\Service\LoggerService;
use Superset\SupersetFactory;

require_once __DIR__ . '/vendor/autoload.php';

const SUPERSET_CLIENT = 'https://superset-domain.example.com';
const SUPERSET_USERNAME = 'YOUR_USERNAME_HERE';
const SUPERSET_PASSWORD = 'YOUR_PASSWORD_HERE';

$loggerConfig = new LoggerConfig(
    logPath: 'YOUR_MONOLOG_LOG_FILE.log',
);

$supersetClient = SupersetFactory::createWithHttpClientConfig(
    httpConfig: new HttpClientConfig(
        baseUrl: SUPERSET_CLIENT,
        debug: fopen('YOUR_GUZZLE_LOG_FILE.log', 'a'),
    ),
    logger: (new LoggerService($loggerConfig))->get()
);

$supersetClient->auth()->authenticate(
    SUPERSET_USERNAME,
    SUPERSET_PASSWORD
);

$dashboards = $supersetClient->dashboard()->list();

$select = [];

foreach ($dashboards as $dashboard) {
    try {
        $select[] = [
            'title' => $dashboard->title,
            'id' => (string) $dashboard->id,
            'uuid' => $supersetClient->dashboard()->uuid((string) $dashboard->id),
        ];
    } catch (Throwable $e) {
        continue;
    }
}

$uuid = $_GET['uuid'] ?? reset($select)['uuid'] ?? null;
$supersetClient->auth()->requestCsrfToken();
$guestToken = $supersetClient->auth()->createGuestToken([], ['dashboard' => $uuid]);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apache Superset dashboard(s) embed example</title>
    <style>
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --color-primary: #20a7c9;
            --color-bg: #f5f5f5;
            --color-surface: #fff;
            --color-text-primary: #333;
            --color-text-secondary: #555;
            --color-text-muted: #999;
            --color-error-bg: #fee;
            --color-error-border: #fcc;
            --color-error-text: #c33;
            --color-border: #e0e0e0;
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.1);
            --radius-md: 8px;
            --radius-sm: 6px;
            --spacing-md: 20px;
            --spacing-sm: 15px;
            --font-system: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }

        body {
            font-family: var(--font-system);
            background-color: var(--color-bg);
            padding: var(--spacing-md);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            background: var(--color-surface);
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--spacing-md);
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: var(--spacing-sm);
            color: var(--color-text-primary);
        }

        .dashboard-selector {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
        }

        .dashboard-selector label {
            font-weight: 600;
            color: var(--color-text-secondary);
        }

        .dashboard-selector select {
            flex: 1;
            padding: 10px var(--spacing-sm);
            font-size: 16px;
            border: 2px solid var(--color-border);
            border-radius: var(--radius-sm);
            background-color: var(--color-surface);
            cursor: pointer;
            transition: border-color 0.3s ease;
        }

        .dashboard-selector select:is(:hover, :focus) {
            border-color: var(--color-primary);
        }

        .dashboard-selector select:focus {
            outline: none;
        }

        .embed-container {
            background: var(--color-surface);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            padding: var(--spacing-md);
            min-height: 100px;
        }

        #superset-embed,
        #superset-embed iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 800px;
            color: var(--color-text-muted);
            font-size: 18px;
        }

        .error {
            padding: var(--spacing-md);
            background-color: var(--color-error-bg);
            border: 1px solid var(--color-error-border);
            border-radius: var(--radius-sm);
            color: var(--color-error-text);
            margin-top: var(--spacing-md);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Apache Superset dashboards</h1>
            <div class="dashboard-selector">
                <label for="dashboard-select">Select dashboard:</label>
                <select id="dashboard-select" onchange="onDashboardChange(this.value)">
                    <?php foreach ($select as $item) { ?>
                        <option 
                            value="<?php echo $item['uuid']; ?>"
                            <?php echo $item['uuid'] === $uuid ? 'selected' : ''; ?>
                        >
                            <?php echo $item['title'] ?? "Dashboard #{$item['id']}"; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="embed-container">
            <div id="superset-embed">
                <div class="loading">Loading dashboard...</div>
            </div>
        </div>
    </div>

    <!-- Superset Embedded SDK -->
    <script src="https://unpkg.com/@superset-ui/embedded-sdk"></script>
    
    <script>
        const SUPERSET_DOMAIN = '<?php echo SUPERSET_CLIENT; ?>';
        const UUID = '<?php echo $uuid; ?>';
        const GUEST_TOKEN = '<?php echo $guestToken; ?>';

        function onDashboardChange(uuid) {
            if (uuid) {
                const url = new URL(window.location.href);
                url.searchParams.set('uuid', uuid);
                window.location.href = url.toString();
            }
        }

        document.querySelector('#superset-embed').style.height = `calc(100vh - ${document.querySelector('.header').offsetHeight + 100}px)`;

        // Embed the dashboard
        async function embedDashboard() {
            try {
                await supersetEmbeddedSdk.embedDashboard({
                    id: UUID,
                    supersetDomain: SUPERSET_DOMAIN,
                    mountPoint: document.getElementById('superset-embed'),
                    fetchGuestToken: () => GUEST_TOKEN,
                    dashboardUiConfig: {
                        hideTitle: false,
                        hideChartControls: false,
                        hideTab: false,
                    },
                });
            } catch (error) {
                document.getElementById('superset-embed').innerHTML = 
                    `<div class="error">
                        <strong>Error loading dashboard:</strong><br>
                        ${error.message || 'Unknown error occurred'}
                    </div>`;
            }
        }

        if (UUID && GUEST_TOKEN) {
            embedDashboard();
        } else {
            document.getElementById('superset-embed').innerHTML = 
                '<div class="error">No dashboard selected or authentication failed.</div>';
        }
    </script>
</body>
</html>