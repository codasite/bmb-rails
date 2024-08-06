import { BracketRes } from '../../brackets/shared'
import { PageHeader } from '../../brackets/shared/components'
import { Page } from './PageRouter/types'
import { ProgressBar } from './ProgressBar'
import { GoLiveSubPageProps } from './types'

interface GoLivePageContainerProps extends GoLiveSubPageProps {
  children?: React.ReactNode
  className?: string
  title?: string
  subTitle?: string
}

export const GoLivePageContainer = (props: GoLivePageContainerProps) => {
  const { showProgress = true } = props
  return (
    <div className="wpbb-reset tw-bg-dd-blue tw-px-20 tw-py-60">
      <div className="tw-max-w-screen-lg tw-flex tw-flex-col tw-mx-auto tw-gap-30 md:tw-gap-60">
        <div className="tw-flex tw-flex-col tw-gap-15">
          <PageHeader title={props.title} subtitle={props.subTitle} />
          {showProgress && (
            <ProgressBar
              maxSteps={props.pages?.length || 0}
              currentStep={props.currentPage || 0}
              stepNames={props.pages?.map((page) => page.title) || []}
            />
          )}
        </div>
        <div className={props.className}>{props.children}</div>
      </div>
    </div>
  )
}
