import { useEffect, useMemo, useRef, useState, type CSSProperties, type ReactNode, type RefObject } from 'react'
import { useLaRecipe } from '@larecipe/ui'

/** The diagram's words in each language the site serves (lang/{en,ar}/docs-flow.php on the server). */
const STRINGS = {
  en: {
    code: 'Your code changes',
    agent: 'Your agent updates the docs',
    agentsRead: 'Agents read',
    peopleRead: 'People read',
    stale: 'stale',
    staleWhy: 'Its source changed',
    title: 'Refunds',
    guide: 'Guide',
    api: 'API',
    text: 'Refund a paid invoice in full or in part, from the app or with one command.',
    updatedAgo: 'Updated 2m ago',
    stalePill: 'Stale',
    updating: 'Updating',
    updatedNow: 'Updated just now',
    updatedBy: 'Updated by Claude Code',
    prCode: 'code',
    prDocs: 'docs',
    open: 'Open',
    merged: 'Merged',
    connected: 'Connected via MCP',
    ago: '2m ago',
    now: 'just now',
    ask: 'Ask AI about the docs…',
    askShort: 'Ask the docs…',
    summary:
      'Your code changes, and LaRecipe finds the docs pages that list that code as their source. Your coding agent updates them before it pushes, so the code and the docs land in the same pull request and merge together. Then people read the docs site, and agents read the same pages as Markdown, through llms.txt and over MCP.',
  },
  ar: {
    code: 'يتغيّر كودك',
    agent: 'وكيلك يحدّث التوثيق',
    agentsRead: 'يقرأ الوكلاء',
    peopleRead: 'يقرأ الناس',
    stale: 'قديمة',
    staleWhy: 'تغيّر مصدرها',
    title: 'الاسترداد',
    guide: 'الدليل',
    api: 'API',
    text: 'استرد مبلغ فاتورة مدفوعة كاملًا أو جزءًا منه، من التطبيق أو بأمر واحد.',
    updatedAgo: 'حُدّث قبل دقيقتين',
    stalePill: 'قديمة',
    updating: 'يُحدَّث الآن',
    updatedNow: 'حُدّث للتو',
    updatedBy: 'حدّثه Claude Code',
    prCode: 'كود',
    prDocs: 'توثيق',
    open: 'مفتوح',
    merged: 'دُمج',
    connected: 'متصلون عبر MCP',
    ago: 'قبل دقيقتين',
    now: 'للتو',
    ask: 'اسأل الذكاء الاصطناعي…',
    askShort: 'اسأل التوثيق…',
    summary:
      'يتغيّر كودك، فيجد LaRecipe صفحات التوثيق التي تذكر ذلك الكود مصدرًا لها. ويحدّثها وكيل البرمجة قبل أن يرفع عمله، فيصل الكود والتوثيق في الـ pull request نفسه ويُدمجان معًا. ثم يقرأ الناس موقع التوثيق، ويقرأ الوكلاء الصفحات نفسها بصيغة Markdown، وعبر llms.txt وخادم MCP.',
  },
}

type Lang = keyof typeof STRINGS
type Phase = 'rest' | 'send' | 'write' | 'ship' | 'publish' | 'read'
type Target = 'text' | 'code' | 'line'
type Step = 'sweep' | 'type' | 'hold'

interface Token {
  text: string
  tone?: 'muted' | 'fn' | 'num'
}

const COMMAND: Token[] = [
  { text: 'php', tone: 'muted' },
  { text: ' artisan ' },
  { text: 'invoices:refund', tone: 'fn' },
  { text: ' ' },
  { text: '1042', tone: 'num' },
]

/**
 * One loop, in milliseconds. Each phase only sets an attribute on the figure;
 * docs-flow.css times the pulses, the sweep and the lights from it.
 *
 * send: the commit fires and the page that lists that code goes stale.
 * write: the agent rewrites the page; its commit joins the pull request.
 * ship: the pull request merges, code and docs together.
 * publish, read: the merged page reaches agents and people.
 */
const CYCLE = 9000
const AT = { send: 800, stale: 2150, write: 2400, type: 2850, typed: 4150, ship: 4550, publish: 5550, read: 6550, rest: 7850 }
const TARGETS: Target[] = ['text', 'code', 'line']

interface Frame {
  phase: Phase | null
  target: Target
  step: Step | null
  typed: number | null
  pill: 'ago' | 'stale' | 'updating' | 'now'
  docs: boolean
  pr: 'open' | 'merged'
  time: 'ago' | 'now'
}

