import { BracketRes } from '../../brackets/shared'
import { PageProps } from './PageRouter/types'

export interface GoLivePageProps {
  bracket: BracketRes
  setBracket?: (bracket: BracketRes) => void
  applicationFeeMinimum: number
  applicationFeePercentage: number
  showProgress?: boolean
}

export interface GoLiveSubPageProps extends GoLivePageProps, PageProps {}
