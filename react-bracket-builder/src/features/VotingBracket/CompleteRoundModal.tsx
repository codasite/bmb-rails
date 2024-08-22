import { useState } from 'react'
import { Modal } from '../../modals/Modal'
import { CancelButton, ConfirmButton } from '../../modals/ModalButtons'
import { ModalHeader } from '../../modals/ModalHeader'
import addClickHandlers from '../../modals/addClickHandlers'
import { votingBracketApi } from './votingBracketApi'

export default function CompleteRoundModal() {
  const [show, setShow] = useState(false)
  const [loading, setLoading] = useState(false)
  const [hasError, setHasError] = useState(false)
  const [bracketId, setBracketId] = useState(0)
  const [liveRoundIndex, setLiveRoundIndex] = useState(0)

  addClickHandlers({
    buttonClassName: 'wpbb-complete-round-button',
    onButtonClick: (b) => {
      setBracketId(parseInt(b.dataset.bracketId))
      setLiveRoundIndex(parseInt(b.dataset.liveRoundIndex))
      setShow(true)
      setHasError(false)
    },
  })

  async function onCompleteRound() {
    setLoading(true)
    try {
      await votingBracketApi.completeRound(bracketId)
      window.location.reload()
    } catch {
      setHasError(true)
    } finally {
      setLoading(false)
    }
  }

  return (
    <Modal show={show} setShow={setShow}>
      <ModalHeader text={`Complete Voting Round ${liveRoundIndex + 1}`} />
      <p className="tw-text-center">
        Complete the current voting round and set the next round to live.
      </p>
      <div className="tw-flex tw-flex-col tw-gap-10">
        <div className="tw-flex tw-gap-10 tw-items-center"></div>
        {hasError && <p className="tw-text-red tw-text-center">Error completing round</p>}
        <ConfirmButton disabled={loading} onClick={onCompleteRound}>
          Complete Round
        </ConfirmButton>
        <CancelButton onClick={() => setShow(false)} />
      </div>
    </Modal>
  )
}