/** The diagram at rest: exactly what the server fallback shows. */
const STILL: Frame = { phase: null, target: 'text', step: null, typed: null, pill: 'ago', docs: true, pr: 'merged', time: 'ago' }

function frameAt(t: number, cycle: number, lengths: Record<Target, number>): Frame {
  const target = TARGETS[cycle % TARGETS.length]
  const time = cycle > 0 || t >= AT.read ? 'now' : 'ago'
  const settled = cycle === 0 ? 'ago' : 'now'
  const rest: Frame = { phase: 'rest', target, step: null, typed: null, pill: t < AT.send ? settled : 'now', docs: true, pr: 'merged', time }

  if (t < AT.send || t >= AT.rest) return rest

  // A new change: the pull request opens with the code alone.
  const open: Frame = { ...rest, pr: 'open', docs: false }
  if (t < AT.write) return { ...open, phase: 'send', pill: t < AT.stale ? settled : 'stale' }
  if (t < AT.type) return { ...open, phase: 'write', step: 'sweep', pill: 'updating' }
  if (t < AT.typed) {
    const typed = lengths[target] ? Math.floor((lengths[target] * (t - AT.type)) / (AT.typed - AT.type)) : null
    return { ...open, phase: 'write', step: 'type', typed, pill: 'updating' }
  }
  if (t < AT.ship) return { ...open, phase: 'write', step: 'hold', typed: lengths[target] || null, docs: true }
  if (t < AT.publish) return { ...rest, phase: 'ship' }
  if (t < AT.read) return { ...rest, phase: 'publish' }
  return { ...rest, phase: 'read' }
}

const sameFrame = (a: Frame, b: Frame) =>
  a.phase === b.phase &&
  a.target === b.target &&
  a.step === b.step &&
  a.typed === b.typed &&
  a.pill === b.pill &&
  a.docs === b.docs &&
  a.pr === b.pr &&
  a.time === b.time

/**
 * Runs the loop while the figure is on screen, the tab is visible and the
 * reader allows motion. Off screen nothing ticks and the CSS animations pause
 * where they are, so the two stay in step.
 */
function useLoop(ref: RefObject<HTMLElement | null>, lengths: Record<Target, number>) {
  const [frame, setFrame] = useState<Frame>(STILL)
  const [live, setLive] = useState(false)

  useEffect(() => {
    const element = ref.current
    if (!element || typeof IntersectionObserver === 'undefined') return

    const motion = window.matchMedia('(prefers-reduced-motion: reduce)')
    let visible = false
    let raf = 0
    let last = 0
    let t = 0
    let cycle = 0
    let current = STILL

    const tick = (now: number) => {
      t += last ? Math.min(now - last, 100) : 0
      last = now
      if (t >= CYCLE) {
        t -= CYCLE
        cycle += 1
      }
      const next = frameAt(t, cycle, lengths)
      if (!sameFrame(next, current)) {
        current = next
        setFrame(next)
      }
      raf = requestAnimationFrame(tick)
    }

    const update = () => {
      cancelAnimationFrame(raf)
      last = 0
      if (motion.matches) {
        t = 0
        cycle = 0
        current = STILL
        setFrame(STILL)
        setLive(false)
        return
      }
      const run = visible && document.visibilityState === 'visible'
      setLive(run)
      if (run) raf = requestAnimationFrame(tick)
    }

    const observer = new IntersectionObserver(
      ([entry]) => {
        visible = entry.isIntersecting
        update()
      },
      { threshold: 0.2 },
    )
    observer.observe(element)
    motion.addEventListener('change', update)
    document.addEventListener('visibilitychange', update)

    return () => {
      cancelAnimationFrame(raf)
      observer.disconnect()
      motion.removeEventListener('change', update)
      document.removeEventListener('visibilitychange', update)
    }
  }, [ref, lengths])

  return { frame, live }
}

