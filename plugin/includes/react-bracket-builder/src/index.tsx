// import { HostBracketModal } from './modals/dashboard/brackets/HostBracketModal'
import React from 'react'
import App from './App'
import { render } from 'react-dom'
import * as Sentry from '@sentry/react'
import { bracketBuilderStore } from './brackets/shared/app/store'
import { Provider } from 'react-redux'
import { camelCaseKeys } from './brackets/shared/api/bracketApi'
import withMatchTree from './brackets/shared/components/HigherOrder/WithMatchTree'
import './styles/main.css'
import { EditBracketModal } from './modals/dashboard/brackets/EditBracketModal'
import { WpbbAjaxObj, WpbbBracketProductPreviewObj } from './wpbbAjaxObj'
import ShareBracketModal from './modals/dashboard/brackets/ShareBracketModal'
import DeleteBracketModal from './modals/dashboard/brackets/DeleteBracketModal'
import { PublishBracketModal } from './modals/dashboard/brackets/PublishBracketModal'
import { unpublishBracketHandler } from './handlers/dashboard/brackets/unpublishBracketHandler'

declare var wp, tailwind: any
tailwind.config = require('../tailwind.config.js')
tailwind.config.corePlugins.preflight = typeof wp === 'undefined'
// Dynamically render components to avoid loading unused modules
const PlayBracketPage = React.lazy(
  () => import('./brackets/BracketBuilders/PlayBracketBuilder/PlayBracketPage')
)
const Gallery = React.lazy(() => import('./preview/Gallery'))
const BracketBuilder = React.lazy(
  () => import('./brackets/BracketBuilders/BracketBuilder/BracketBuilder')
)
const BracketResultsBuilder = React.lazy(
  () =>
    import(
      './brackets/BracketBuilders/BracketResultsBuilder/BracketResultsBuilder'
    )
)

const ViewPlayPage = React.lazy(
  () => import('./brackets/BracketBuilders/ViewPlayPage/ViewPlayPage')
)
const BustPlayPage = React.lazy(
  () => import('./brackets/BracketBuilders/BustPlayPage/BustPlayPage')
)
const PrintPlayPage = React.lazy(
  () => import('./brackets/BracketBuilders/PrintPlayPage/PrintPlayPage')
)

declare var wpbb_ajax_obj: any
declare var wpbb_bracket_product_preview_obj: any
// Try to get the wpbb_ajax_obj from the global scope. If it exists, then we know we are rendering in wordpress.
if (window.hasOwnProperty('wpbb_ajax_obj')) {
  const ajaxObj: WpbbAjaxObj = camelCaseKeys(wpbb_ajax_obj)
  initializeSentry(ajaxObj)
  renderProductPreview()
  renderBracketBuilder(ajaxObj)
  renderPlayBracket(ajaxObj)
  renderBracketResultsBuilder(ajaxObj)
  renderViewBracketPlay(ajaxObj)
  renderMyBracketsModals(ajaxObj)
  renderBustBracketPlay(ajaxObj)
  addClickHandlers(ajaxObj)
} else {
  renderPrintBracketPage()
}

function initializeSentry(ajaxObj: WpbbAjaxObj) {
  const { sentryEnv, sentryDsn } = ajaxObj
  if (sentryDsn) {
    // Init Sentry
    Sentry.init({
      environment: sentryEnv || 'production',
      dsn: sentryDsn,
      integrations: [
        new Sentry.BrowserTracing({
          // Set `tracePropagationTargets` to control for which URLs distributed tracing should be enabled
          tracePropagationTargets: [
            'localhost',
            /^https:\/\/backmybracket\.com/,
          ],
        }),
        new Sentry.Replay(),
      ],
      // Performance Monitoring
      tracesSampleRate: 1.0, // Capture 100% of the transactions, reduce in production!
      // Session Replay
      replaysSessionSampleRate: 0.1, // This sets the sample rate at 10%. You may want to change it to 100% while in development and then sample at a lower rate in production.
      replaysOnErrorSampleRate: 1.0, // If you're not already sampling the entire session, change the sample rate to 100% when sampling sessions where errors occur.
    })
  }
}
function renderBracketBuilder(ajaxObj: WpbbAjaxObj) {
  const { myBracketsUrl, bracket } = ajaxObj
  renderDiv(
    <App>
      <BracketBuilder bracket={bracket} saveBracketLink={myBracketsUrl} />
    </App>,
    'wpbb-bracket-builder'
  )
}

