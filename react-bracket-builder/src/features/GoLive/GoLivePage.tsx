import { useState } from 'react'
import { ChargesEnabledContainer } from '../../modals/dashboard/brackets/SetTournamentFeeModal/SetTournamentFeeModal'
import Button from '../../ui/Button'
import {
  BracketRes,
  SignalIcon,
  bracketApi,
  getDashboardPath,
} from '../../brackets/shared'
import { useLocation } from 'react-use'
import {
  CopyLinkButton,
  FacebookShareLink,
  TwitterShareLink,
} from '../../modals/dashboard/brackets/ShareBracketModal'
import { InputFeeAmount } from '../../modals/dashboard/brackets/SetTournamentFeeModal/InputFeeAmount'

const GoLivePage = (props: {
  bracket: BracketRes
  applicationFeeMinimum: number
  applicationFeePercentage: number
}) => {
  const [chargesEnabled, setChargesEnabled] = useState(false)
  const [loading, setLoading] = useState(false)
  const location = useLocation()

  const onContinue = async () => {
    setLoading(true)
    try {
      await bracketApi.updateBracket(props.bracket.id, { status: 'publish' })
      history.pushState({}, '', 'share/')
    } finally {
      setLoading(false)
    }
  }

  const EntryFeePage = () => {
    return (
      <>
        <h2 className="tw-text-center tw-mb-20 tw-mt-40 tw-text-24">
          Do you want to set an entry fee?
        </h2>
        <ChargesEnabledContainer
          chargesEnabled={chargesEnabled}
          setChargesEnabled={setChargesEnabled}
        >
          <InputFeeAmount
            bracketId={props.bracket.id}
            fee={props.bracket.fee}
            onCancel={() => {
              onContinue()
            }}
            onSave={() => {
              onContinue()
            }}
            applicationFeeMinimum={props.applicationFeeMinimum}
            applicationFeePercentage={props.applicationFeePercentage}
          />
        </ChargesEnabledContainer>
        <Button
          disabled={loading}
          onClick={() => {
            onContinue()
          }}
          className="tw-mt-20"
          variant="filled"
        >
          Continue without fee
        </Button>
      </>
    )
  }

  const playBracketUrl = props.bracket.url + 'play'

  const SharePage = () => {
    return (
      <>
        <SignalIcon className="tw-mt-40" width="40" height="40" />
        <div className="tw-mt-10">
          <h2 className="tw-inline">Tournament is </h2>
          <h2 className="tw-inline !tw-text-green">live</h2>
        </div>
        <p className="tw-font-700 tw-text-14 tw-mt-10 tw-mb-30">
          Share and play with friends!
        </p>
        <div className="tw-flex tw-flex-col md:tw-flex-row tw-w-full tw-gap-15 tw-mb-30">
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
      </>
    )
  }

  const SwitchPages = () => {
    if (location.pathname.endsWith('/share/')) {
      return <SharePage />
    }
    return <EntryFeePage />
  }

  return (
    <div>
      <div className="wpbb-reset tw-bg-dd-blue tw-flex tw-justify-center">
        <div className="tw-max-w-screen-lg tw-flex tw-flex-grow tw-flex-col tw-items-center tw-px-20 tw-py-60 tw-overflow-hidden">
          <div className="tw-h-[50px] tw-w-[200px] md:tw-h-[75px] md:tw-w-[300px] lg:tw-h-[85px] lg:tw-w-[340px] tw-bg-[url('https://s3.amazonaws.com/backmybracket.com/bmb_text_logo_500.png')] tw-bg-no-repeat tw-bg-contain tw-mb-20" />
          <h1 className="tw-text-center tw-text-40 md:tw-text-48 lg:tw-text-64">
            Go Live!
          </h1>
          <SwitchPages />
        </div>
      </div>
    </div>
  )
}
export default GoLivePage