/** Tokens with the first `typed` characters shown and the rest kept in place, invisible, so nothing reflows. */
function Typed({ tokens, typed }: { tokens: Token[]; typed: number | null }) {
  if (typed === null) {
    return (
      <>
        {tokens.map((token, index) => (
          <span key={index} className={token.tone ? `lr-flow-${token.tone}` : undefined}>
            {token.text}
          </span>
        ))}
      </>
    )
  }

  const shown: ReactNode[] = []
  const hidden: ReactNode[] = []
  let offset = 0
  tokens.forEach((token, index) => {
    const cut = Math.max(0, Math.min(token.text.length, typed - offset))
    const tone = token.tone ? `lr-flow-${token.tone}` : undefined
    if (cut > 0) shown.push(<span key={`s${index}`} className={tone}>{token.text.slice(0, cut)}</span>)
    if (cut < token.text.length) hidden.push(<span key={`h${index}`} className={tone}>{token.text.slice(cut)}</span>)
    offset += token.text.length
  })

  return (
    <>
      {shown}
      <span className="lr-flow-caret" />
      <span className="lr-flow-ghost">{hidden}</span>
    </>
  )
}

const i = (index: number) => ({ '--i': index }) as CSSProperties

/**
 * The LaRecipe loop on the Introduction (<DocsFlow />): a commit makes the
 * docs page that lists its code stale; the coding agent rewrites that page;
 * both sit in one pull request that merges them together; agents (MCP,
 * llms.txt) and people read the result.
 *
 * The markup matches resources/views/docs/docs-flow.blade.php line for line,
 * so React mounts over the server fallback without a shift. React only adds
 * the loop: data attributes for docs-flow.css, the typed line, the pill and
 * the pull request's state. The diagram is decorative for assistive tech; a
 * sentence says the same.
 */
