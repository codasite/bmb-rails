// import { HostBracketModal } from './modals/dashboard/brackets/HostBracketModal'
import React from 'react'
import App from './App'
import { render } from 'react-dom'
import * as Sentry from '@sentry/react'
import './styles/main.css'
import { EditBracketModal } from './modals/dashboard/brackets/EditBracketModal'
import {
  wpbbAjax,
  WpbbAppObj,
  WpbbBracketProductPreviewObj,
} from './utils/WpbbAjax'
import ShareBracketModal from './modals/dashboard/brackets/ShareBracketModal'
import DeleteBracketModal from './modals/dashboard/brackets/DeleteBracketModal'
import { PublishBracketModal } from './modals/dashboard/brackets/PublishBracketModal'
import { unpublishBracketHandler } from './handlers/dashboard/brackets/unpublishBracketHandler'
import { insertLeaderboardTeamName } from './elements/leaderboard/insertTeamName'
import { UpcomingNotificationModal } from './modals/dashboard/brackets/UpcomingNotificationModal'
import { paymentsHandler } from './handlers/dashboard/payments/paymentsHandler'
import { SetTournamentFeeModal } from './modals/dashboard/brackets/SetTournamentFeeModal'

declare var wp: any, tailwind: any
tailwind.config = require('../tailwind.config.js')
tailwind.config.corePlugins.preflight = typeof wp === 'undefined'
// Dynamically render components to avoid loading unused modules
const PlayBuilderPage = React.lazy(
  () => import('./brackets/BracketBuilders/PlayBracketBuilder/PlayBuilderPage')
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
const ViewBracketResultsPage = React.lazy(
  () =>
    import(
      './brackets/BracketBuilders/ViewBracketResultsPage/ViewBracketResultsPage'
    )
)
const ViewPlayRouter = React.lazy(
  () => import('./brackets/BracketBuilders/ViewPlayPage/ViewPlayRouter')
)
const BustPlayPage = React.lazy(
  () => import('./brackets/BracketBuilders/BustPlayPage/BustPlayPage')
)
const PrintPlayPage = React.lazy(
  () => import('./brackets/BracketBuilders/PrintPlayPage/PrintPlayPage')
)

const StripeOnboardingRedirect = React.lazy(
  () => import('./redirects/StripeOnboardingRedirect')
)

// Try to get the wpbb_app_obj from the global scope. If it exists, then we know we are rendering in wordpress.
const appObj = wpbbAjax.getAppObj()
if (Object.keys(appObj).length !== 0) {
  initializeSentry(appObj)
  renderProductPreview(appObj)
  renderBracketBuilder(appObj)
  renderPlayBracket(appObj)
  renderUpdateBracketResultsPage(appObj)
  renderViewBracketResultsPage(appObj)
  renderViewBracketPlay(appObj)
  renderMyBracketsModals(appObj)
  renderBustBracketPlay(appObj)
  renderPublicBracketsModals(appObj)
  renderStripeOnboardingRedirect(appObj)
  addClickHandlers(appObj)
  insertElements(appObj)
} else {
  renderPrintBracketPage()
}

function initializeSentry(appObj: WpbbAppObj) {
  const { sentryEnv, sentryDsn } = appObj
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
function renderBracketBuilder(appObj: WpbbAppObj) {
  const { bracket } = appObj
  renderDiv(
    <App>
      <BracketBuilder bracket={bracket} />
    </App>,
    'wpbb-bracket-builder'
  )
}

function renderPlayBracket(appObj: WpbbAppObj) {
  const {
    bracket,
    bracketProductArchiveUrl,
    myPlayHistoryUrl,
    isUserLoggedIn,
    userCanPlayPaidBracketForFree,
  } = appObj
  if (bracket) {
    renderDiv(
      <App>
        <PlayBuilderPage
          bracket={bracket}
          bracketProductArchiveUrl={bracketProductArchiveUrl}
          myPlayHistoryUrl={myPlayHistoryUrl}
          isUserLoggedIn={isUserLoggedIn}
          userCanPlayPaidBracketForFree={userCanPlayPaidBracketForFree}
        />
      </App>,
      'wpbb-play-bracket'
    )
  }
}

function renderUpdateBracketResultsPage(appObj: WpbbAppObj) {
  if (appObj.bracket) {
    renderDiv(
      <App>
        <BracketResultsBuilder bracket={appObj.bracket} />
      </App>,
      'wpbb-update-bracket-results'
    )
  }
}

function renderViewBracketResultsPage(appObj: WpbbAppObj) {
  if (appObj.bracket) {
    renderDiv(
      <App>
        <ViewBracketResultsPage bracket={appObj.bracket} />
      </App>,
      'wpbb-view-bracket-results'
    )
  }
}
function renderViewBracketPlay(appObj: WpbbAppObj) {
  const { play, bracketProductArchiveUrl } = appObj
  if (play) {
    renderDiv(
      <App>
        <ViewPlayRouter
          bracketPlay={play}
          addToApparelUrl={bracketProductArchiveUrl}
        />
      </App>,
      'wpbb-view-play'
    )
  }
}
function renderBustBracketPlay(appObj: WpbbAppObj) {
  const { play, bracketProductArchiveUrl, myPlayHistoryUrl } = appObj
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
function renderProductPreview(appObj: WpbbAppObj) {
  const previewObj: WpbbBracketProductPreviewObj = wpbbAjax.getPreviewObj()
  if (
    !previewObj ||
    !previewObj.bracketUrlThemeMap ||
    !previewObj.galleryImages ||
    !previewObj.colorOptions
  ) {
    return
  }
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
function renderMyBracketsModals(appObj: WpbbAppObj) {
  renderDiv(
    <>
      <EditBracketModal />
      <ShareBracketModal />
      <DeleteBracketModal />
      <PublishBracketModal
        upgradeAccountUrl={appObj.upgradeAccountUrl}
        canCreateBracket={appObj.userCanShareBracket}
      />
      <UpcomingNotificationModal isUserLoggedIn={appObj.isUserLoggedIn} />
      <SetTournamentFeeModal
        applicationFeeMinimum={appObj.applicationFeeMinimum}
        applicationFeePercentage={appObj.applicationFeePercentage}
      />
    </>,
    'wpbb-tournaments-modals'
  )
}
function renderPublicBracketsModals(appObj: WpbbAppObj) {
  renderDiv(
    <>
      <UpcomingNotificationModal isUserLoggedIn={appObj.isUserLoggedIn} />
      <ShareBracketModal />
    </>,
    'wpbb-public-bracket-modals'
  )
}

function renderStripeOnboardingRedirect(appObj: WpbbAppObj) {
  renderDiv(
    <App>
      <StripeOnboardingRedirect />
    </App>,
    'wpbb-stripe-onboarding-redirect'
  )
}
function addClickHandlers(appObj: WpbbAppObj) {
  unpublishBracketHandler()
  paymentsHandler()
}

function insertElements(appObj: WpbbAppObj) {
  insertLeaderboardTeamName(appObj)
}

function renderDiv(element: React.FunctionComponentElement<any>, id: string) {
  const div = document.getElementById(id)
  if (!div) {
    return
  }
  render(element, div)
}
