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
import { WpbbAjaxObj } from './wpbbAjaxObj'
import ShareBracketModal from './modals/dashboard/brackets/ShareBracketModal'
import DeleteBracketModal from './modals/dashboard/brackets/DeleteBracketModal'
import { PublishBracketModal } from './modals/dashboard/brackets/PublishBracketModal'

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
// Try to get the wpbb_ajax_obj from the global scope. If it exists, then we know we are rendering in wordpress.
if (window.hasOwnProperty('wpbb_ajax_obj')) {
  const ajaxObj: WpbbAjaxObj = camelCaseKeys(wpbb_ajax_obj)
  console.log('ajaxObj', ajaxObj)
  initializeSentry(ajaxObj)
  renderProductPreview(ajaxObj)
  renderBracketBuilder(ajaxObj)
  renderPlayBracket(ajaxObj)
  renderBracketResultsBuilder(ajaxObj)
  renderViewBracketPlay(ajaxObj)
  renderMyBracketsModals(ajaxObj)
  renderBustBracketPlay(ajaxObj)
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
  const { bracket, redirectUrl, cssUrl, userDisplayName } = ajaxObj
  if (bracket) {
    renderDiv(
      <App>
        <PlayBracketPage bracket={bracket} apparelUrl={redirectUrl} />
      </App>,
      'wpbb-play-bracket'
    )
  }
}

function renderBracketResultsBuilder(ajaxObj: WpbbAjaxObj) {
  const builderDiv = document.getElementById('wpbb-bracket-results-builder')
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
        <ViewPlayPage bracketPlay={play} apparelUrl={redirectUrl} />
      </App>,
      'wpbb-view-play'
    )
  }
}
function renderBustBracketPlay(ajaxObj: WpbbAjaxObj) {
  const { play, redirectUrl, thumbnailUrl, authorDisplayName } = ajaxObj
  if (play) {
    renderDiv(
      <App>
        <BustPlayPage
          bracketPlay={play}
          redirectUrl={redirectUrl}
          thumbnailUrl={thumbnailUrl}
          authorDisplayName={authorDisplayName}
        />
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

  console.log('print bracket play')
  renderDiv(
    <App>
      <PrintPlayPage />
    </App>,
    'wpbb-print-play'
  )
}
// This renders the image gallery on the bracket product preview page
function renderProductPreview(ajaxObj: WpbbAjaxObj) {
  renderDiv(
    <App>
      <Gallery
        overlayThemeMap={ajaxObj.bracketUrlThemeMap}
        galleryImages={ajaxObj.galleryImages}
        colorOptions={ajaxObj.colorOptions}
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
function renderDiv(element: React.FunctionComponentElement<any>, id: string) {
  const div = document.getElementById(id)
  if (div) {
    render(element, div)
  }
}