export function DocsFlow({ caption }: { caption?: string }) {
  const { locale } = useLaRecipe()
  const lang: Lang = locale.code === 'ar' ? 'ar' : 'en'
  const t = STRINGS[lang]
  const ref = useRef<HTMLElement>(null)
  const lengths = useLengths(t.text)
  const { frame, live } = useLoop(ref, lengths)

  const typedFor = (target: Target) => (frame.phase === 'write' && frame.target === target ? frame.typed : null)
  const pill = { ago: t.updatedAgo, stale: t.stalePill, updating: t.updating, now: t.updatedNow }[frame.pill]

  return (
    <figure
      ref={ref}
      className="lr-flow"
      data-lr-flow="react"
      data-live={live ? '' : undefined}
      data-phase={frame.phase ?? undefined}
      data-target={frame.phase ? frame.target : undefined}
      data-step={frame.step ?? undefined}
    >
      <div className="lr-flow-stage" aria-hidden="true" data-markdown-ignore="">
        <div className="lr-flow-pr" />

        <div className="lr-flow-label lr-flow-label-code">{t.code}</div>
        <div className="lr-flow-sources">
          <div className="lr-flow-card lr-flow-source" style={i(0)}>
            <CommitIcon />
            <span className="lr-flow-source-id lr-flow-mono" dir="ltr">a1f3c9e</span>
            <span className="lr-flow-source-stat" dir="ltr">
              <span className="lr-flow-add">+42</span> <span className="lr-flow-remove">−7</span>
            </span>
            <span className="lr-flow-source-title" dir="ltr">feat: refund invoices</span>
          </div>
          <div className="lr-flow-chain">
            <svg className="lr-flow-wires" width="12" height="22" viewBox="0 0 12 22">
              <Wire d="M6 0V22" index={0} />
              <Port x={6} y={0} port="src" index={0} />
              <Port x={6} y={22} port="src" index={4} />
            </svg>
          </div>
          <div className="lr-flow-card lr-flow-source lr-flow-stale" style={i(4)}>
            <StaleIcon />
            <span className="lr-flow-source-id lr-flow-mono" dir="ltr">refunds.md</span>
            <span className="lr-flow-source-stat lr-flow-word">{t.stale}</span>
            <span className="lr-flow-source-title">{t.staleWhy}</span>
          </div>
        </div>

        <div className="lr-flow-link lr-flow-link-in">
          <WiresIn />
          <VerticalWire from="src" to="hub-in" index={4} />
        </div>

        <div className="lr-flow-label lr-flow-label-agent">{t.agent}</div>
        <div className="lr-flow-card lr-flow-page">
          <div className="lr-flow-page-bar">
            <span className="lr-flow-path lr-flow-mono" dir="ltr">
              docs / billing / <b>refunds.md</b>
            </span>
          </div>
          <div className="lr-flow-page-body">
            <div className="lr-flow-page-head">
              <span className="lr-flow-page-title">{t.title}</span>
              <span className="lr-flow-pill" data-state={frame.pill}>
                <span className="lr-flow-dot" />
                <span>{pill}</span>
              </span>
            </div>
            <div className="lr-flow-tabs">
              <span className="lr-flow-tab is-active">{t.guide}</span>
              <span className="lr-flow-tab">{t.api}</span>
            </div>
            <div className="lr-flow-line lr-flow-text" data-line="text">
              <Typed tokens={[{ text: t.text }]} typed={typedFor('text')} />
            </div>
            <div className="lr-flow-code" dir="ltr">
              <span className="lr-flow-line" data-line="code">
                <Typed tokens={COMMAND} typed={typedFor('code')} />
              </span>
            </div>
            <div className="lr-flow-bars">
              <span className="lr-flow-bar" />
              <span className="lr-flow-bar" />
              <span className="lr-flow-bar" />
              <span className="lr-flow-bar" data-line="line" />
            </div>
          </div>
          <div className="lr-flow-page-foot">
            <PullRequestIcon />
            <span>
              {t.updatedBy} · <span dir="ltr">PR #412</span>
            </span>
          </div>
        </div>

        <div className="lr-flow-pr-bar">
          <PullRequestIcon />
          <span className="lr-flow-pr-id lr-flow-mono" dir="ltr">#412</span>
          <span className="lr-flow-pr-title" dir="ltr">Add invoice refunds</span>
          <span className="lr-flow-pr-files">
            <span className="lr-flow-pr-file lr-flow-word" data-state="on" style={i(0)}>
              <CheckIcon />
              {t.prCode}
            </span>
            <span className="lr-flow-pr-plus">+</span>
            <span className="lr-flow-pr-file lr-flow-word" data-state={frame.docs ? 'on' : 'off'} style={i(1)}>
              <CheckIcon />
              {t.prDocs}
            </span>
          </span>
          <span className="lr-flow-pr-state lr-flow-word" data-state={frame.pr}>
            <span className="lr-flow-dot" />
            {frame.pr === 'merged' ? t.merged : t.open}
          </span>
        </div>

        <div className="lr-flow-link lr-flow-link-out">
          <WiresOut />
          <VerticalWire from="hub-out" to="reader" index={0} />
          <VerticalWire from="hub-out" to="reader" index={1} end />
        </div>

        <div className="lr-flow-label lr-flow-label-agents">{t.agentsRead}</div>
        <div className="lr-flow-readers">
          <div className="lr-flow-card lr-flow-reader lr-flow-agents" style={i(0)}>
            <div className="lr-flow-reader-head">
              <span className="lr-flow-chips">
                <span className="lr-flow-chip" title="Claude" style={i(0)}>
                  <ClaudeGlyph />
                </span>
                <span className="lr-flow-chip" title="ChatGPT" style={i(1)}>
                  <ChatGptGlyph />
                </span>
                <span className="lr-flow-chip" title="Cursor" style={i(2)}>
                  <CursorGlyph />
                </span>
              </span>
              <span className="lr-flow-badge">
                <span className="lr-flow-dot" />
                MCP
              </span>
            </div>
            <div className="lr-flow-note">{t.connected}</div>
            <div className="lr-flow-preview">
              <span className="lr-flow-preview-head">
                <span className="lr-flow-word"># {t.title}</span>
                <span className="lr-flow-faint" dir="ltr">llms.txt</span>
              </span>
              <span className="lr-flow-bar" />
            </div>
          </div>
          <div className="lr-flow-label lr-flow-label-people">{t.peopleRead}</div>
          <div className="lr-flow-card lr-flow-reader lr-flow-people" style={i(1)}>
            <div className="lr-flow-people-head">
              <span className="lr-flow-dot" />
              <span className="lr-flow-people-title">{t.title}</span>
              <span className="lr-flow-time">{frame.time === 'now' ? t.now : t.ago}</span>
            </div>
            <div className="lr-flow-bars">
              <span className="lr-flow-bar" />
              <span className="lr-flow-bar" />
            </div>
            <div className="lr-flow-ask">
              <span className="lr-flow-ask-text">
                <span className="lr-flow-ask-long">{t.ask}</span>
                <span className="lr-flow-ask-short">{t.askShort}</span>
              </span>
              <span className="lr-flow-send">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round">
                  <path d="m5 12 7-7 7 7" />
                  <path d="M12 19V5" />
                </svg>
              </span>
            </div>
          </div>
        </div>
      </div>
      <div className="lr-sr">{t.summary}</div>
      {caption ? <figcaption className="lr-flow-caption">{caption}</figcaption> : null}
    </figure>
  )
}

