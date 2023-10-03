// require('../../../public/js/wp-bracket-builder-public.js')
require('./wp-bracket-builder-public.js')
import React from 'react'
import App from './App'
// import { render, hydrate } from '@wordpress/element';
import { render, hydrate } from 'react-dom'
import * as Sentry from '@sentry/react'
import { OverlayUrlThemeMap } from './preview/Gallery'
// import { BracketRes } from './brackets/shared/api/types/bracket';
import { bracketBuilderStore } from './brackets/shared/app/store'
import { Provider } from 'react-redux'
import { camelCaseKeys } from './brackets/shared/api/bracketApi'
import withMatchTree from './brackets/shared/components/HigherOrder/WithMatchTree'
import { CreateTournamentButtonAndModal } from './modals/CreateTournamentButtonAndModal'
import { CreateTemplateButtonAndModal } from './modals/CreateTemplateButtonAndModal'
import { MyTemplatesModal } from './modals/DashboardTemplates/MyTemplatesModal'
/**
 * Import the stylesheet for the plugin.
 */
import './styles/main.css'

interface WpbbAjaxObj {
  page: string
  nonce: string
  rest_url: string
  tournament: any
  template: any
  play: any
  bracketUrlThemeMap: OverlayUrlThemeMap
  cssUrl: string
  redirectUrl: string
  galleryImages: any
  colorOptions: any
  sentryEnv: string
  sentryDsn: string
  post: any
  myTemplatesUrl: string
  myTournamentsUrl: string
  bracketTemplateBuilderUrl: string
  userCanCreateTournament: boolean
  homeUrl: string
  printOptions: PrintOptions
}

interface PrintOptions {
  theme: string
  position: string
  inchHeight: number
  inchWidth: number
}

