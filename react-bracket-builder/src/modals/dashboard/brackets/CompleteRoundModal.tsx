import { useEffect, useState } from 'react'
import { Modal } from '../../Modal'
import { CancelButton, ConfirmButton } from '../../ModalButtons'
import { ModalHeader } from '../../ModalHeader'
import { votingBracketApi } from '../../../features/VotingBracket/votingBracketApi'
import { HttpError } from '../../../brackets/shared/api/wpHttpClient'
import { BracketData } from './BracketData'

interface CompleteRoundModalProps {
  show: boolean
  setShow: (show: boolean) => void
  bracketData: BracketData
}

export const CompleteRoundModal = (props: CompleteRoundModalProps) => {
  const [loading, setLoading] = useState(false)
  const [errorMessage, setErrorMessage] = useState('')

  useEffect(() => {
    if (props.show) {
      setErrorMessage('')
    }
  }, [props.show])

  async function onCompleteRound() {
    if (!props.bracketData.id) return

    setLoading(true)
    try {
      await votingBracketApi.completeRound(props.bracketData.id)
      window.location.reload()
    } catch (error) {
      console.error(error)
      if (error instanceof HttpError) {
        setErrorMessage(`Error completing round. ${error.data.message}`)
      } else {
        setErrorMessage('Error completing round')
      }
    } finally {
      setLoading(false)
    }
  }

  const liveRoundIndex = props.bracketData.liveRoundIndex ?? 0
  const isFinalRound = props.bracketData.isFinalRound ?? false

  return (
    <Modal show={props.show} setShow={props.setShow}>
      <ModalHeader text={`Close Voting Round ${liveRoundIndex + 1}`} />
      <p className="tw-text-center">
        Close the current voting round and
        {isFinalRound
          ? ' complete the tournament.'
          : ' set the next round to live.'}
      </p>
      <div className="tw-flex tw-flex-col tw-gap-10">
        <div className="tw-flex tw-gap-10 tw-items-center"></div>
        {errorMessage && (
          <p className="tw-text-red tw-text-center">{errorMessage}</p>
        )}
        <ConfirmButton disabled={loading} onClick={onCompleteRound}>
          Close Round
        </ConfirmButton>
        <CancelButton onClick={() => props.setShow(false)} />
      </div>
    </Modal>
  )
}
