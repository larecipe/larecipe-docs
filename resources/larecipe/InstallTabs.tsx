import { useId, useLayoutEffect, useRef, useState, type KeyboardEvent, type ReactNode } from 'react'
import { Icon, useLaRecipe } from '@larecipe/ui'

/** The commands, the same in every language. */
const COMMANDS = ['composer require larecipe/larecipe', 'php artisan larecipe:install']

/** The component's words in each language the site serves (lang/{en,ar}/install.php on the server). */
const STRINGS = {
  en: {
    label: 'How to install',
    agentTab: 'With your AI agent',
    manualTab: 'Manual',
    agentLead: 'Paste this prompt into Claude Code, Codex, Cursor or any coding agent, inside your Laravel app.',
    promptLabel: 'Prompt',
    copyPrompt: 'Copy prompt',
    copy: 'Copy',
    copied: 'Copied',
    prompt: [
      'Install LaRecipe in this Laravel app:',
      '',
      '1. Run `composer require larecipe/larecipe`.',
      '2. Run `php artisan larecipe:install --no-interaction` to keep the defaults.',
      '3. Run `php artisan larecipe:doctor` and fix anything it reports.',
      '4. Read the app and write starter docs with the larecipe-write-docs skill.',
      '',
      'Then tell me the URL of the docs site.',
    ].join('\n'),
    manualLead: 'Run this in your Laravel app.',
    steps: ['Paste it in your terminal', 'Press Enter for the defaults', 'Open `/docs`'],
  },
  ar: {
    label: 'طريقة التثبيت',
    agentTab: 'عبر وكيل البرمجة',
    manualTab: 'يدويًا',
    agentLead: 'الصق هذا الطلب في Claude Code أو Codex أو Cursor أو أي وكيل برمجة، داخل تطبيق Laravel الخاص بك.',
    promptLabel: 'الطلب',
    copyPrompt: 'انسخ الطلب',
    copy: 'نسخ',
    copied: 'نُسخ',
    prompt: [
      'ثبّت LaRecipe في تطبيق Laravel هذا:',
      '',
      '1. شغّل `composer require larecipe/larecipe`.',
      '2. شغّل `php artisan larecipe:install --no-interaction` لتبقى القيم الافتراضية.',
      '3. شغّل `php artisan larecipe:doctor` وأصلح كل ما يذكره.',
      '4. اقرأ التطبيق واكتب توثيقًا أوليًا بمهارة larecipe-write-docs.',
      '',
      'ثم أخبرني برابط موقع التوثيق.',
    ].join('\n'),
    manualLead: 'شغّل هذا في تطبيق Laravel الخاص بك.',
    steps: ['الصقه في الطرفية', 'اضغط Enter للقيم الافتراضية', 'افتح `/docs`'],
  },
}

type Tab = 'agent' | 'manual'
const TABS: Tab[] = ['agent', 'manual']

async function copyText(text: string): Promise<boolean> {
  try {
    await navigator.clipboard.writeText(text)
    return true
  } catch {
    const area = document.createElement('textarea')
    area.value = text
    area.setAttribute('readonly', '')
    area.style.position = 'fixed'
    area.style.opacity = '0'
    document.body.appendChild(area)
    area.select()
    const copied = document.execCommand('copy')
    area.remove()
    return copied
  }
}

/** A copy action that flips to "Copied" for 1.2 s and tells screen readers. */
function useCopy(announce: (message: string) => void, copiedText: string) {
  const [copied, setCopied] = useState(false)
  const timer = useRef<number | undefined>(undefined)

  return [
    copied,
    async (text: string) => {
      if (!(await copyText(text))) return
      setCopied(true)
      announce(copiedText)
      window.clearTimeout(timer.current)
      timer.current = window.setTimeout(() => setCopied(false), 1200)
    },
  ] as const
}

