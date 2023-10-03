import { HostTournamentModal } from './modals/dashboard/templates/HostTournamentModal'
import React from 'react'
import App from './App'
import { render } from 'react-dom'
import * as Sentry from '@sentry/react'
import { bracketBuilderStore } from './brackets/shared/app/store'
import { Provider } from 'react-redux'
import { camelCaseKeys } from './brackets/shared/api/bracketApi'
import withMatchTree from './brackets/shared/components/HigherOrder/WithMatchTree'
import { CreateTournamentModal } from './modals/dashboard/tournaments/CreateTournamentModal'
import './styles/main.css'
import { EditTemplateModal } from './modals/dashboard/templates/EditTemplateModal'
import { WpbbAjaxObj } from './wpbbAjaxObj'

require('./wp-bracket-builder-public.js')
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
  renderMyTemplatesModals(ajaxObj)
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
  renderDiv(
    <App>
      <PrintPlayPage />
    </App>,
    'wpbb-print-play'
  )
}
function renderPreview(ajaxObj: WpbbAjaxObj) {
  renderDiv(
    <App>
      <Gallery
        overlayThemeMap={ajaxObj.bracketUrlThemeMap}
        galleryImages={ajaxObj.galleryImages}
        colorOptions={ajaxObj.colorOptions}
      />{' '}
    </App>,
    'wpbb-bracket-preview-controller'
  )
}
function renderCreateTournamentModal(ajaxObj: WpbbAjaxObj) {
  renderDiv(
    <CreateTournamentModal
      myTemplatesUrl={ajaxObj.myTemplatesUrl}
      bracketTemplateBuilderUrl={ajaxObj.bracketTemplateBuilderUrl}
      canCreateTournament={ajaxObj.userCanCreateTournament}
      upgradeAccountUrl={ajaxObj.homeUrl}
    />,
    'wpbb-create-tournament-modal'
  )
}
function renderMyTemplatesModals(ajaxObj: WpbbAjaxObj) {
  renderDiv(
    <>
      <HostTournamentModal tournamentsUrl={ajaxObj.myTournamentsUrl} />
      <EditTemplateModal />
    </>,
    'wpbb-my-templates-modals'
  )
}
function renderDiv(element: React.FunctionComponentElement<any>, id: string) {
  const div = document.getElementById(id)
  if (div) {
    render(element, div)
  }
}
