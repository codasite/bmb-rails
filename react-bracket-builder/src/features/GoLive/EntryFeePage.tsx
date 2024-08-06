import { ChargesEnabledContainer } from '../../modals/dashboard/brackets/SetTournamentFeeModal/SetTournamentFeeModal'
import Button from '../../ui/Button'
import { BracketRes, bracketApi } from '../../brackets/shared'
import { InputFeeAmount } from '../../modals/dashboard/brackets/SetTournamentFeeModal/InputFeeAmount'
import { H4 } from '../../elements/typography/H4'
import { GoLivePageContainer } from './GoLivePageContainer'
import { useState } from 'react'
import { GoLiveSubPageProps } from './types'
import { logger } from '../../utils/Logger'

interface EntryFeePageProps extends GoLiveSubPageProps {
  chargesEnabled: boolean
  setChargesEnabled: (chargesEnabled: boolean) => void
}

export const EntryFeePage = (props: EntryFeePageProps) => {
  const [loading, setLoading] = useState(false)
  // Don't allow the user to continue without setting a fee if the fee is already set
  const canContinueWithoutFee = props.bracket.fee === 0
  const onContinue = async () => {
    setLoading(true)
    try {
      await bracketApi.updateBracket(props.bracket.id, { status: 'publish' })
      props.navigate('next')
    } catch (error) {
      logger.error('Failed to update bracket', error)
    } finally {
      setLoading(false)
    }
  }
  return (
    <GoLivePageContainer
      title="Go Live!"
      subTitle={props.bracket.title}
      {...props}
    >
      <H4 className="tw-text-center tw-mb-30 md:tw-mb-60">
        Do you want to set an entry fee?
      </H4>
      <ChargesEnabledContainer
        chargesEnabled={props.chargesEnabled}
        setChargesEnabled={props.setChargesEnabled}
      >
        <InputFeeAmount
          bracketId={props.bracket.id}
          fee={props.bracket.fee}
          onCancel={onContinue}
          onSave={onContinue}
          applicationFeeMinimum={props.applicationFeeMinimum}
          applicationFeePercentage={props.applicationFeePercentage}
          gap="lg"
        />
      </ChargesEnabledContainer>
      {canContinueWithoutFee && (
        <Button
          disabled={loading}
          onClick={onContinue}
          className="tw-mt-15"
          variant="filled"
        >
          Continue without fee
        </Button>
      )}
    </GoLivePageContainer>
  )
}
