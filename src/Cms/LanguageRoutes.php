<?php

namespace Infinri\Cms;

use Infinri\Filesystem\F;

class LanguageRoutes
{
	/**
	 * Creates all multi-language routes
	 */
	public static function create(App $infinri): array
	{
		$routes = [];

		// add the route for the home page
		$routes[] = static::home($infinri);

		// Infinri's base url
		$baseurl = $infinri->url();

		foreach ($infinri->languages() as $language) {
			// ignore languages with a different base url
			if ($language->baseurl() !== $baseurl) {
				continue;
			}

			$routes[] = [
				'pattern' => $language->pattern(),
				'method'  => 'ALL',
				'env'     => 'site',
				'action'  => function ($path = null) use ($infinri, $language, $baseurl) {
					$result = $language->router()->call($path);

					// explicitly test for null as $result can
					// contain falsy values that should still be returned
					if ($result !== null) {
						return $result;
					}

					// a language without URL prefix matches any path;
					// if the path belongs to the URL prefix of another
					// language, that language stays the current one
					if ($language->path() === '') {
						$matched = $infinri->languages()
							->filter('baseurl', $baseurl)
							->findByPath($path);

						if ($matched !== null) {
							$infinri->setCurrentTranslation($matched->code());
							$infinri->setCurrentLanguage($matched->code());
						}
					}

					// jump through to the fallback if nothing
					// can be found for this language
					/** @var \Infinri\Http\Route $this */
					$this->next();
				}
			];
		}

		$routes[] = static::fallback($infinri);

		return $routes;
	}


	/**
	 * Create the fallback route
	 * for unprefixed default language URLs.
	 */
	public static function fallback(App $infinri): array
	{
		return [
			'pattern' => '(:all)',
			'method'  => 'ALL',
			'env'     => 'site',
			'action'  => function (string $path) use ($infinri) {
				// check for content representations or files
				$extension = F::extension($path);

				// try to redirect prefixed pages
				if (
					empty($extension) === true &&
					$page = $infinri->page($path)
				) {
					$url = $infinri->request()->url([
						'query'    => null,
						'params'   => null,
						'fragment' => null
					]);

					if ($url->toString() !== $page->url()) {
						// redirect to translated page directly if translation
						// is exists and languages detect is enabled
						$lang = $infinri->detectedLanguage()->code();

						if (
							$infinri->option('languages.detect') === true &&
							$page->translation($lang)->exists() === true
						) {
							return $infinri
								->response()
								->redirect($page->url($lang));
						}

						return $infinri
							->response()
							->redirect($page->url());
					}
				}

				return $infinri->language()->router()->call($path);
			}
		];
	}

	/**
	 * Create the multi-language home page route
	 */
	public static function home(App $infinri): array
	{
		// Multi-language home
		return [
			'pattern' => '',
			'method'  => 'ALL',
			'env'     => 'site',
			'action'  => function () use ($infinri) {
				// find all languages with the same base url as the current installation
				$languages = $infinri->languages()->filter(
					'baseurl',
					$infinri->url()
				);

				// if there's no language with a matching base url,
				// redirect to the default language
				if ($languages->count() === 0) {
					return $infinri
						->response()
						->redirect($infinri->defaultLanguage()->url());
				}

				// if there's just one language,
				// we take that to render the home page
				$currentLanguage = match ($languages->count()) {
					1       => $languages->first(),
					default => $infinri->defaultLanguage()
				};

				// language detection on the home page with / as URL
				if ($infinri->url() !== $currentLanguage->url()) {
					if ($infinri->option('languages.detect') === true) {
						return $infinri
							->response()
							->redirect($infinri->detectedLanguage()->url());
					}

					return $infinri
						->response()
						->redirect($currentLanguage->url());
				}

				// render the home page of the current language
				return $currentLanguage->router()->call();
			}
		];
	}
}
