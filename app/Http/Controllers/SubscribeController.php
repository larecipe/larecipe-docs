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
 * It never fails with a 500: a missing SDK or API key, an unreachable API or
 * a rejected address each come back as one plain sentence.
 */
final class SubscribeController
{
    private const NOT_CONNECTED = 'The newsletter is not taking sign-ups yet. Watch the GitHub repository for releases in the meantime.';

    private const UNAVAILABLE = 'The newsletter could not be reached. Please try again in a few minutes.';

    public function __invoke(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:254'],
            'list' => ['nullable', 'string', 'max:64'],
            'source' => ['nullable', 'string', 'max:255'],
        ], [
            'email.required' => 'Enter your email address.',
            'email.email' => 'That does not look like an email address.',
        ]);

        [$subscribed, $message, $field] = $this->subscribe($data['email']);

        if ($request->expectsJson()) {
            return $subscribed
                ? response()->json(['subscribed' => true])
                : response()->json([
                    'subscribed' => false,
                    'message' => $message,
                    'errors' => $field ? [$field => [$message]] : null,
                ], $field ? 422 : 503);
        }

        return $subscribed
            ? redirect()->route('newsletter')->with('subscribed', $data['email'])
            : redirect()->route('newsletter')->withInput()->withErrors(['email' => $message]);
    }

    /** @return array{bool, ?string, ?string} subscribed, a message for the reader, the field it is about */
    private function subscribe(string $email): array
    {
        if (! class_exists(Nashra::class) || blank(config('nashra.api_key'))) {
            return [false, self::NOT_CONNECTED, null];
        }

        try {
            Nashra::subscribers()->create(['email' => $email]);

            return [true, null, null];
        } catch (ValidationException $exception) {
            return [false, $exception->errors()['email'][0] ?? 'That email address was not accepted.', 'email'];
        } catch (RateLimitException) {
            return [false, 'A lot of people are signing up right now. Please try again in a minute.', null];
        } catch (AuthenticationException|AuthorizationException $exception) {
            report($exception);

            return [false, self::NOT_CONNECTED, null];
        } catch (Throwable $exception) {
            report($exception);

            return [false, self::UNAVAILABLE, null];
        }
    }
}
