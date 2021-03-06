<?php

/*
 * This file is part of the Liip/ThemeBundle
 *
 * (c) Liip AG
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Liip\ThemeBundle\Controller;

use Liip\ThemeBundle\ActiveTheme;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

/**
 * Theme controller.
 *
 * @author Gordon Franke <info@nevalon.de>
 */
class ThemeController
{
    protected $activeTheme;

    /**
     * Available themes.
     *
     * @var array
     */
    protected $themes;

    /**
     * Options of the cookie to store active theme.
     *
     * @var array|null
     */
    protected $cookieOptions;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var string|null
     */
    protected $defaultRoute;

    /**
     * Theme controller construct.
     *
     * @param ActiveTheme $activeTheme   active theme instance
     * @param array       $themes        Available themes
     * @param array|null  $cookieOptions The options of the cookie we look for the theme to set
     * @param RouterInterface $router
     * @param string|null $defaultRoute
     */
    public function __construct(ActiveTheme $activeTheme, array $themes, array $cookieOptions = null, RouterInterface $router, $defaultRoute = null)
    {
        $this->activeTheme = $activeTheme;
        $this->themes = $themes;
        $this->cookieOptions = $cookieOptions;
        $this->router = $router;
        $this->defaultRoute = $defaultRoute;
    }

    /**
     * Switch theme.
     *
     * @param Request $request actual request
     *
     * @return RedirectResponse
     *
     * @throws NotFoundHttpException when theme name not exists
     */
    public function switchAction(Request $request)
    {
        $theme = $request->get('theme');

        if (!in_array($theme, $this->themes)) {
            throw new NotFoundHttpException(sprintf('The theme "%s" does not exist', $theme));
        }

        $this->activeTheme->setName($theme);


        if ($this->defaultRoute) {
            $redirect = $this->router->generate($this->defaultRoute);
        } else {
            $redirect = '/';
        }

        $url = $request->headers->get('Referer', $redirect);

        $response = new RedirectResponse($url);

        if (!empty($this->cookieOptions)) {
            $cookie = new Cookie(
                $this->cookieOptions['name'],
                $theme,
                time() + $this->cookieOptions['lifetime'],
                $this->cookieOptions['path'],
                $this->cookieOptions['domain'],
                (bool) $this->cookieOptions['secure'],
                (bool) $this->cookieOptions['http_only']
            );

            $response->headers->setCookie($cookie);
        }

        return $response;
    }
}