function renderPlayBracket(ajaxObj: WpbbAjaxObj) {
  const { bracket, redirectUrl } = ajaxObj
  if (bracket) {
    renderDiv(
      <App>
        <PlayBracketPage bracket={bracket} redirectUrl={redirectUrl} />
      </App>,
      'wpbb-play-bracket'
    )
  }
}

function renderBracketResultsBuilder(ajaxObj: WpbbAjaxObj) {
  const { bracket, myBracketsUrl } = ajaxObj

  if (bracket) {
    renderDiv(
      <App>
        <BracketResultsBuilder
          bracket={bracket}
          myBracketsUrl={myBracketsUrl}
        />
      </App>,
      'wpbb-bracket-results-builder'
    )
  }
}
function renderViewBracketPlay(ajaxObj: WpbbAjaxObj) {
  const { play, redirectUrl } = ajaxObj
  if (play) {
    renderDiv(
      <App>
        <ViewPlayPage bracketPlay={play} redirectUrl={redirectUrl} />
      </App>,
      'wpbb-view-play'
    )
  }
}
function renderBustBracketPlay(ajaxObj: WpbbAjaxObj) {
  console.log('renderBustBracketPlay')
  const { play, redirectUrl } = ajaxObj
  if (play) {
    renderDiv(
      <App>
        <BustPlayPage bracketPlay={play} redirectUrl={redirectUrl} />
      </App>,
      'wpbb-bust-play'
    )
  }
}

// This function is used to render the print bracket page outside of wordpress
function renderPrintBracketPage() {
  const builderDiv = document.getElementById('wpbb-print-play')
  if (!builderDiv) return
  const fontPath = require('./assets/fonts/ClashDisplay-Variable.woff2')
  const link = document.createElement('link')
  link.rel = 'preload'
  link.href = fontPath
  link.as = 'font'
  link.type = 'font/woff2'
  link.crossOrigin = 'anonymous'
  document.head.appendChild(link)

  renderDiv(
    <App>
      <PrintPlayPage />
    </App>,
    'wpbb-print-play'
  )
}
// This renders the image gallery on the bracket product preview page
function renderProductPreview() {
  if (typeof wpbb_bracket_product_preview_obj === 'undefined') {
    return
  }
  const previewObj: WpbbBracketProductPreviewObj = camelCaseKeys(
    wpbb_bracket_product_preview_obj
  )
  renderDiv(
    <App>
      <Gallery
        overlayThemeMap={previewObj.bracketUrlThemeMap}
        galleryImages={previewObj.galleryImages}
        colorOptions={previewObj.colorOptions}
      />
    </App>,
    'wpbb-product-preview'
  )
}
function renderMyBracketsModals(ajaxObj: WpbbAjaxObj) {
  renderDiv(
    <>
      <EditBracketModal />
      <ShareBracketModal />
      <DeleteBracketModal />
      <PublishBracketModal
        upgradeAccountUrl={ajaxObj.upgradeAccountUrl}
        canCreateBracket={ajaxObj.userCanShareBracket}
      />
    </>,
    'wpbb-my-brackets-modals'
  )
}
function addClickHandlers(ajaxObj: WpbbAjaxObj) {
  unpublishBracketHandler()
}

function renderDiv(element: React.FunctionComponentElement<any>, id: string) {
  const div = document.getElementById(id)
  if (div) {
    render(element, div)
  }
}
