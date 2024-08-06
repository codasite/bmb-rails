import { CancelButton } from '../../../ModalButtons'
import { useEffect, useState } from 'react'
import { Modal } from '../../../Modal'
import addClickHandlers from '../../../addClickHandlers'
import { bracketApi } from '../../../../brackets/shared'
import { Spinner } from '../../../../brackets/shared/components/Spinner'
import { InputFeeAmount } from './InputFeeAmount'
import { ModalHeader } from '../../../ModalHeader'
import { ModalHeaderLogo } from './ModalHeaderLogo'
import { SetUpPaymentsButton } from './SetUpPaymentsButton'
import { logger } from '../../../../utils/Logger'

export const ChargesEnabledContainer = (props: {
  // I hoist this state so that react will save it between rerenders, we
  // could also change the modal to use tw-hidden instead of changing the DOM
  // so react saves this state.
  chargesEnabled: boolean
  setChargesEnabled: (enabled: boolean) => void
  children?: React.ReactNode
}) => {
  const [loadingAccount, setLoadingAccount] = useState(false)
  const fetchChargesEnabled = async () => {
    try {
      setLoadingAccount(true)
      const data = await bracketApi.getStripeAccount()
      if (data.account?.chargesEnabled) {
        props.setChargesEnabled(true)
      }
    } catch (error) {
      console.error(error)
      logger.error(error)
    } finally {
      setLoadingAccount(false)
    }
  }

  useEffect(() => {
    if (!props.chargesEnabled) {
      fetchChargesEnabled()
    }
  }, [])

  if (loadingAccount) {
    return (
      <div className="tw-flex tw-justify-center tw-items-center tw-mb-30">
        <Spinner fill={'white'} height={32} width={32} />
      </div>
    )
  }
  if (!props.chargesEnabled) {
    return <SetUpPaymentsButton />
  }

  return <>{props.children}</>
}

interface SetTournamentFeeModalProps {
  applicationFeeMinimum: number
  applicationFeePercentage: number
}

export const SetTournamentFeeModal = (props: SetTournamentFeeModalProps) => {
  const [show, setShow] = useState(false)
  const [fee, setFee] = useState<number>(null)
  const [bracketId, setBracketId] = useState<number>(null)
  const [chargesEnabled, setChargesEnabled] = useState(false)

  const handleCancel = () => {
    setShow(false)
  }

  addClickHandlers({
    buttonClassName: 'wpbb-set-tournament-fee-button',
    onButtonClick: async (b) => {
      setBracketId(parseInt(b.dataset.bracketId))
      setFee(parseInt(b.dataset.fee))
      setShow(true)
    },
  })

  return (
    <Modal show={show} setShow={setShow}>
      <ModalHeaderLogo />
      <ModalHeader text={'Set an Entry Fee for Your Tournament'} />
      <ChargesEnabledContainer
        chargesEnabled={chargesEnabled}
        setChargesEnabled={setChargesEnabled}
      >
        <InputFeeAmount
          bracketId={bracketId}
          fee={fee}
          onCancel={handleCancel}
          onSave={() => window.location.reload()}
          applicationFeeMinimum={props.applicationFeeMinimum}
          applicationFeePercentage={props.applicationFeePercentage}
        />
      </ChargesEnabledContainer>
      <div className="tw-flex tw-justify-center tw-mt-10" />
      <CancelButton onClick={handleCancel} />
    </Modal>
  )
}
