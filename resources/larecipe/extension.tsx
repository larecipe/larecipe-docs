import { defineExtension } from '@larecipe/ui'
import { Subscribe } from './Subscribe'
import './subscribe.css'

// Our LaRecipe extension: the React side of the components this site adds.
// Each one is also defined in PHP (AppServiceProvider) with its props, its
// server fallback and its Markdown form.
export default defineExtension({ components: { Subscribe } })
