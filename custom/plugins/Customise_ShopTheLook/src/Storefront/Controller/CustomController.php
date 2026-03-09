<?php declare(strict_types=1);

namespace Customise_ShopTheLook\Storefront\Controller;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Shopware\Core\Content\Seo\SeoUrlPlaceholderHandlerInterface;
use Shopware\Core\Framework\Adapter\Twig\TemplateFinder;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Script\Execution\Hook;
use Shopware\Core\Framework\Script\Execution\ScriptExecutor;
use Shopware\Core\PlatformRequest;
use Shopware\Core\Profiling\Profiler;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Shopware\Storefront\Framework\Routing\RequestTransformer;
use Shopware\Storefront\Framework\Routing\StorefrontResponse;
use Shopware\Storefront\Framework\Twig\Extension\IconCacheTwigFilter;
use Shopware\Storefront\Page\Navigation\NavigationPageLoadedHook;
use Shopware\Storefront\Page\Navigation\NavigationPageLoaderInterface;
use Shopware\Storefront\Pagelet\Menu\Offcanvas\MenuOffcanvasPageletLoaderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Routing\Annotation\Since;
use Shopware\Storefront\Framework\Cache\Annotation\HttpCache;
use Shopware\Storefront\Pagelet\Menu\Offcanvas\MenuOffcanvasPageletLoadedHook;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */

class CustomController extends AbstractController
{
    public function __construct(
        NavigationPageLoaderInterface $navigationPageLoader
    ) {
        $this->navigationPageLoader = $navigationPageLoader;
    }

    /**
     * @Route("/navigation/{navigationId}/{imageId}",
     * name="frontend.navigation.navigationId.imageId",
     * options={"seo"="false"}, methods={"GET"}, defaults={"XmlHttpRequest"=true})
     */

    public function index(SalesChannelContext $context, Request $request): Response
    {
        $page = $this->navigationPageLoader->load($request, $context);

        $this->hook(new NavigationPageLoadedHook($page, $context));

        return $this->renderStorefront('@Storefront/storefront/page/content/index.html.twig', ['page' => $page]);
    }

    public function hook(Hook $hook): void
    {
        $this->container->get(ScriptExecutor::class)->execute($hook);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function renderStorefront(string $view, array $parameters = []): Response
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();

        if ($request === null) {
            $request = new Request();
        }

        $salesChannelContext = $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);

        /* @feature-deprecated $view will be original template in StorefrontRenderEvent from 6.5.0.0 */
        if (Feature::isActive('FEATURE_NEXT_17275')) {
            $event = new StorefrontRenderEvent($view, $parameters, $request, $salesChannelContext);
        } else {
            $inheritedView = $this->getTemplateFinder()->find($view);

            $event = new StorefrontRenderEvent($inheritedView, $parameters, $request, $salesChannelContext);
        }
        $this->container->get('event_dispatcher')->dispatch($event);

        $iconCacheEnabled = $this->getSystemConfigService()->get('core.storefrontSettings.iconCache');

        /** @deprecated tag:v6.5.0 - icon cache will be true by default. */
        if ($iconCacheEnabled || (Feature::isActive('v6.5.0.0') && $iconCacheEnabled === null)) {
            IconCacheTwigFilter::enable();
        }

        $response = Profiler::trace('twig-rendering', function () use ($view, $event) {
            return $this->render($view, $event->getParameters(), new StorefrontResponse());
        });

        /** @deprecated tag:v6.5.0 - icon cache will be true by default. */
        if ($iconCacheEnabled || (Feature::isActive('v6.5.0.0') && $iconCacheEnabled === null)) {
            IconCacheTwigFilter::disable();
        }

        if (!$response instanceof StorefrontResponse) {
            throw new \RuntimeException('Symfony render implementation changed. Providing a response is no longer supported');
        }

        $host = $request->attributes->get(RequestTransformer::STOREFRONT_URL);

        $seoUrlReplacer = $this->container->get(SeoUrlPlaceholderHandlerInterface::class);
        $content = $response->getContent();
        if ($content !== false) {
            $response->setContent(
                $seoUrlReplacer->replace($content, $host, $salesChannelContext)
            );
        }

        $response->setData($parameters);
        $response->setContext($salesChannelContext);
        $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, '1');
        $response->headers->set('Content-Type', 'text/html');

        return $response;
    }

    public function getTemplateFinder(): TemplateFinder
    {
        return $this->container->get(TemplateFinder::class);
    }

    public function getSystemConfigService(): SystemConfigService
    {
        return $this->container->get(SystemConfigService::class);
    }
}