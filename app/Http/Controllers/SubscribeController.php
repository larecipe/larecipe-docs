<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Nashra\Sdk\Exceptions\AuthenticationException;
use Nashra\Sdk\Exceptions\AuthorizationException;
use Nashra\Sdk\Exceptions\RateLimitException;
use Nashra\Sdk\Exceptions\ValidationException;
use Nashra\Sdk\Facades\Nashra;
use Throwable;

/**
 * POST /newsletter/subscribe: adds a reader to the LaRecipe newsletter on Nashra.
 *
 * Called by the Subscribe component in two ways: as JSON from the React form
 * (through request() from @larecipe/ui), and as a plain form post from the
 * server fallback when JavaScript is off. A plain post lands on /newsletter,
 * which can show the outcome; the docs page itself is cached HTML and cannot.
 *
 * It answers in the page's language (English or Arabic), and it never fails
 * with a 500: a missing SDK or API key, an unreachable API or a rejected
 * address each come back as one plain sentence.
 */
final class SubscribeController
{
    public const LOCALES = ['en', 'ar'];

    public function __invoke(Request $request): JsonResponse|RedirectResponse
    {
        $locale = in_array($request->input('locale'), self::LOCALES, true) ? $request->input('locale') : 'en';

        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:254'],
            'list' => ['nullable', 'string', 'max:64'],
            'source' => ['nullable', 'string', 'max:255'],
            'locale' => ['nullable', 'string'],
        ], [
            'email.required' => __('newsletter.required', [], $locale),
            'email.email' => __('newsletter.invalid', [], $locale),
        ]);

        [$subscribed, $message, $field] = $this->subscribe($data['email'], $locale);

        if ($request->expectsJson()) {
            return $subscribed
                ? response()->json(['subscribed' => true])
                : response()->json([
                    'subscribed' => false,
                    'message' => $message,
                    'errors' => $field ? [$field => [$message]] : null,
                ], $field ? 422 : 503);
        }

        $page = redirect()->route('newsletter', $locale === 'en' ? [] : ['lang' => $locale]);

        return $subscribed
            ? $page->with('subscribed', $data['email'])
            : $page->withInput()->withErrors(['email' => $message]);
    }

    /** @return array{bool, ?string, ?string} subscribed, a message for the reader, the field it is about */
    private function subscribe(string $email, string $locale): array
    {
        if (! class_exists(Nashra::class) || blank(config('nashra.api_key'))) {
            return [false, __('newsletter.not_connected', [], $locale), null];
        }

        try {
            Nashra::subscribers()->create(['email' => $email]);

            return [true, null, null];
        } catch (ValidationException $exception) {
            return [false, $exception->errors()['email'][0] ?? __('newsletter.rejected', [], $locale), 'email'];
        } catch (RateLimitException) {
            return [false, __('newsletter.rate_limited', [], $locale), null];
        } catch (AuthenticationException|AuthorizationException $exception) {
            report($exception);

            return [false, __('newsletter.not_connected', [], $locale), null];
        } catch (Throwable $exception) {
            report($exception);

            return [false, __('newsletter.unavailable', [], $locale), null];
        }
    }
}
