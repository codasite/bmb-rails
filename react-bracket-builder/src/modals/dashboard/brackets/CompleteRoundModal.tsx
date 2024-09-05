import { useState } from 'react'
import { Modal } from '../../Modal'
import { CancelButton, ConfirmButton } from '../../ModalButtons'
import { ModalHeader } from '../../ModalHeader'
import addClickHandlers from '../../addClickHandlers'
import { votingBracketApi } from '../../../features/VotingBracket/votingBracketApi'
import { HttpError } from '../../../brackets/shared/api/wpHttpClient'

export const CompleteRoundModal = () => {
  const [show, setShow] = useState(false)
  const [loading, setLoading] = useState(false)
  const [errorMessage, setErrorMessage] = useState('')
  const [bracketId, setBracketId] = useState(0)
  const [liveRoundIndex, setLiveRoundIndex] = useState(0)
  const [isFinalRound, setIsFinalRound] = useState(false)

  addClickHandlers({
    buttonClassName: 'wpbb-complete-round-btn',
    onButtonClick: (b) => {
      setBracketId(parseInt(b.dataset.bracketId))
      setLiveRoundIndex(parseInt(b.dataset.liveRoundIndex))
      setIsFinalRound(b.dataset.isFinalRound === 'true')
      setShow(true)
      setErrorMessage('')
    },
  })

  async function onCompleteRound() {
    setLoading(true)
    try {
      await votingBracketApi.completeRound(bracketId)
      window.location.reload()
    } catch (error) {
      console.log(error)
      if (error instanceof HttpError) {
        setErrorMessage(`Error completing round. ${error.data.message}`)
      } else {
        setErrorMessage('Error completing round')
      }
    } finally {
      setLoading(false)
    }
  }

  return (
    <Modal show={show} setShow={setShow}>
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
        <CancelButton onClick={() => setShow(false)} />
      </div>
    </Modal>
  )
}
