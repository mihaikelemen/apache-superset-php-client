<?php

declare(strict_types=1);

namespace Superset\Service\Component;

use Superset\Dto\Dashboard;
use Superset\Exception\UnexpectedRuntimeException;
use Superset\Http\Contracts\HttpClientInterface;
use Superset\Http\UrlBuilder;
use Superset\Serializer\SerializerService;

final readonly class DashboardService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private UrlBuilder $urlBuilder,
        private SerializerService $serializer,
    ) {
        $this->httpClient->addDefaultHeader('Referer', $this->urlBuilder->baseUrl);
    }

    public function get(int|string $identity): Dashboard
    {
        $url = $this->urlBuilder->build("dashboard/{$identity}");
        $response = $this->httpClient->get($url);

        if (!isset($response['result']) || !\is_array($response['result'])) {
            throw new UnexpectedRuntimeException("Dashboard data not found in response for dashboard identifier '{$identity}'");
        }

        /** @var array<string, mixed> $result */
        $result = $response['result'];

        return $this->serializer->hydrate($result, Dashboard::class);
    }

    public function uuid(int|string $identity): string
    {
        $url = $this->urlBuilder->build("dashboard/{$identity}/embedded");
        $response = $this->httpClient->get($url);

        if (!isset($response['result']) || !\is_array($response['result']) || !isset($response['result']['uuid']) || !\is_string($response['result']['uuid'])) {
            throw new UnexpectedRuntimeException("Dashboard UUID not found in response for dashboard identifier '{$identity}'");
        }

        return $response['result']['uuid'];
    }

    /**
     * @return Dashboard[]
     */
    public function list(?string $tag = null, ?bool $onlyPublished = null): array
    {
        $url = $this->urlBuilder->build('dashboard');
        $dashboards = $this->httpClient->get(
            $url,
            $this->createFilteredParams($tag, $onlyPublished)
        )['result'] ?? [];

        if (empty($dashboards)) {
            return [];
        }

        if (!\is_array($dashboards)) {
            throw new UnexpectedRuntimeException('Invalid dashboards data format received from API');
        }

        $result = [];
        foreach ($dashboards as $dashboard) {
            if (\is_array($dashboard)) {
                /** @var array<string, mixed> $dashboard */
                $result[] = $this->serializer->hydrate($dashboard, Dashboard::class);
            }
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function createFilteredParams(?string $tag = null, ?bool $onlyPublished = null): array
    {
        $params = [];

        if (null !== $tag) {
            $params['q'] = \json_encode([
                'filters' => [
                    [
                        'col' => 'tags',
                        'opr' => 'dashboard_tags',
                        'value' => $tag,
                    ],
                ],
            ]);
        }

        if (null !== $onlyPublished) {
            $params['published'] = $onlyPublished ? 'true' : 'false';
        }

        return $params;
    }
}
