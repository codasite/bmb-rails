// import { HostBracketModal } from './modals/dashboard/brackets/HostBracketModal'
import React from 'react'
import App from './App'
import { render } from 'react-dom'
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
import { insertLeaderboardTeamName } from './elements/leaderboard/insertTeamName'
import { UpcomingNotificationModal } from './modals/dashboard/brackets/UpcomingNotificationModal'
import { paymentsHandler } from './handlers/dashboard/payments/paymentsHandler'
import { SetTournamentFeeModal } from './modals/dashboard/brackets/SetTournamentFeeModal'
import { LockLiveTournamentModal } from './modals/dashboard/brackets/LockLiveTournamentModal'
import CompleteRoundModal from './features/VotingBracket/CompleteRoundModal'
import mergePicksFromPlayAndResults from './features/VotingBracket/mergePicksFromPlayAndResults'
import { getBracketMeta } from './brackets/shared/components/Bracket/utils'
import { MatchTree } from './brackets/shared/models/MatchTree'
import { PlayStorage } from './brackets/shared/storages/PlayStorage'
import { BracketMetaContext } from './brackets/shared/context/context'

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

const GoLivePage = React.lazy(() => import('./features/GoLive/GoLivePage'))
const ViewBracketMPP = React.lazy(
  () => import('./features/MostPopularPicks/ViewBracketMPP')
)

// Try to get the wpbb_app_obj from the global scope. If it exists, then we know we are rendering in wordpress.
const appObj = wpbbAjax.getAppObj()
if (Object.keys(appObj).length !== 0) {
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
  renderGoLivePage(appObj)
  renderMostPopularPicks(appObj)
  addClickHandlers(appObj)
  insertElements(appObj)
} else {
  renderPrintBracketPage()
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
    play,
    bracketProductArchiveUrl,
    myPlayHistoryUrl,
    isUserLoggedIn,
    userCanPlayBracketForFree,
  } = appObj
  if (bracket) {
    const playStorage = new PlayStorage('loadStoredPicks', 'wpbb_play_data_')
    const meta = getBracketMeta(bracket)
    let tree: MatchTree
    const storedPlay = playStorage.loadPlay(bracket.id)
    if (storedPlay) {
      tree = MatchTree.fromPicks(bracket, storedPlay.picks)
    } else if (bracket.isVoting) {
      tree = MatchTree.fromPicks(
        bracket,
        mergePicksFromPlayAndResults(bracket.results, play)
      )
    } else if (play) {
      tree = MatchTree.fromPicks(bracket, play.picks)
    } else {
      tree = MatchTree.fromMatchRes(bracket)
    }

    renderDiv(
      <App>
        <BracketMetaContext.Provider value={meta}>
          <PlayBuilderPage
            matchTree={tree}
            bracketMeta={meta}
            bracket={bracket}
            play={play}
            bracketProductArchiveUrl={bracketProductArchiveUrl}
            myPlayHistoryUrl={myPlayHistoryUrl}
            isUserLoggedIn={isUserLoggedIn}
            userCanPlayBracketForFree={userCanPlayBracketForFree}
            loginUrl={appObj.loginUrl}
          />
        </BracketMetaContext.Provider>
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
function renderProductPreview(_appObj: WpbbAppObj) {
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
      <UpcomingNotificationModal
        isUserLoggedIn={appObj.isUserLoggedIn}
        loginUrl={appObj.loginUrl}
      />
      <SetTournamentFeeModal
        applicationFeeMinimum={appObj.applicationFeeMinimum}
        applicationFeePercentage={appObj.applicationFeePercentage}
      />
      <LockLiveTournamentModal />
      <CompleteRoundModal />
    </>,
    'wpbb-tournaments-modals'
  )
}
function renderPublicBracketsModals(appObj: WpbbAppObj) {
  renderDiv(
    <>
      <EditBracketModal />
      <UpcomingNotificationModal
        isUserLoggedIn={appObj.isUserLoggedIn}
        loginUrl={appObj.loginUrl}
      />
      <ShareBracketModal />
      <LockLiveTournamentModal />
    </>,
    'wpbb-public-bracket-modals'
  )
}

function renderStripeOnboardingRedirect(_appObj: WpbbAppObj) {
  renderDiv(
    <App>
      <StripeOnboardingRedirect />
    </App>,
    'wpbb-stripe-onboarding-redirect'
  )
}

function renderGoLivePage(appObj: WpbbAppObj) {
  renderDiv(
    <App>
      <GoLivePage
        bracket={appObj.bracket}
        applicationFeeMinimum={appObj.applicationFeeMinimum}
        applicationFeePercentage={appObj.applicationFeePercentage}
      />
    </App>,
    'wpbb-go-live'
  )
}

function renderMostPopularPicks(appObj: WpbbAppObj) {
  renderDiv(
    <App>
      <ViewBracketMPP bracket={appObj.bracket} />
    </App>,
    'wpbb-most-popular-picks'
  )
}

function addClickHandlers(_appObj: WpbbAppObj) {
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
