import { OverlayUrlThemeMap } from './preview/Gallery'

export interface WpbbAjaxObj {
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
  thumbnailUrl: string
  celebrityDisplayName: string
  authorDisplayName: string
  tournamentTitle: string
}
interface PrintOptions {
  theme: string
  position: string
  inchHeight: number
  inchWidth: number
}
