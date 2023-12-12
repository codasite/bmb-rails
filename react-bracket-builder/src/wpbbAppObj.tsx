import { BracketRes, PlayRes } from './brackets/shared/api/types/bracket'
import { OverlayUrlThemeMap } from './preview/Gallery'

export interface wpbbAppObj {
  myBracketsUrl: string
  myPlayHistoryUrl: string
  bracketBuilderUrl: string
  userCanShareBracket: boolean
  upgradeAccountUrl: string
  bracketProductArchiveUrl: string
  nonce: string
  rest_url: string
  sentryEnv: string
  sentryDsn: string
  bracket: BracketRes
  play: PlayRes
  isUserLoggedIn: boolean
}

export interface WpbbBracketProductPreviewObj {
  bracketUrlThemeMap: OverlayUrlThemeMap
  galleryImages: any
  colorOptions: any
}
