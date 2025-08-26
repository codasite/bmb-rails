import Button from '../../ui/Button'
import {
  BracketRes,
  SignalIcon,
  getDashboardPath,
  getPlayBracketUrl,
} from '../../brackets/shared'
import {
  CopyLinkButton,
  FacebookShareLink,
  TwitterShareLink,
} from '../../modals/dashboard/brackets/ShareBracketModal'
import { GoLivePageContainer } from './GoLivePageContainer'
import { H3 } from '../../elements'
import { GoLiveSubPageProps } from './types'

export const SharePage = (props: GoLiveSubPageProps) => {
  const playBracketUrl = getPlayBracketUrl(props.bracket)
  return (
    <GoLivePageContainer
      className="tw-flex tw-flex-col tw-gap-30 tw-items-center"
      subTitle={props.bracket.title}
      {...props}
    >
      <SignalIcon />
      <div className="tw-flex tw-flex-col tw-gap-15 tw-items-center">
        {props.bracket.status === 'upcoming' ? (
          <>
            <H3>
              <span className="tw-inline">Tournament is </span>
              <span className="tw-inline !tw-text-yellow">upcoming</span>
            </H3>
            <span className="tw-font-700 tw-text-14">Share the preview link!</span>
          </>
        ) : (
          <>
            <H3>
              <span className="tw-inline">Tournament is </span>
              <span className="tw-inline !tw-text-green">live</span>
            </H3>
            <span className="tw-font-700 tw-text-14">
              Share and play with friends!
            </span>
          </>
        )}
      </div>
      <div className="tw-flex tw-flex-col md:tw-flex-row tw-w-full tw-gap-10">
        <CopyLinkButton playBracketUrl={playBracketUrl} onClick={() => {}} />
        <FacebookShareLink playBracketUrl={playBracketUrl} />
        <TwitterShareLink
          playBracketUrl={playBracketUrl}
          bracketTitle={props.bracket.title}
        />
      </div>
      <Button
        onClick={() => {
          window.location.href = getDashboardPath()
        }}
        variant="filled"
        text="Return to dashboard"
      />
    </GoLivePageContainer>
  )
}