/** Text as it reads: `code` spans set in mono, everything else as typed. */
function promptText(text: string): ReactNode[] {
  return text.split(/(`[^`]+`)/).map((part, index) => (part.startsWith('`') ? <code key={index}>{part.slice(1, -1)}</code> : part))
}

/** The prompt's paragraphs (split at blank lines), so they space like prose instead of empty lines. */
function promptParagraphs(prompt: string): ReactNode[] {
  return prompt.split('\n\n').map((paragraph, index) => (
    <div key={index} className="lr-install-prompt-para">
      {promptText(paragraph)}
    </div>
  ))
}

/** The AI mark: a four point star that breathes and a small one that twinkles (still under reduced motion). */
export function AgentMark() {
  return (
    <svg className="lr-install-mark" width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
      <path className="lr-install-mark-star" d="M10 3.5c.5 3.6 2.4 5.5 6 6-3.6.5-5.5 2.4-6 6-.5-3.6-2.4-5.5-6-6 3.6-.5 5.5-2.4 6-6Z" />
      <path className="lr-install-mark-spark" d="M18 14c.25 1.8 1.2 2.75 3 3-1.8.25-2.75 1.2-3 3-.25-1.8-1.2-2.75-3-3 1.8-.25 2.75-1.2 3-3Z" />
      <circle className="lr-install-mark-dot" cx="18.5" cy="4.5" r="1.25" />
    </svg>
  )
}

/**
 * The Introduction's install box: one tab hands an AI coding agent a ready
 * prompt, the other gives the two commands to run by hand.
 *
 * Both panels share one grid cell, and the box eases to the open panel's
 * height when the reader switches tabs. The markup matches the server fallback in
 * resources/views/docs/install-tabs.blade.php, which shows both panels when
 * there is no JavaScript.
 */
export function InstallTabs() {
  const { locale } = useLaRecipe()
  const lang = locale.code === 'ar' ? 'ar' : 'en'
  const t = STRINGS[lang]
  const id = useId()
  const [active, setActive] = useState<Tab>('agent')
  const [announcement, setAnnouncement] = useState('')
  const [promptCopied, copyPrompt] = useCopy(setAnnouncement, t.copied)
  const [commandsCopied, copyCommands] = useCopy(setAnnouncement, t.copied)
  const tabs = useRef<Record<Tab, HTMLButtonElement | null>>({ agent: null, manual: null })
  const panels = useRef<HTMLDivElement>(null)
  const [height, setHeight] = useState<number | undefined>(undefined)

  // Follow the open panel's height (it changes with the width, too).
  useLayoutEffect(() => {
    const open = panels.current?.querySelector<HTMLElement>(`[data-panel="${active}"]`)
    if (!open) return
    const measure = () => setHeight(open.offsetHeight)
    measure()
    const observer = new ResizeObserver(measure)
    observer.observe(open)
    return () => observer.disconnect()
  }, [active])

  function onKeyDown(event: KeyboardEvent) {
    const forward = locale.dir === 'rtl' ? 'ArrowLeft' : 'ArrowRight'
    const backward = locale.dir === 'rtl' ? 'ArrowRight' : 'ArrowLeft'
    const index = TABS.indexOf(active)
    const next =
      event.key === forward ? TABS[(index + 1) % TABS.length]
      : event.key === backward ? TABS[(index - 1 + TABS.length) % TABS.length]
      : event.key === 'Home' ? TABS[0]
      : event.key === 'End' ? TABS[TABS.length - 1]
      : null
    if (!next) return
    event.preventDefault()
    setActive(next)
    tabs.current[next]?.focus()
  }

  const tab = (name: Tab, icon: ReactNode, label: string) => (
    <button
      ref={(element) => {
        tabs.current[name] = element
      }}
      type="button"
      role="tab"
      id={`${id}-${name}-tab`}
      className="lr-install-tab"
      data-tab={name}
      aria-selected={active === name}
      aria-controls={`${id}-${name}`}
      tabIndex={active === name ? 0 : -1}
      onClick={() => setActive(name)}
    >
      {icon}
      <span>{label}</span>
    </button>
  )

  const panel = (name: Tab, children: ReactNode) => (
    <div
      role="tabpanel"
      id={`${id}-${name}`}
      className="lr-install-panel"
      data-panel={name}
      data-active={active === name || undefined}
      aria-labelledby={`${id}-${name}-tab`}
      inert={active !== name}
    >
      {children}
    </div>
  )

  return (
    <div className="lr-install" data-lr-install="react">
      <div className="lr-install-tabs lr-js-only" role="tablist" aria-label={t.label} onKeyDown={onKeyDown}>
        {tab('agent', <AgentMark />, t.agentTab)}
        {tab('manual', <Icon name="Terminal" size={15} />, t.manualTab)}
      </div>
      <div className="lr-install-panels" ref={panels} style={height === undefined ? undefined : { height }}>
        {panel(
          'agent',
          <>
            <p className="lr-install-panel-label">{t.agentTab}</p>
            <p className="lr-install-lead">{t.agentLead}</p>
            <div className="lr-install-prompt">
              <div className="lr-install-prompt-head">
                <span className="lr-install-prompt-label">{t.promptLabel}</span>
                <button type="button" className="lr-button lr-button-primary lr-install-copy lr-js-only" data-copied={promptCopied || undefined} onClick={() => copyPrompt(t.prompt)}>
                  <Icon name={promptCopied ? 'Check' : 'Copy'} size={14} />
                  <span>{promptCopied ? t.copied : t.copyPrompt}</span>
                </button>
              </div>
              <div className="lr-install-prompt-text">{promptParagraphs(t.prompt)}</div>
            </div>
          </>,
        )}
        {panel(
          'manual',
          <>
            <p className="lr-install-panel-label">{t.manualTab}</p>
            <p className="lr-install-lead">{t.manualLead}</p>
            <figure className="lr-code" data-language="bash">
              <figcaption className="lr-code-header">
                <span className="lr-code-title">bash</span>
                <span className="lr-code-actions">
                  <button type="button" className="lr-code-copy lr-js-only" aria-label={commandsCopied ? t.copied : t.copy} data-copied={commandsCopied || undefined} onClick={() => copyCommands(COMMANDS.join('\n'))}>
                    <Icon name={commandsCopied ? 'Check' : 'Copy'} size={13} />
                    <span aria-hidden="true">{commandsCopied ? t.copied : t.copy}</span>
                  </button>
                </span>
              </figcaption>
              <pre className="lr-pre" tabIndex={0}>
                <code>
                  <span className="lr-line" data-line="1">
                    <span className="tok-function">composer</span> <span className="tok-string">require</span> <span className="tok-string">larecipe/larecipe</span>
                  </span>
                  {'\n'}
                  <span className="lr-line" data-line="2">
                    <span className="tok-function">php</span> <span className="tok-string">artisan</span> <span className="tok-string">larecipe:install</span>
                  </span>
                </code>
              </pre>
            </figure>
            <ol className="lr-install-steps">
              {t.steps.map((step) => (
                <li key={step}>
                  <span>{promptText(step)}</span>
                </li>
              ))}
            </ol>
          </>,
        )}
      </div>
      <span className="lr-sr" aria-live="polite">
        {announcement}
      </span>
    </div>
  )
}