// Dynamically render components to avoid loading unused modules
const Settings = React.lazy(
  () => import('./brackets/AdminTemplateBuilder/Settings')
)
const PlayTournamentBuilder = React.lazy(
  () =>
    import(
      './brackets/BracketBuilders/PlayTournamentBuilder/PlayTournamentPage'
    )
)
const Gallery = React.lazy(() => import('./preview/Gallery'))
const TemplateBuilder = React.lazy(
  () => import('./brackets/BracketBuilders/TemplateBuilder/TemplateBuilder')
)
const TournamentResultsBuilder = React.lazy(
  () =>
    import(
      './brackets/BracketBuilders/TournamentResultsBuilder/TournamentResultsBuilder'
    )
)
const ViewPlayPage = React.lazy(
  () => import('./brackets/BracketBuilders/ViewPlayPage/ViewPlayPage')
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
  renderSettings(ajaxObj)
  renderPreview(ajaxObj)
  renderPlayTournamentBuilder(ajaxObj)
  renderTemplateBuilder(ajaxObj)
  renderPlayTemplate(ajaxObj)
  renderTournamentResultsBuilder(ajaxObj)
  renderViewBracketPlay(ajaxObj)
  renderCreateTournamentModal(ajaxObj)
  loadMyTemplatesModal(ajaxObj)
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

/**
 * This renders the bracket builder admin page. DEPRECATED
 */
function renderSettings(ajaxObj: WpbbAjaxObj) {
  const page = ajaxObj.page

  if (page === 'settings') {
    // Render the App component into the DOM
    render(
      <App>
        <Settings />
      </App>,
      document.getElementById('wpbb-admin-panel')
    )
  }
}

/**
 * This renders the create template builder page
 */
function renderTemplateBuilder(ajaxObj: WpbbAjaxObj) {
  const templateBuilder = document.getElementById('wpbb-template-builder')
  const { myTemplatesUrl, myTournamentsUrl, template } = ajaxObj

  const temp = camelCaseKeys(template)

  if (templateBuilder) {
    console.log('rendering template builder')
    render(
      <App>
        <TemplateBuilder
          template={temp}
          saveTemplateLink={myTemplatesUrl}
          saveTournamentLink={myTournamentsUrl}
        />
      </App>,
      templateBuilder
    )
  }
}

/**
 * This renders the play template page
 */
function renderPlayTemplate(ajaxObj: WpbbAjaxObj) {
  const builderDiv = document.getElementById('wpbb-play-template')
  const { template, redirectUrl, cssUrl } = ajaxObj

  const temp = camelCaseKeys(template)

  if (builderDiv && temp) {
    console.log('rendering play template')
    render(
      <App>
        <Provider store={bracketBuilderStore}>
          <PlayTournamentBuilder
            bracketStylesheetUrl={cssUrl}
            template={temp}
            apparelUrl={redirectUrl}
          />
        </Provider>
      </App>,
      builderDiv
    )
  }
}

/**
 * This renders the play tournament page
 */
function renderPlayTournamentBuilder(ajaxObj: WpbbAjaxObj) {
  const builderDiv = document.getElementById('wpbb-play-tournament-builder')
  const { tournament, redirectUrl, cssUrl } = ajaxObj

  const tourney = camelCaseKeys(tournament)

  if (builderDiv && tournament) {
    console.log('rendering play tournament builder')
    render(
      <App>
        <PlayTournamentBuilder
          bracketStylesheetUrl={cssUrl}
          tournament={tourney}
          apparelUrl={redirectUrl}
        />
      </App>,
      builderDiv
    )
  }
}

/**
 * This renders the update tournament results page
 */
function renderTournamentResultsBuilder(ajaxObj: WpbbAjaxObj) {
  const builderDiv = document.getElementById('wpbb-tournament-results-builder')
  const { tournament, myTournamentsUrl } = ajaxObj

  const tourney = camelCaseKeys(tournament)

  if (builderDiv && tourney) {
    const TournamentResultsBuilderWithMatchTree = withMatchTree(
      TournamentResultsBuilder
    )
    render(
      <App>
        <Provider store={bracketBuilderStore}>
          <TournamentResultsBuilderWithMatchTree
            tournament={tourney}
            myTournamentsUrl={myTournamentsUrl}
          />
        </Provider>
      </App>,
      builderDiv
    )
  }
}

function renderViewBracketPlay(ajaxObj: WpbbAjaxObj) {
  const builderDiv = document.getElementById('wpbb-view-play')
  const { play, redirectUrl } = ajaxObj

  const playObj = camelCaseKeys(play)

  if (builderDiv && playObj) {
    console.log('rendering view play')
    render(
      <App>
        <ViewPlayPage bracketPlay={playObj} apparelUrl={redirectUrl} />
      </App>,
      builderDiv
    )
  }
}

function renderPrintBracketPage() {
  const builderDiv = document.getElementById('wpbb-print-play')
  if (!builderDiv) return
  console.log('print bracket play')
  render(
    <App>
      {' '}
      <PrintPlayPage />{' '}
    </App>,
    builderDiv
  )
}

/**
 * This loads the apparel preview component for the bracket product page.
 */
function renderPreview(ajaxObj: WpbbAjaxObj) {
  const previewDiv = document.getElementById('wpbb-bracket-preview-controller')

  if (previewDiv) {
    // ---------- Start Preview Page Logic ----------------
    // There must exist an element of class 'wpbb-bracket-preview-controller' to the
    // product page for the component to be rendered.
    const { bracketUrlThemeMap, galleryImages, colorOptions } = ajaxObj
    // Render the preview component into the DOM
    // Find the location to render the gallery component, and render the gallery component.
    render(
      <App>
        <Gallery
          overlayThemeMap={bracketUrlThemeMap}
          galleryImages={galleryImages}
          colorOptions={colorOptions}
        />{' '}
      </App>,
      previewDiv
    )
  }
}
function renderCreateTournamentModal(ajaxObj: WpbbAjaxObj) {
  const div = document.getElementById('wpbb-create-tournament-button-and-modal')
  if (div) {
    const {
      myTemplatesUrl,
      bracketTemplateBuilderUrl,
      userCanCreateTournament,
      homeUrl,
    } = ajaxObj
    render(
      <CreateTournamentButtonAndModal
        myTemplatesUrl={myTemplatesUrl}
        bracketTemplateBuilderUrl={bracketTemplateBuilderUrl}
        canCreateTournament={userCanCreateTournament}
        upgradeAccountUrl={homeUrl}
      />,
      div
    )
  }
}
function loadMyTemplatesModal(ajaxObj: WpbbAjaxObj) {
  const { myTournamentsUrl } = ajaxObj

  const modalDiv = document.getElementById('wpbb-my-templates-modal')

  if (modalDiv) {
    hydrate(<MyTemplatesModal tournamentsUrl={myTournamentsUrl} />, modalDiv)
  }
}
function renderCreateTemplateModal(ajaxObj: WpbbAjaxObj) {
  const div = document.getElementById('wpbb-create-template-button-and-modal')
  const templateId = div.getAttribute('data-template-id')
  if (div) {
    const { homeUrl } = ajaxObj

    const templatePath = `/wp-json/wp-bracket-builder/v1/templates/${templateId}`
    const templateUrl = homeUrl + templatePath
    render(<CreateTemplateButtonAndModal templateUrl={templateUrl} />, div)
  }
}
