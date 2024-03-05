import { BracketRes, PlayRes } from '../brackets/shared/api/types/bracket'
import { OverlayUrlThemeMap } from '../preview/Gallery'
import { camelCaseKeys } from '../brackets/shared/utils/caseUtils'

declare var wpbb_app_obj: any
declare var wpbb_bracket_product_preview_obj: any

export interface WpbbAppObj {
  dashboardUrl?: string
  myPlayHistoryUrl?: string
  bracketBuilderUrl?: string
  userCanShareBracket?: boolean
  userCanPlayPaidBracketForFree?: boolean
  upgradeAccountUrl?: string
  bracketProductArchiveUrl?: string
  nonce?: string
  rest_url?: string
  sentryEnv?: string
  sentryDsn?: string
  bracket?: BracketRes
  play?: PlayRes
  isUserLoggedIn?: boolean
  stripePublishableKey?: string
  applicationFeePercentage?: number
  applicationFeeMinimum?: number
}

export interface WpbbBracketProductPreviewObj {
  bracketUrlThemeMap?: OverlayUrlThemeMap
  galleryImages: any
  colorOptions: any
}

class WpbbAjax {
  private appObj: WpbbAppObj
  private previewObj: WpbbBracketProductPreviewObj

  constructor() {
    this.appObj = window.hasOwnProperty('wpbb_app_obj')
      ? camelCaseKeys(wpbb_app_obj)
      : {}
    this.previewObj =
      typeof wpbb_bracket_product_preview_obj !== 'undefined'
        ? camelCaseKeys(wpbb_bracket_product_preview_obj)
        : {}

    // parseFloats for number fields
    if (this.appObj.applicationFeePercentage) {
      this.appObj.applicationFeePercentage = parseFloat(
        this.appObj.applicationFeePercentage.toString()
      )
    }
    if (this.appObj.applicationFeeMinimum) {
      this.appObj.applicationFeeMinimum = parseFloat(
        this.appObj.applicationFeeMinimum.toString()
      )
    }
  }

  public getAppObj(): WpbbAppObj {
    return this.appObj
  }

  public getPreviewObj(): WpbbBracketProductPreviewObj {
    return this.previewObj
  }
}

export const wpbbAjax = new WpbbAjax()
