import { defineExtension } from '@larecipe/ui'
import { DocsFlow } from './DocsFlow'
import { InstallTabs } from './InstallTabs'
import { Subscribe } from './Subscribe'
import './docs-flow.css'
import './install-tabs.css'
import './subscribe.css'
// Server-only components (no React): only their styles ship here.
import './featured-on.css'
import './features.css'

// Our LaRecipe extension: the React side of the components this site adds.
// Each one is also defined in PHP (AppServiceProvider) with its props, its
// server fallback and its Markdown form.
export default defineExtension({ components: { DocsFlow, InstallTabs, Subscribe } })
