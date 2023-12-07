// import { HostBracketModal } from './modals/dashboard/brackets/HostBracketModal'
import React from 'react'
import App from './App'
import { render } from 'react-dom'
import * as Sentry from '@sentry/react'
import { camelCaseKeys } from './brackets/shared/api/bracketApi'
import './styles/main.css'
import { EditBracketModal } from './modals/dashboard/brackets/EditBracketModal'
import { wpbbAppObj, WpbbBracketProductPreviewObj } from './wpbbAppObj'
import ShareBracketModal from './modals/dashboard/brackets/ShareBracketModal'
import DeleteBracketModal from './modals/dashboard/brackets/DeleteBracketModal'
import { PublishBracketModal } from './modals/dashboard/brackets/PublishBracketModal'
import { unpublishBracketHandler } from './handlers/dashboard/brackets/unpublishBracketHandler'
import { insertLeaderboardTeamName } from './elements/leaderboard/insertTeamName'
import EnableUpcomingNotificationModal from './modals/dashboard/brackets/EnableUpcomingNotificationModal'
import DisableUpcomingNotificationModal from './modals/dashboard/brackets/DisableUpcomingNotificationModal'

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

declare var wpbb_app_obj: any
declare var wpbb_bracket_product_preview_obj: any
// Try to get the wpbb_app_obj from the global scope. If it exists, then we know we are rendering in wordpress.
if (window.hasOwnProperty('wpbb_app_obj')) {
  const ajaxObj: wpbbAppObj = camelCaseKeys(wpbb_app_obj)
  initializeSentry(ajaxObj)
  renderProductPreview(ajaxObj)
  renderBracketBuilder(ajaxObj)
  renderPlayBracket(ajaxObj)
  renderBracketResultsBuilder(ajaxObj)
  renderViewBracketPlay(ajaxObj)
  renderMyBracketsModals(ajaxObj)
  renderBustBracketPlay(ajaxObj)
  renderPublicBracketsModals(ajaxObj)
  addClickHandlers(ajaxObj)
  insertElements(ajaxObj)
} else {
  renderPrintBracketPage()
}

function initializeSentry(ajaxObj: wpbbAppObj) {
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
function renderBracketBuilder(ajaxObj: wpbbAppObj) {
  const { myBracketsUrl, bracket } = ajaxObj
  renderDiv(
    <App>
      <BracketBuilder bracket={bracket} saveBracketLink={myBracketsUrl} />
    </App>,
    'wpbb-bracket-builder'
  )
}

function renderPlayBracket(ajaxObj: wpbbAppObj) {
  const { bracket } = ajaxObj
  const redirectUrl = ajaxObj.bracketProductArchiveUrl
  if (bracket) {
    renderDiv(
      <App>
        <PlayBracketPage bracket={bracket} redirectUrl={redirectUrl} />
      </App>,
      'wpbb-play-bracket'
    )
  }
}

function renderBracketResultsBuilder(ajaxObj: wpbbAppObj) {
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
function renderViewBracketPlay(ajaxObj: wpbbAppObj) {
  const { play, bracketProductArchiveUrl } = ajaxObj
  if (play) {
    renderDiv(
      <App>
        <ViewPlayPage
          bracketPlay={play}
          addToApparelUrl={bracketProductArchiveUrl}
        />
      </App>,
      'wpbb-view-play'
    )
  }
}
function renderBustBracketPlay(ajaxObj: wpbbAppObj) {
  const { play, bracketProductArchiveUrl, myPlayHistoryUrl } = ajaxObj
  if (play) {
    renderDiv(
      <App>
        <BustPlayPage
          bracketPlay={play}
          addApparelUrl={bracketProductArchiveUrl}
          myPlayHistoryUrl={myPlayHistoryUrl}
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

  renderDiv(
    <App>
      <PrintPlayPage />
    </App>,
    'wpbb-print-play'
  )
}
// This renders the image gallery on the bracket product preview page
function renderProductPreview(ajaxObj: wpbbAppObj) {
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
function renderMyBracketsModals(ajaxObj: wpbbAppObj) {
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
function renderPublicBracketsModals(ajaxObj: wpbbAppObj) {
  renderDiv(
    <>
      <EnableUpcomingNotificationModal />
      <DisableUpcomingNotificationModal />
    </>,
    'wpbb-public-bracket-modals'
  )
}
function addClickHandlers(ajaxObj: wpbbAppObj) {
  unpublishBracketHandler()
}

function insertElements(ajaxObj: wpbbAppObj) {
  insertLeaderboardTeamName(ajaxObj)
}

function renderDiv(element: React.FunctionComponentElement<any>, id: string) {
  const div = document.getElementById(id)
  if (div) {
    render(element, div)
  }
}
