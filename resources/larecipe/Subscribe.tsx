import { useState, type FormEvent } from 'react'
import { Button, Input, request, useLaRecipe } from '@larecipe/ui'

/**
 * The newsletter form on every docs page (the article.after insert point) and
 * inline on the Introduction (<Subscribe />).
 *
 * React comes from the page's import map, so this shares LaRecipe's React.
 * request() posts to our own route with the session cookie and the XSRF
 * token, and hands back Laravel's validation errors. The markup matches the
 * server fallback in resources/views/docs/subscribe.blade.php.
 */
export function Subscribe({ list = 'larecipe' }: { list?: string }) {
  const { page } = useLaRecipe()
  const [email, setEmail] = useState('')
  const [status, setStatus] = useState<'idle' | 'sending' | 'done'>('idle')
  const [error, setError] = useState<string | null>(null)

  async function submit(event: FormEvent) {
    event.preventDefault()
    setStatus('sending')
    setError(null)

    const response = await request<{ subscribed: boolean }>('/newsletter/subscribe', {
      method: 'POST',
      body: { email, list, source: page.path },
    })

    if (response.ok) {
      setStatus('done')
      return
    }

    setError(response.errors?.email?.[0] ?? response.message ?? 'That did not work. Please try again.')
    setStatus('idle')
  }

  if (status === 'done') {
    return (
      <div className="lr-subscribe" data-lr-subscribe="react" role="status">
        <div className="lr-subscribe-copy">
          <p className="lr-subscribe-label">Newsletter</p>
          <p className="lr-subscribe-title">You are on the list</p>
          <p className="lr-subscribe-body">The next release note goes to {email}.</p>
        </div>
      </div>
    )
  }

  return (
    <form className="lr-subscribe" data-lr-subscribe="react" onSubmit={submit} noValidate={false}>
      <div className="lr-subscribe-copy">
        <p className="lr-subscribe-label">Newsletter</p>
        <p className="lr-subscribe-title">Stay in the loop</p>
        <p className="lr-subscribe-body">LaRecipe release notes by email. Unsubscribe any time.</p>
      </div>
      <div className="lr-subscribe-row">
        <label className="lr-subscribe-field">
          <span className="lr-subscribe-sr">Email address</span>
          <Input
            type="email"
            name="email"
            required
            autoComplete="email"
            placeholder="you@example.com"
            value={email}
            aria-invalid={error ? true : undefined}
            onChange={(event) => setEmail(event.target.value)}
          />
        </label>
        <Button type="submit" variant="secondary" className="lr-subscribe-button" loading={status === 'sending'}>
          {status === 'sending' ? 'Subscribing' : 'Subscribe'}
        </Button>
      </div>
      {error && (
        <p className="lr-subscribe-error" role="alert">
          {error}
        </p>
      )}
    </form>
  )
}
