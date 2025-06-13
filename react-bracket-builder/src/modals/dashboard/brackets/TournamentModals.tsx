import { useEffect, useRef, useState } from 'react'
import { WpbbAppObj } from '../../../utils/WpbbAjax'
import { CompleteRoundModal } from './CompleteRoundModal'
import { DeleteBracketModal } from './DeleteBracketModal'
import { EditBracketModal } from './EditBracketModal'
import { LockLiveTournamentModal } from './LockLiveTournamentModal'
import { PublishBracketModal } from './PublishBracketModal'
import { SetTournamentFeeModal } from './SetTournamentFeeModal'
import { ShareBracketModal } from './ShareBracketModal'
import { UpcomingNotificationModal } from './UpcomingNotificationModal'
import { MoreOptionsModal } from './MoreOptionsModal'
import { BracketData } from './BracketData'
import { TournamentModalVisibility } from './TournamentModalVisibility'
import { loadBracketData } from '../../loadBracketData'

// Map button class names to modal names
const BUTTON_TO_MODAL_MAP: Record<string, keyof TournamentModalVisibility> = {
  'wpbb-share-bracket-button': 'shareBracket',
  'wpbb-edit-bracket-button': 'editBracket',
  'wpbb-delete-bracket-button': 'deleteBracket',
  'wpbb-set-tournament-fee-button': 'setTournamentFee',
  'wpbb-lock-live-tournament-button': 'lockLiveTournament',
  'wpbb-more-options-button': 'moreOptions',
}

interface TournamentModalsProps {
  appObj: WpbbAppObj
}

export const TournamentModals = (props: TournamentModalsProps) => {
  const [modalVisibility, setModalVisibility] =
    useState<TournamentModalVisibility>({
      editBracket: false,
      shareBracket: false,
      deleteBracket: false,
      setTournamentFee: false,
      lockLiveTournament: false,
      moreOptions: false,
    })
  const [bracketData, setBracketData] = useState<BracketData>({})

  const setShowModal = (
    modalName?: keyof TournamentModalVisibility,
    show?: boolean
  ) => {
    setModalVisibility({
      editBracket: false,
      shareBracket: false,
      deleteBracket: false,
      setTournamentFee: false,
      lockLiveTournament: false,
      moreOptions: false,
      [modalName]: show,
    })
  }

  const containerRef = useRef<HTMLDivElement>(null)
  useEffect(() => {
    const container = containerRef.current
    if (!container) return

    const handleClick = async (event: MouseEvent) => {
      const target = event.target as HTMLElement
      const button = target.closest('button')
      if (!button) return

      // Find the first matching modal for this button's classes
      const matchingClass = Object.keys(BUTTON_TO_MODAL_MAP).find((className) =>
        button.classList.contains(className)
      )
      if (!matchingClass) return

      try {
        // Load bracket data first
        await loadBracketData(button, setBracketData)

        // Show the corresponding modal
        const modalName = BUTTON_TO_MODAL_MAP[matchingClass]
        setShowModal(modalName, true)
      } catch (error) {
        console.error('Error handling click:', error)
      }
    }

    container.addEventListener('click', handleClick)
    return () => container.removeEventListener('click', handleClick)
  }, [])

  return (
    <>
      <EditBracketModal
        show={modalVisibility.editBracket}
        setShow={(show) => setShowModal('editBracket', show)}
        bracketData={bracketData}
        setBracketData={setBracketData}
      />
      <SetTournamentFeeModal
        applicationFeeMinimum={props.appObj.applicationFeeMinimum}
        applicationFeePercentage={props.appObj.applicationFeePercentage}
        show={modalVisibility.setTournamentFee}
        setShow={(show) => setShowModal('setTournamentFee', show)}
        bracketData={bracketData}
        setBracketData={setBracketData}
      />
      <ShareBracketModal
        show={modalVisibility.shareBracket}
        setShow={(show) => setShowModal('shareBracket', show)}
        bracketData={bracketData}
      />
      <LockLiveTournamentModal
        show={modalVisibility.lockLiveTournament}
        setShow={(show) => setShowModal('lockLiveTournament', show)}
        bracketData={bracketData}
        setBracketData={setBracketData}
      />
      <DeleteBracketModal
        show={modalVisibility.deleteBracket}
        setShow={(show) => setShowModal('deleteBracket', show)}
        bracketData={bracketData}
        setBracketData={setBracketData}
      />
      <PublishBracketModal
        upgradeAccountUrl={props.appObj.upgradeAccountUrl}
        canCreateBracket={props.appObj.userCanShareBracket}
      />
      <UpcomingNotificationModal
        isUserLoggedIn={props.appObj.isUserLoggedIn}
        loginUrl={props.appObj.loginUrl}
      />
      <CompleteRoundModal />
      <MoreOptionsModal
        show={modalVisibility.moreOptions}
        setShow={(show) => setShowModal('moreOptions', show)}
        showModal={(modalName) => setShowModal(modalName, true)}
        bracketData={bracketData}
        setBracketData={setBracketData}
      />
    </>
  )
}
