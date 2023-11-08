import { OverlayUrlThemeMap } from './preview/Gallery'

export interface WpbbAjaxObj {
  myBracketsUrl: string
  bracketBuilderUrl: string
  userCanShareBracket: boolean
  upgradeAccountUrl: string
  bracketProductArchiveUrl: string
  nonce: string
  rest_url: string
  sentryEnv: string
  sentryDsn: string

  bracket: any
  play: any
  redirectUrl: string
  printOptions: PrintOptions
  thumbnailUrl: string
}

export interface PrintOptions {
  theme: string
  position: string
  inchHeight: number
  inchWidth: number
}
export interface WpbbBracketProductPreviewObj {
  bracketUrlThemeMap: OverlayUrlThemeMap
  galleryImages: any
  colorOptions: any
}
