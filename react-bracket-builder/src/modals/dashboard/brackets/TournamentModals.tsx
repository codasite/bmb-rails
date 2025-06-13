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
import { parseBracketDataFromEl } from '../../parseBracketDataFromEl'

// Map button class names to modal names
const BUTTON_TO_MODAL_MAP: Record<string, keyof TournamentModalVisibility> = {
  'wpbb-share-bracket-button': 'shareBracket',
  'wpbb-edit-bracket-button': 'editBracket',
  'wpbb-delete-bracket-button': 'deleteBracket',
  'wpbb-set-tournament-fee-button': 'setTournamentFee',
  'wpbb-lock-live-tournament-button': 'lockLiveTournament',
  'wpbb-more-options-button': 'moreOptions',
  'wpbb-publish-bracket-button': 'publishBracket',
  'wpbb-complete-round-btn': 'completeRound',
  'wpbb-enable-upcoming-notification-button': 'enableUpcomingNotification',
  'wpbb-disable-upcoming-notification-button': 'disableUpcomingNotification',
}

interface TournamentModalsProps {
  appObj: WpbbAppObj
  children?: React.ReactNode
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
      publishBracket: false,
      completeRound: false,
      enableUpcomingNotification: false,
      disableUpcomingNotification: false,
    })
  const [bracketData, setBracketData] = useState<BracketData>({})

  const containerRef = useRef<HTMLDivElement>(null)
  const listContainerRef = useRef<HTMLDivElement | null>(null)

  useEffect(() => {
    // If no children were passed, look for the legacy container
    if (!props.children) {
      const element = document.getElementById('wpbb-tournaments-list-container')
      listContainerRef.current = element as HTMLDivElement | null
    }

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
        const bracketData = parseBracketDataFromEl(button)

        // Special handling for publish bracket
        if (matchingClass === 'wpbb-publish-bracket-button') {
          if (props.appObj.userCanShareBracket && bracketData.goLiveUrl) {
            window.location.href = bracketData.goLiveUrl
            return
          }
        }

        // Show the corresponding modal
        const modalName = BUTTON_TO_MODAL_MAP[matchingClass]
        setBracketData(bracketData)
        setShowModal(modalName, true)
      } catch (error) {
        console.error('Error handling click:', error)
      }
    }

    // Add click handler to both the modals container and the list container if it exists
    container.addEventListener('click', handleClick)
    if (listContainerRef.current) {
      listContainerRef.current.addEventListener('click', handleClick)
    }

    return () => {
      container.removeEventListener('click', handleClick)
      if (listContainerRef.current) {
        listContainerRef.current.removeEventListener('click', handleClick)
      }
    }
  }, [props.children])

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
      publishBracket: false,
      completeRound: false,
      enableUpcomingNotification: false,
      disableUpcomingNotification: false,
      [modalName]: show,
    })
  }

  return (
    <div ref={containerRef}>
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
        show={modalVisibility.publishBracket}
        setShow={(show) => setShowModal('publishBracket', show)}
      />
      <UpcomingNotificationModal
        isUserLoggedIn={props.appObj.isUserLoggedIn}
        loginUrl={props.appObj.loginUrl}
        showEnable={modalVisibility.enableUpcomingNotification}
        setShowEnable={(show) =>
          setShowModal('enableUpcomingNotification', show)
        }
        showDisable={modalVisibility.disableUpcomingNotification}
        setShowDisable={(show) =>
          setShowModal('disableUpcomingNotification', show)
        }
        bracketData={bracketData}
      />
      <CompleteRoundModal
        show={modalVisibility.completeRound}
        setShow={(show) => setShowModal('completeRound', show)}
        bracketData={bracketData}
      />
      <MoreOptionsModal
        show={modalVisibility.moreOptions}
        setShow={(show) => setShowModal('moreOptions', show)}
        showModal={(modalName) => setShowModal(modalName, true)}
        bracketData={bracketData}
        setBracketData={setBracketData}
      />
      {props.children}
    </div>
  )
}
