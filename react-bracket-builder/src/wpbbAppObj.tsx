import { camelCaseKeys } from './brackets/shared/api/bracketApi'
import { BracketRes, PlayRes } from './brackets/shared/api/types/bracket'
import { OverlayUrlThemeMap } from './preview/Gallery'

declare var wpbb_app_obj: any

export interface wpbbAppObj {
  myBracketsUrl: string
  myPlayHistoryUrl: string
  bracketBuilderUrl: string
  userCanShareBracket: boolean
  userCanPlayPaidBracketForFree: boolean
  upgradeAccountUrl: string
  bracketProductArchiveUrl: string
  nonce: string
  rest_url: string
  sentryEnv: string
  sentryDsn: string
  bracket: BracketRes
  play: PlayRes
  isUserLoggedIn: boolean
  stripePublishableKey: string
}

export interface WpbbBracketProductPreviewObj {
  bracketUrlThemeMap: OverlayUrlThemeMap
  galleryImages: any
  colorOptions: any
}

export function getAppObj(): wpbbAppObj | null {
  if (window.hasOwnProperty('wpbb_app_obj')) {
    const ajaxObj: wpbbAppObj = camelCaseKeys(wpbb_app_obj)
    return ajaxObj
  }
  return null
}
