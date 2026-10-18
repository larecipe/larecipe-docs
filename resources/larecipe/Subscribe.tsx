import { useState, type FormEvent } from 'react'
import { Button, Icon, Input, request, useLaRecipe } from '@larecipe/ui'

/** The form's words in each language the site serves (lang/{en,ar}/newsletter.php on the server). */
const STRINGS = {
  en: {
    label: 'Newsletter',
    title: 'Stay in the loop',
    body: 'LaRecipe release notes by email. Unsubscribe any time.',
    email: 'Email address',
    submit: 'Subscribe',
    sending: 'Subscribing',
    doneTitle: 'You are on the list',
    doneBody: (email: string) => `The next release note goes to ${email}.`,
    failed: 'That did not work. Please try again.',
    note: 'This form is a custom React component this site adds to LaRecipe.',
    noteLink: 'See how it is built',
  },
  ar: {
    label: 'النشرة البريدية',
    title: 'ابقَ على اطلاع',
    body: 'ملاحظات إصدارات LaRecipe عبر البريد الإلكتروني. يمكنك إلغاء الاشتراك في أي وقت.',
    email: 'عنوان البريد الإلكتروني',
    submit: 'اشترك',
    sending: 'جارٍ الاشتراك',
    doneTitle: 'أنت الآن في القائمة',
    doneBody: (email: string) => `ستصل ملاحظة الإصدار التالي إلى ${email}.`,
    failed: 'لم ينجح ذلك. حاول مرة أخرى.',
    note: 'هذا النموذج مكوّن React مخصّص يضيفه هذا الموقع إلى LaRecipe.',
    noteLink: 'شاهد كيف بُني',
  },
}

/** Where "See how it is built" goes: the custom components guide, in the reader's language and version. */
function guideUrl(base: string, locale: string, version: string): string {
  return `${base.replace(/\/$/, '')}${locale === 'ar' ? '/ar' : ''}/${version}/customize/custom-components`
}

/**
 * The newsletter form on every docs page (the article.after insert point) and
 * inline on the Introduction (<Subscribe />).
 *
 * React comes from the page's import map, so this shares LaRecipe's React.
 * useLaRecipe() gives the page and its locale; request() posts to our own
 * route with the session cookie and the XSRF token, and hands back Laravel's
 * validation errors. The markup matches the server fallback in
 * resources/views/docs/subscribe.blade.php.
 */
export function Subscribe({ list = 'larecipe', showcase = false }: { list?: string; showcase?: boolean }) {
  const { page, locale, boot, version } = useLaRecipe()
  const lang = locale.code === 'ar' ? 'ar' : 'en'
  const t = STRINGS[lang]
  const [email, setEmail] = useState('')
  const [status, setStatus] = useState<'idle' | 'sending' | 'done'>('idle')
  const [error, setError] = useState<string | null>(null)

  async function submit(event: FormEvent) {
    event.preventDefault()
    setStatus('sending')
    setError(null)

    const response = await request<{ subscribed: boolean }>('/newsletter/subscribe', {
      method: 'POST',
      body: { email, list, source: page.path, locale: lang },
    })

    if (response.ok) {
      setStatus('done')
      return
    }

    setError(response.errors?.email?.[0] ?? response.message ?? t.failed)
    setStatus('idle')
  }

  const note = showcase ? (
    <p className="lr-subscribe-note">
      {t.note} <a href={guideUrl(boot.urls.base, lang, version)}>{t.noteLink}</a>
    </p>
  ) : null

  const head = (title: string, body: string) => (
    <div className="lr-subscribe-head">
      <span className="lr-subscribe-icon" aria-hidden="true">
        <Icon name="Mail" size={18} />
      </span>
      <div className="lr-subscribe-copy">
        <p className="lr-subscribe-label">{t.label}</p>
        <p className="lr-subscribe-title">{title}</p>
        <p className="lr-subscribe-body">{body}</p>
      </div>
    </div>
  )

  if (status === 'done') {
    return (
      <div className="lr-subscribe-wrap">
        <div className="lr-subscribe" data-lr-subscribe="react" role="status">
          {head(t.doneTitle, t.doneBody(email))}
        </div>
        {note}
      </div>
    )
  }

  return (
    <div className="lr-subscribe-wrap">
      <form className="lr-subscribe" data-lr-subscribe="react" onSubmit={submit}>
        {head(t.title, t.body)}
        <div className="lr-subscribe-row">
          <label className="lr-subscribe-field">
            <span className="lr-sr">{t.email}</span>
            <Input
              type="email"
              name="email"
              required
              autoComplete="email"
              placeholder="you@example.com"
              dir="ltr"
              value={email}
              aria-invalid={error ? true : undefined}
              onChange={(event) => setEmail(event.target.value)}
            />
          </label>
          <Button type="submit" variant="primary" loading={status === 'sending'}>
            {status === 'sending' ? t.sending : t.submit}
          </Button>
        </div>
        {error && (
          <p className="lr-subscribe-error" role="alert">
            {error}
          </p>
        )}
      </form>
      {note}
    </div>
  )
}