/** Characters to type per target; stable across renders so the loop's effect runs once per language. */
function useLengths(text: string): Record<Target, number> {
  return useMemo(() => ({ text: text.length, code: COMMAND.reduce((sum, token) => sum + token.text.length, 0), line: 0 }), [text])
}

/* Wires: the wide layout's curves (52 by 300, see docs-flow.css), the narrow
   layout's short vertical runs (12 by 44) and the commit-to-page chain (12 by 22). */

function Port({ x, y, port, index = 0, r = 3.25 }: { x: number; y: number; port: string; index?: number; r?: number }) {
  return (
    <>
      <circle className="lr-flow-ring" data-port={port} cx={x} cy={y} r={r} style={i(index)} />
      <circle className="lr-flow-port" data-port={port} cx={x} cy={y} r={r} style={i(index)} />
    </>
  )
}

function Wire({ d, index }: { d: string; index: number }) {
  return (
    <>
      <path className="lr-flow-track" d={d} />
      <path className="lr-flow-pulse" d={d} pathLength={100} style={i(index)} />
    </>
  )
}

function WiresIn() {
  return (
    <svg className="lr-flow-wires lr-flow-wires-h" width="52" height="300" viewBox="0 0 52 300">
      <Wire d="M0 189C26 189 26 150 52 150" index={4} />
      <Port x={0} y={189} port="src" index={4} />
      <Port x={52} y={150} port="hub-in" r={3.75} />
    </svg>
  )
}

function WiresOut() {
  return (
    <svg className="lr-flow-wires lr-flow-wires-h" width="52" height="300" viewBox="0 0 52 300">
      <Wire d="M0 150C26 150 26 59 52 59" index={0} />
      <Wire d="M0 150C26 150 26 241 52 241" index={1} />
      <Port x={0} y={150} port="hub-out" r={3.75} />
      <Port x={52} y={59} port="reader" index={0} />
      <Port x={52} y={241} port="reader" index={1} />
    </svg>
  )
}

function VerticalWire({ from, to, index, end = false }: { from: string; to: string; index: number; end?: boolean }) {
  return (
    <svg className={`lr-flow-wires lr-flow-wires-v${end ? ' is-end' : ''}`} width="12" height="44" viewBox="0 0 12 44">
      <Wire d="M6 0V44" index={index} />
      <Port x={6} y={0} port={from} index={index} />
      <Port x={6} y={44} port={to} index={index} />
    </svg>
  )
}

/* Icons: Lucide geometry (ISC), drawn the same way in the Blade fallback. */

function Glyph({ children, className }: { children: ReactNode; className?: string }) {
  return (
    <svg className={className} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={1.75} strokeLinecap="round" strokeLinejoin="round">
      {children}
    </svg>
  )
}

function CommitIcon() {
  return (
    <Glyph className="lr-flow-icon">
      <circle cx="12" cy="12" r="3" />
      <path d="M3 12h6" />
      <path d="M15 12h6" />
    </Glyph>
  )
}

function StaleIcon() {
  return (
    <Glyph className="lr-flow-icon">
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
      <path d="M14 2v6h6" />
      <path d="M12 12v3" />
      <path d="M12 18h.01" />
    </Glyph>
  )
}

function PullRequestIcon() {
  return (
    <Glyph className="lr-flow-icon">
      <circle cx="18" cy="18" r="3" />
      <circle cx="6" cy="6" r="3" />
      <path d="M13 6h3a2 2 0 0 1 2 2v7" />
      <path d="M6 9v12" />
    </Glyph>
  )
}

function CheckIcon() {
  return (
    <Glyph className="lr-flow-check">
      <path d="M20 6 9 17l-5-5" />
    </Glyph>
  )
}

/* Agent marks: plain glyphs, not the products' logos. */

function ClaudeGlyph() {
  return (
    <Glyph>
      <path d="M12 4v16" />
      <path d="M4 12h16" />
      <path d="m6.3 6.3 11.4 11.4" />
      <path d="m17.7 6.3-11.4 11.4" />
    </Glyph>
  )
}

function ChatGptGlyph() {
  return (
    <Glyph>
      <path d="M12 3.5 19.4 7.75v8.5L12 20.5l-7.4-4.25v-8.5z" />
      <circle cx="12" cy="12" r="2.5" />
    </Glyph>
  )
}

function CursorGlyph() {
  return (
    <Glyph>
      <path d="M5 4.5 19 11l-6.2 1.8L11 19z" />
    </Glyph>
  )
}
