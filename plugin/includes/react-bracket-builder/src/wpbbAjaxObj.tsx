import { OverlayUrlThemeMap } from './preview/Gallery'

export interface WpbbAjaxObj {
  page: string
  nonce: string
  rest_url: string
  bracket: any
  play: any
  bracketUrlThemeMap: OverlayUrlThemeMap
  cssUrl: string
  redirectUrl: string
  galleryImages: any
  colorOptions: any
  sentryEnv: string
  sentryDsn: string
  post: any
  myBracketsUrl: string
  bracketBuilderUrl: string
  userCanShareBracket: boolean
  homeUrl: string
  printOptions: PrintOptions
  thumbnailUrl: string
  authorDisplayName: string
  userDisplayName: string
  bracketTitle: string
  upgradeAccountUrl: string
}
interface PrintOptions {
  theme: string
  position: string
  inchHeight: number
  inchWidth: number
}
