<?php

namespace Infinri\Cms;

use Exception;
use Infinri\Exception\NotFoundException;
use Infinri\Http\Router;
use Infinri\Toolkit\A;
use Infinri\Toolkit\Str;
use Infinri\Uuid\Uuid;

class LanguageRouter
{
	protected Router $router;

	/**
	 * Creates a new language router instance
	 * for the given language
	 */
	public function __construct(
		protected Language $language
	) {
	}

	/**
	 * Fetches all scoped routes for the
	 * current language from the Infinri instance
	 *
	 * @throws \Infinri\Exception\NotFoundException
	 */
	public function routes(): array
	{
		$language = $this->language;
		$infinri    = $language->infinri();
		$routes   = $infinri->routes();

		// only keep the scoped language routes
		$routes = array_values(array_filter($routes, function ($route) use ($language) {
			// no language scope
			if (empty($route['language']) === true) {
				return false;
			}

			// wildcard
			if ($route['language'] === '*') {
				return true;
			}

			// get all applicable languages
			$languages = Str::split(strtolower($route['language']), '|');

			// validate the language
			return in_array($language->code(), $languages, true) === true;
		}));

		// add the page-scope if necessary
		foreach ($routes as $index => $route) {
			if ($pageId = ($route['page'] ?? null)) {
				if ($page = $infinri->page($pageId)) {
					// convert string patterns to arrays
					$patterns = A::wrap($route['pattern']);

					// prefix all patterns with the page slug
					$patterns = A::map(
						$patterns,
						fn ($pattern) => $page->uri($language) . '/' . $pattern
					);

					// re-inject the pattern and the full page object
					$routes[$index]['pattern'] = $patterns;
					$routes[$index]['page']    = $page;
				} else {
					throw new NotFoundException(
						message: 'The page "' . $pageId . '" does not exist'
					);
				}
			}
		}

		// Language-specific UUID URLs
		$routes[] = [
			'pattern' => '@/(page|file)/(:all)',
			'method'  => 'ALL',
			'env'     => 'site',
			'action'  => function (string $languageCode, string $type, string $id) use ($infinri, $language) {
				// try to resolve to model, but only from UUID cache;
				// this ensures that only existing UUIDs can be queried
				// and attackers can't force Infinri to go through the whole
				// site index with a non-existing UUID
				if ($model = Uuid::for($type . '://' . $id)?->model(true)) {
					return $infinri
						->response()
						->redirect($model->url($language->code()));
				}

				// render the error page
				return false;
			}
		];

		return $routes;
	}

	/**
	 * Wrapper around the Router::call method
	 * that injects the Language instance and
	 * if needed also the Page as arguments.
	 */
	public function call(string|null $path = null): mixed
	{
		$language       = $this->language;
		$infinri          = $language->infinri();
		$this->router ??= new Router($this->routes());

		try {
			return $this->router->call($path, $infinri->request()->method(), function ($route) use ($infinri, $language) {
				$infinri->setCurrentTranslation($language);
				$infinri->setCurrentLanguage($language);

				if ($page = $route->page()) {
					return $route->action()->call(
						$route,
						$language,
						$page,
						...$route->arguments()
					);
				}

				return $route->action()->call(
					$route,
					$language,
					...$route->arguments()
				);
			});
		} catch (Exception) {
			return $infinri->resolve($path, $language->code());
		}
	}
}
